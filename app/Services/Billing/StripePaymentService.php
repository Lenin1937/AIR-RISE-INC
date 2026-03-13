<?php

namespace App\Services\Billing;

use App\Models\User;
use App\Models\Payment;
use Laravel\Cashier\Subscription;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripePaymentService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create or retrieve a Stripe customer for a user.
     * Note: Cashier handles this automatically, but this method is kept for backward compatibility.
     */
    public function createOrGetCustomer(User $user): string
    {
        // Ensure user has Stripe customer ID via Cashier
        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        return $user->stripe_id;
    }

    /**
     * Create a subscription using Cashier.
     */
    public function createSubscription(
        User $user, 
        string $priceId, 
        string $type = 'default',
        ?int $trialDays = null,
        ?string $paymentMethod = null
    ): Subscription {
        try {
            $subscriptionBuilder = $user->newSubscription($type, $priceId);

            // Add trial if specified
            if ($trialDays) {
                $subscriptionBuilder->trialDays($trialDays);
            }

            // Create subscription with payment method
            if ($paymentMethod) {
                return $subscriptionBuilder->create($paymentMethod);
            }

            // Create subscription (requires default payment method to be set)
            return $subscriptionBuilder->create();
            
        } catch (\Exception $e) {
            throw new \Exception("Failed to create subscription: " . $e->getMessage());
        }
    }

    /**
     * Cancel a user's subscription.
     */
    public function cancelSubscription(User $user, string $subscriptionType = 'default', bool $immediately = false): bool
    {
        try {
            $subscription = $user->subscription($subscriptionType);
            
            if (!$subscription) {
                throw new \Exception("Subscription not found");
            }

            if ($immediately) {
                $subscription->cancelNow();
            } else {
                $subscription->cancel();
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to cancel subscription: " . $e->getMessage());
        }
    }

    /**
     * Resume a canceled subscription.
     */
    public function resumeSubscription(User $user, string $subscriptionType = 'default'): bool
    {
        try {
            $subscription = $user->subscription($subscriptionType);
            
            if (!$subscription) {
                throw new \Exception("Subscription not found");
            }

            $subscription->resume();
            return true;
            
        } catch (\Exception $e) {
            throw new \Exception("Failed to resume subscription: " . $e->getMessage());
        }
    }

    /**
     * Swap subscription to a new price/plan.
     */
    public function swapSubscription(User $user, string $newPriceId, string $subscriptionType = 'default'): bool
    {
        try {
            $subscription = $user->subscription($subscriptionType);
            
            if (!$subscription) {
                throw new \Exception("Subscription not found");
            }

            $subscription->swap($newPriceId);
            return true;
            
        } catch (\Exception $e) {
            throw new \Exception("Failed to swap subscription: " . $e->getMessage());
        }
    }

    /**
     * Create a payment intent.
     */
    public function createPaymentIntent(float $amount, string $currency = 'usd', array $metadata = []): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => strtolower($currency),
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (ApiErrorException $e) {
            throw new \Exception("Failed to create payment intent: " . $e->getMessage());
        }
    }

    /**
     * Charge customer using Cashier.
     */
    public function chargeCustomer(User $user, float $amount, string $currency = 'usd', array $options = []): \Laravel\Cashier\Payment
    {
        try {
            // Convert amount to cents for Stripe
            $amountInCents = $amount * 100;
            
            return $user->charge($amountInCents, $options['payment_method'] ?? null, [
                'currency' => $currency,
                'metadata' => $options['metadata'] ?? [],
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to charge customer: " . $e->getMessage());
        }
    }

    /**
     * Confirm a payment intent.
     */
    public function confirmPayment(string $paymentIntentId): bool
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            
            return $paymentIntent->status === 'succeeded';
        } catch (ApiErrorException $e) {
            throw new \Exception("Failed to confirm payment: " . $e->getMessage());
        }
    }

    /**
     * Add a payment method to customer using Cashier.
     */
    public function addPaymentMethod(User $user, string $paymentMethodId): bool
    {
        try {
            $user->addPaymentMethod($paymentMethodId);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to add payment method: " . $e->getMessage());
        }
    }

    /**
     * Update default payment method using Cashier.
     */
    public function updateDefaultPaymentMethod(User $user, string $paymentMethodId): bool
    {
        try {
            $user->updateDefaultPaymentMethod($paymentMethodId);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to update default payment method: " . $e->getMessage());
        }
    }

    /**
     * Get customer's payment methods using Cashier.
     */
    public function getPaymentMethods(User $user, string $type = 'card'): array
    {
        try {
            return $user->paymentMethods($type)->map(function ($paymentMethod) {
                return [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'brand' => $paymentMethod->card->brand ?? null,
                    'last4' => $paymentMethod->card->last4 ?? null,
                    'exp_month' => $paymentMethod->card->exp_month ?? null,
                    'exp_year' => $paymentMethod->card->exp_year ?? null,
                ];
            })->toArray();
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve payment methods: " . $e->getMessage());
        }
    }

    /**
     * Create a setup intent for saving payment method without charging.
     */
    public function createSetupIntent(User $user): array
    {
        try {
            $setupIntent = $user->createSetupIntent();

            return [
                'client_secret' => $setupIntent->client_secret,
                'setup_intent_id' => $setupIntent->id,
            ];
        } catch (\Exception $e) {
            throw new \Exception("Failed to create setup intent: " . $e->getMessage());
        }
    }

    /**
     * Get user's invoices using Cashier.
     */
    public function getInvoices(User $user): array
    {
        try {
            return $user->invoices()->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'date' => $invoice->date()->toDateTimeString(),
                    'total' => $invoice->total(),
                    'status' => $invoice->status,
                    'invoice_pdf' => $invoice->invoice_pdf,
                ];
            })->toArray();
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve invoices: " . $e->getMessage());
        }
    }

    /**
     * Create a billing portal session using Cashier.
     */
    public function createBillingPortalSession(User $user, string $returnUrl): string
    {
        try {
            return $user->billingPortalUrl($returnUrl);
        } catch (\Exception $e) {
            throw new \Exception("Failed to create billing portal session: " . $e->getMessage());
        }
    }

    /**
     * Create a payment intent that supports cryptocurrency.
     */
    public function createCryptoPaymentIntent(float $amount, string $currency = 'usd', array $metadata = []): array
    {
        try {
            $paymentMethodTypes = ['card'];
            
            // Add crypto if enabled
            if (config('services.stripe.crypto_enabled')) {
                $paymentMethodTypes[] = 'crypto';
            }

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => strtolower($currency),
                'payment_method_types' => $paymentMethodTypes,
                'metadata' => $metadata,
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'payment_method_types' => $paymentIntent->payment_method_types,
            ];
        } catch (ApiErrorException $e) {
            throw new \Exception("Failed to create crypto payment intent: " . $e->getMessage());
        }
    }

    /**
     * Check if a payment was made with cryptocurrency.
     */
    public function isCryptoPayment(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            
            $isCrypto = in_array('crypto', $paymentIntent->payment_method_types);
            
            $result = [
                'is_crypto' => $isCrypto,
                'payment_method_types' => $paymentIntent->payment_method_types,
            ];

            if ($isCrypto && isset($paymentIntent->charges->data[0])) {
                $charge = $paymentIntent->charges->data[0];
                if (isset($charge->payment_method_details->crypto)) {
                    $cryptoDetails = $charge->payment_method_details->crypto;
                    $result['crypto_type'] = $cryptoDetails->type ?? null; // bitcoin, ethereum, etc.
                    $result['crypto_network'] = $cryptoDetails->network ?? null;
                }
            }

            return $result;
        } catch (ApiErrorException $e) {
            throw new \Exception("Failed to check crypto payment: " . $e->getMessage());
        }
    }

    /**
     * Get supported cryptocurrency types from configuration.
     */
    public function getSupportedCryptocurrencies(): array
    {
        return config('services.stripe.crypto_currencies', []);
    }

    /**
     * Check if cryptocurrency payments are enabled.
     */
    public function isCryptoEnabled(): bool
    {
        return config('services.stripe.crypto_enabled', false);
    }
}

