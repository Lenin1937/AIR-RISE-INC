<?php

namespace App\Services\Billing;

use App\Models\User;
use App\Models\Payment;
use App\Models\PayPalSubscription;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalPaymentService
{
    protected PayPalClient $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

    /**
     * Create an order for one-time payment.
     */
    public function createOrder(float $amount, string $currency = 'USD', array $metadata = []): array
    {
        try {
            $order = $this->paypal->createOrder([
                'intent' => 'CAPTURE',
                'application_context' => [
                    'return_url' => route('billing.paypal.success'),
                    'cancel_url' => route('billing.paypal.cancel'),
                ],
                'purchase_units' => [
                    [
                        'reference_id' => $metadata['reference_id'] ?? uniqid(),
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                        'description' => $metadata['description'] ?? 'Payment',
                    ],
                ],
            ]);

            if (isset($order['id'])) {
                return [
                    'order_id' => $order['id'],
                    'approval_url' => $order['links'][1]['href'] ?? null,
                ];
            }

            throw new \Exception('Failed to create PayPal order');
        } catch (\Exception $e) {
            throw new \Exception("PayPal order creation failed: " . $e->getMessage());
        }
    }

    /**
     * Capture an approved order.
     */
    public function captureOrder(string $orderId): array
    {
        try {
            $result = $this->paypal->capturePaymentOrder($orderId);

            if (isset($result['status']) && $result['status'] === 'COMPLETED') {
                return [
                    'success' => true,
                    'order_id' => $result['id'],
                    'payer_id' => $result['payer']['payer_id'] ?? null,
                    'capture_id' => $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Payment capture failed',
            ];
        } catch (\Exception $e) {
            throw new \Exception("PayPal capture failed: " . $e->getMessage());
        }
    }

    /**
     * Create a subscription.
     */
    public function createSubscription(User $user, string $planId, string $type = 'default'): PayPalSubscription
    {
        try {
            $subscription = $this->paypal->createSubscription([
                'plan_id' => $planId,
                'subscriber' => [
                    'name' => [
                        'given_name' => $user->first_name,
                        'surname' => $user->last_name,
                    ],
                    'email_address' => $user->email,
                ],
                'application_context' => [
                    'return_url' => route('billing.paypal.subscription.success'),
                    'cancel_url' => route('billing.paypal.subscription.cancel'),
                    'brand_name' => config('app.name'),
                    'user_action' => 'SUBSCRIBE_NOW',
                ],
            ]);

            if (isset($subscription['id'])) {
                // Find the plan in our database
                $plan = \App\Models\Plan::where('paypal_plan_id', $planId)->firstOrFail();

                // Create subscription record
                return PayPalSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'type' => $type,
                    'paypal_subscription_id' => $subscription['id'],
                    'paypal_plan_id' => $planId,
                    'status' => strtolower($subscription['status']),
                ]);
            }

            throw new \Exception('Failed to create PayPal subscription');
        } catch (\Exception $e) {
            throw new \Exception("PayPal subscription creation failed: " . $e->getMessage());
        }
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'Not satisfied with the service'): bool
    {
        try {
            $result = $this->paypal->cancelSubscription($subscriptionId, $reason);
            
            // PayPal returns 204 No Content on success
            return true;
        } catch (\Exception $e) {
            throw new \Exception("PayPal subscription cancellation failed: " . $e->getMessage());
        }
    }

    /**
     * Get subscription details.
     */
    public function getSubscriptionDetails(string $subscriptionId): array
    {
        try {
            return $this->paypal->showSubscriptionDetails($subscriptionId);
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve subscription details: " . $e->getMessage());
        }
    }

    /**
     * Suspend a subscription.
     */
    public function suspendSubscription(string $subscriptionId, string $reason = 'Customer request'): bool
    {
        try {
            $result = $this->paypal->suspendSubscription($subscriptionId, $reason);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("PayPal subscription suspension failed: " . $e->getMessage());
        }
    }

    /**
     * Activate a suspended subscription.
     */
    public function activateSubscription(string $subscriptionId, string $reason = 'Reactivating subscription'): bool
    {
        try {
            $result = $this->paypal->activateSubscription($subscriptionId, $reason);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("PayPal subscription activation failed: " . $e->getMessage());
        }
    }

    /**
     * Create a billing plan in PayPal.
     */
    public function createBillingPlan(\App\Models\Plan $plan): string
    {
        try {
            $frequency = strtoupper($this->getBillingFrequency($plan->billing_interval));
            
            $billingPlan = $this->paypal->createPlan([
                'product_id' => $this->getOrCreateProduct($plan),
                'name' => $plan->name,
                'description' => $plan->description,
                'status' => 'ACTIVE',
                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => $frequency,
                            'interval_count' => $plan->billing_interval_count,
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0, // 0 for unlimited
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => number_format($plan->price, 2, '.', ''),
                                'currency_code' => $plan->currency,
                            ],
                        ],
                    ],
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3,
                ],
            ]);

            if (isset($billingPlan['id'])) {
                return $billingPlan['id'];
            }

            throw new \Exception('Failed to create PayPal billing plan');
        } catch (\Exception $e) {
            throw new \Exception("PayPal plan creation failed: " . $e->getMessage());
        }
    }

    /**
     * Get or create a product in PayPal.
     */
    protected function getOrCreateProduct(\App\Models\Plan $plan): string
    {
        // In a real implementation, you'd want to store the product_id
        // For now, we'll create a new product each time
        try {
            $product = $this->paypal->createProduct([
                'name' => $plan->name,
                'description' => $plan->description ?? 'Subscription plan',
                'type' => 'SERVICE',
                'category' => 'SOFTWARE',
            ]);

            return $product['id'];
        } catch (\Exception $e) {
            throw new \Exception("PayPal product creation failed: " . $e->getMessage());
        }
    }

    /**
     * Convert billing interval to PayPal frequency.
     */
    protected function getBillingFrequency(string $interval): string
    {
        return match($interval) {
            'day' => 'DAY',
            'week' => 'WEEK',
            'month' => 'MONTH',
            'year' => 'YEAR',
            default => 'MONTH',
        };
    }
}
