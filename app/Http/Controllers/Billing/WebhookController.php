<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\Billing\InvoiceService;
use App\Services\OrderMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Handle Stripe webhooks.
     */
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid Stripe webhook payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid Stripe webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Check if we've already processed this event
        if (WebhookEvent::exists($event->id, 'stripe')) {
            return response()->json(['message' => 'Event already processed'], 200);
        }

        // Record the webhook event
        $webhookEvent = WebhookEvent::record(
            $event->id,
            'stripe',
            $event->type,
            json_decode($payload, true)
        );

        try {
            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                case 'invoice.paid':
                    $this->handleInvoicePaid($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;

                case 'customer.subscription.trial_will_end':
                    $this->handleTrialWillEnd($event->data->object);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event: ' . $event->type);
            }

            $webhookEvent->markAsProcessed();
            return response()->json(['message' => 'Webhook handled'], 200);
        } catch (\Exception $e) {
            Log::error('Error processing Stripe webhook: ' . $e->getMessage());
            $webhookEvent->markAsFailed($e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle PayPal webhooks.
     */
    public function handlePayPal(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;
        $eventId = $payload['id'] ?? null;

        if (!$eventId || !$eventType) {
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        // Verify PayPal webhook signature to reject forged events.
        $webhookId = config('paypal.webhook_id');

        if (!empty($webhookId)) {
            try {
                $provider = new PayPalClient();
                $provider->setApiCredentials(config('paypal'));
                $provider->getAccessToken();

                $verificationResult = $provider->verifyWebHook([
                    'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
                    'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                    'cert_url'          => $request->header('PAYPAL-CERT-URL'),
                    'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
                    'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
                    'webhook_id'        => $webhookId,
                    'webhook_event'     => $payload,
                ]);

                if (($verificationResult['verification_status'] ?? '') !== 'SUCCESS') {
                    Log::warning('PayPal webhook signature verification failed', [
                        'event_id'   => $eventId,
                        'event_type' => $eventType,
                        'status'     => $verificationResult['verification_status'] ?? 'unknown',
                    ]);
                    return response()->json(['error' => 'Invalid webhook signature'], 401);
                }
            } catch (\Exception $e) {
                Log::error('PayPal webhook verification error: ' . $e->getMessage());
                return response()->json(['error' => 'Webhook verification failed'], 400);
            }
        } else {
            Log::warning('PAYPAL_WEBHOOK_ID is not configured — skipping signature verification.');
        }

        // Check if we've already processed this event
        if (WebhookEvent::exists($eventId, 'paypal')) {
            return response()->json(['message' => 'Event already processed'], 200);
        }

        // Record the webhook event
        $webhookEvent = WebhookEvent::record(
            $eventId,
            'paypal',
            $eventType,
            $payload
        );

        try {
            // Handle the event
            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePayPalPaymentCompleted($payload);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePayPalPaymentDenied($payload);
                    break;

                case 'BILLING.SUBSCRIPTION.CREATED':
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                case 'BILLING.SUBSCRIPTION.UPDATED':
                    $this->handlePayPalSubscriptionUpdated($payload);
                    break;

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    $this->handlePayPalSubscriptionCancelled($payload);
                    break;

                case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                    $this->handlePayPalSubscriptionPaymentFailed($payload);
                    break;

                default:
                    Log::info('Unhandled PayPal webhook event: ' . $eventType);
            }

            $webhookEvent->markAsProcessed();
            return response()->json(['message' => 'Webhook handled'], 200);
        } catch (\Exception $e) {
            Log::error('Error processing PayPal webhook: ' . $e->getMessage());
            $webhookEvent->markAsFailed($e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle successful payment intent from Stripe.
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update(['status' => 'completed']);

            // Mark associated invoice as paid
            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                $this->invoiceService->markInvoiceAsPaid($invoice, $payment);
            }
        }

        Log::info('Payment intent succeeded: ' . $paymentIntent->id);
    }

    /**
     * Handle failed payment intent from Stripe.
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update(['status' => 'failed']);

            if ($payment->order_id) {
                $order = $payment->order;
                if ($order) {
                    $failureReason = $paymentIntent->last_payment_error?->message ?? null;
                    app(OrderMailService::class)->sendPaymentFailed($order, $failureReason);
                }
            }
        }

        Log::warning('Payment intent failed: ' . $paymentIntent->id);
    }

    /**
     * Handle subscription update from Stripe.
     */
    protected function handleSubscriptionUpdated($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status' => $stripeSubscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $stripeSubscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $stripeSubscription->current_period_end),
            ]);
        }

        Log::info('Subscription updated: ' . $stripeSubscription->id);
    }

    /**
     * Handle subscription deletion from Stripe.
     */
    protected function handleSubscriptionDeleted($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'ends_at' => now(),
            ]);
        }

        Log::info('Subscription deleted: ' . $stripeSubscription->id);
    }

    /**
     * Handle paid invoice from Stripe.
     */
    protected function handleInvoicePaid($stripeInvoice)
    {
        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();

        if ($invoice) {
            $this->invoiceService->markInvoiceAsPaid($invoice);
        }

        Log::info('Invoice paid: ' . $stripeInvoice->id);
    }

    /**
     * Handle failed invoice payment from Stripe.
     */
    protected function handleInvoicePaymentFailed($stripeInvoice)
    {
        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();

        if ($invoice) {
            $invoice->update(['status' => 'overdue']);
        }

        Log::warning('Invoice payment failed: ' . $stripeInvoice->id);
    }

    /**
     * Handle trial ending soon notification.
     */
    protected function handleTrialWillEnd($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            // Send notification to user about trial ending
            // You can implement email notification here
            Log::info('Trial will end soon for subscription: ' . $stripeSubscription->id);
        }
    }

    /**
     * Handle completed PayPal payment.
     */
    protected function handlePayPalPaymentCompleted($payload)
    {
        $captureId = $payload['resource']['id'] ?? null;
        
        if ($captureId) {
            // You may need to store PayPal capture ID in your payments table
            Log::info('PayPal payment completed: ' . $captureId);
        }
    }

    /**
     * Handle denied PayPal payment.
     */
    protected function handlePayPalPaymentDenied($payload)
    {
        $captureId = $payload['resource']['id'] ?? null;
        
        if ($captureId) {
            Log::warning('PayPal payment denied: ' . $captureId);
        }
    }

    /**
     * Handle PayPal subscription update.
     */
    protected function handlePayPalSubscriptionUpdated($payload)
    {
        $subscriptionId = $payload['resource']['id'] ?? null;
        
        if ($subscriptionId) {
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            
            if ($subscription) {
                $status = strtolower($payload['resource']['status'] ?? 'active');
                $subscription->update(['status' => $status]);
            }

            Log::info('PayPal subscription updated: ' . $subscriptionId);
        }
    }

    /**
     * Handle PayPal subscription cancellation.
     */
    protected function handlePayPalSubscriptionCancelled($payload)
    {
        $subscriptionId = $payload['resource']['id'] ?? null;
        
        if ($subscriptionId) {
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'ends_at' => now(),
                ]);
            }

            Log::info('PayPal subscription cancelled: ' . $subscriptionId);
        }
    }

    /**
     * Handle PayPal subscription payment failure.
     */
    protected function handlePayPalSubscriptionPaymentFailed($payload)
    {
        $subscriptionId = $payload['resource']['id'] ?? null;
        
        if ($subscriptionId) {
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            
            if ($subscription) {
                $subscription->update(['status' => 'past_due']);
            }

            Log::warning('PayPal subscription payment failed: ' . $subscriptionId);
        }
    }
}
