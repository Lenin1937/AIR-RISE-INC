<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PayPalSubscription;
use App\Services\Billing\StripePaymentService;
use App\Services\Billing\PayPalPaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function __construct(
        protected StripePaymentService $stripeService,
        protected PayPalPaymentService $paypalService
    ) {}

    /**
     * Display available plans.
     */
    public function index()
    {
        $plans = Plan::active()->get();
        $user = auth()->user();

        // Get all active subscriptions
        $activeSubscriptions = $user->allActiveSubscriptions();

        return Inertia::render('Billing/Plans', [
            'plans' => $plans,
            'stripeSubscriptions' => $activeSubscriptions['stripe'],
            'paypalSubscriptions' => $activeSubscriptions['paypal'],
        ]);
    }

    /**
     * Show user's subscription details.
     */
    public function show(Request $request)
    {
        $user = auth()->user();
        $gateway = $request->query('gateway', 'stripe');

        if ($gateway === 'stripe') {
            $subscription = $user->subscription('default');
            
            if (!$subscription) {
                return redirect()->route('billing.plans');
            }

            return Inertia::render('Billing/StripeSubscription', [
                'subscription' => [
                    'id' => $subscription->id,
                    'type' => $subscription->type,
                    'stripe_status' => $subscription->stripe_status,
                    'stripe_price' => $subscription->stripe_price,
                    'quantity' => $subscription->quantity,
                    'trial_ends_at' => $subscription->trial_ends_at,
                    'ends_at' => $subscription->ends_at,
                    'onTrial' => $subscription->onTrial(),
                    'onGracePeriod' => $subscription->onGracePeriod(),
                    'active' => $subscription->active(),
                    'canceled' => $subscription->canceled(),
                ],
            ]);
        } else {
            $subscription = $user->activePayPalSubscription()->with('plan')->first();
            
            if (!$subscription) {
                return redirect()->route('billing.plans');
            }

            return Inertia::render('Billing/PayPalSubscription', [
                'subscription' => $subscription,
            ]);
        }
    }

    /**
     * Create a new Stripe subscription.
     */
    public function storeStripe(Request $request)
    {
        $request->validate([
            'price_id' => 'required|string',
            'payment_method' => 'required|string',
            'trial_days' => 'nullable|integer',
        ]);

        $user = auth()->user();

        // Check if user already has an active Stripe subscription
        if ($user->subscribed()) {
            return back()->withErrors(['error' => 'You already have an active Stripe subscription.']);
        }

        try {
            $subscription = $this->stripeService->createSubscription(
                $user,
                $request->price_id,
                'default',
                $request->trial_days,
                $request->payment_method
            );

            return redirect()->route('billing.subscription', ['gateway' => 'stripe'])
                ->with('success', 'Stripe subscription created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new PayPal subscription.
     */
    public function storePayPal(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($request->plan_id);

        // Check if user already has an active PayPal subscription
        if ($user->activePayPalSubscription()->exists()) {
            return back()->withErrors(['error' => 'You already have an active PayPal subscription.']);
        }

        try {
            $subscription = $this->paypalService->createSubscription(
                $user,
                $plan->paypal_plan_id,
                'default'
            );

            return redirect()->route('billing.subscription', ['gateway' => 'paypal'])
                ->with('success', 'PayPal subscription created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a Stripe subscription.
     */
    public function cancelStripe(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->subscription('default');

        if (!$subscription) {
            return back()->withErrors(['error' => 'No active subscription found.']);
        }

        $immediately = $request->boolean('immediately', false);

        try {
            $this->stripeService->cancelSubscription($user, 'default', $immediately);

            return redirect()->route('billing.subscription', ['gateway' => 'stripe'])
                ->with('success', 'Stripe subscription canceled successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a PayPal subscription.
     */
    public function cancelPayPal(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->activePayPalSubscription()->first();

        if (!$subscription) {
            return back()->withErrors(['error' => 'No active PayPal subscription found.']);
        }

        try {
            $this->paypalService->cancelSubscription(
                $subscription->paypal_subscription_id,
                $request->input('reason', 'Customer request')
            );

            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'ends_at' => $subscription->current_period_end ?? now(),
            ]);

            return redirect()->route('billing.subscription', ['gateway' => 'paypal'])
                ->with('success', 'PayPal subscription canceled successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Resume a canceled Stripe subscription.
     */
    public function resumeStripe()
    {
        $user = auth()->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->onGracePeriod()) {
            return back()->withErrors(['error' => 'No cancelable subscription found.']);
        }

        try {
            $this->stripeService->resumeSubscription($user, 'default');

            return redirect()->route('billing.subscription', ['gateway' => 'stripe'])
                ->with('success', 'Stripe subscription resumed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Change Stripe subscription plan.
     */
    public function swapStripe(Request $request)
    {
        $request->validate([
            'price_id' => 'required|string',
        ]);

        $user = auth()->user();
        $subscription = $user->subscription('default');

        if (!$subscription) {
            return back()->withErrors(['error' => 'No active subscription found.']);
        }

        if ($subscription->stripe_price === $request->price_id) {
            return back()->withErrors(['error' => 'You are already subscribed to this plan.']);
        }

        try {
            $this->stripeService->swapSubscription($user, $request->price_id, 'default');

            return redirect()->route('billing.subscription', ['gateway' => 'stripe'])
                ->with('success', 'Plan changed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

   /**
     * Get customer portal URL for Stripe.
     */
    public function portal(Request $request)
    {
        $request->validate([
            'return_url' => 'required|url',
        ]);

        try {
            $url = $this->stripeService->createBillingPortalSession(
                auth()->user(),
                $request->return_url
            );

            return redirect($url);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
