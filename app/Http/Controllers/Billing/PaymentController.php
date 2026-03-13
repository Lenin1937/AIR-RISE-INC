<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Services\Billing\StripePaymentService;
use App\Services\Billing\PayPalPaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        protected StripePaymentService $stripeService,
        protected PayPalPaymentService $paypalService
    ) {}

    /**
     * Show payment methods page.
     */
    public function index()
    {
        $user = auth()->user();
        $payments = Payment::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return Inertia::render('Billing/Payments', [
            'payments' => $payments,
        ]);
    }

    /**
     * Create a payment intent for Stripe.
     */
    public function createIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        try {
            $metadata = [
                'user_id' => auth()->id(),
            ];

            if ($request->order_id) {
                $metadata['order_id'] = $request->order_id;
            }

            $result = $this->stripeService->createPaymentIntent(
                $request->amount,
                $request->currency ?? 'USD',
                $metadata
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create a payment intent with cryptocurrency support.
     */
    public function createCryptoIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        try {
            if (!$this->stripeService->isCryptoEnabled()) {
                return response()->json([
                    'error' => 'Cryptocurrency payments are not enabled'
                ], 400);
            }

            $metadata = [
                'user_id' => auth()->id(),
                'payment_type' => 'crypto',
            ];

            if ($request->order_id) {
                $metadata['order_id'] = $request->order_id;
            }

            $result = $this->stripeService->createCryptoPaymentIntent(
                $request->amount,
                $request->currency ?? 'USD',
                $metadata
            );

            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'payment_intent_id' => $result['payment_intent_id'],
                'payment_method_types' => $result['payment_method_types'],
                'supported_cryptocurrencies' => $this->stripeService->getSupportedCryptocurrencies(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirm a Stripe payment.
     */
    public function confirmStripe(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        try {
            $confirmed = $this->stripeService->confirmPayment($request->payment_intent_id);

            if ($confirmed) {
                // Create payment record
                $payment = Payment::create([
                    'user_id' => auth()->id(),
                    'order_id' => $request->order_id,
                    'stripe_payment_intent_id' => $request->payment_intent_id,
                    'payment_method' => 'credit_card',
                    'status' => 'completed',
                    'amount' => $request->amount ?? 0,
                ]);

                // Update order if exists
                if ($request->order_id) {
                    $order = Order::find($request->order_id);
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_id' => $payment->id,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'payment_id' => $payment->id,
                ]);
            }

            return response()->json(['error' => 'Payment confirmation failed'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create a PayPal order.
     */
    public function createPayPalOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        try {
            $metadata = [
                'user_id' => auth()->id(),
                'description' => $request->description ?? 'Payment',
            ];

            if ($request->order_id) {
                $metadata['reference_id'] = 'ORDER-' . $request->order_id;
            }

            $result = $this->paypalService->createOrder(
                $request->amount,
                $request->currency ?? 'USD',
                $metadata
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Capture a PayPal order.
     */
    public function capturePayPalOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'icorp_order_id' => 'nullable|exists:orders,id',
        ]);

        try {
            $result = $this->paypalService->captureOrder($request->order_id);

            if ($result['success']) {
                // Create payment record
                $payment = Payment::create([
                    'user_id' => auth()->id(),
                    'order_id' => $request->icorp_order_id,
                    'payment_method' => 'paypal',
                    'status' => 'completed',
                    'amount' => $request->amount ?? 0,
                ]);

                // Update order if exists
                if ($request->icorp_order_id) {
                    $order = Order::find($request->icorp_order_id);
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_id' => $payment->id,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'payment_id' => $payment->id,
                    'capture_id' => $result['capture_id'],
                ]);
            }

            return response()->json(['error' => $result['error']], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Save a payment method (Stripe).
     */
    public function savePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        try {
            $user = auth()->user();
            $customerId = $this->stripeService->createOrGetCustomer($user);

            $this->stripeService->attachPaymentMethod(
                $customerId,
                $request->payment_method_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment method saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get saved payment methods.
     */
    public function getPaymentMethods()
    {
        try {
            $user = auth()->user();
            $customerId = $this->stripeService->createOrGetCustomer($user);

            $paymentMethods = $this->stripeService->getPaymentMethods($customerId);

            return response()->json([
                'payment_methods' => $paymentMethods,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create a setup intent for saving payment method.
     */
    public function createSetupIntent()
    {
        try {
            $user = auth()->user();
            $customerId = $this->stripeService->createOrGetCustomer($user);

            $result = $this->stripeService->createSetupIntent($customerId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
