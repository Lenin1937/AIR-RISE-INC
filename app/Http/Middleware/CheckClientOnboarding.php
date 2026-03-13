<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect authenticated clients to their current onboarding step
 * if they have not yet been approved by an admin.
 */
class CheckClientOnboarding
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only apply to authenticated, non-admin users
        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        $status = $user->registration_status ?: 'approved';

        if ($request->routeIs('logout')) {
            return $next($request);
        }

        if ($status === 'approved') {
            return $next($request);
        }

        if ($request->routeIs('onboarding.review') && in_array($status, ['pending_approval', 'rejected'], true)) {
            return $next($request);
        }

        if (in_array($status, ['incomplete', 'order_pending'], true) && $this->isOrderFlowRoute($request)) {
            return $next($request);
        }

        switch ($status) {
            case 'incomplete':
            case 'order_pending':
                return redirect()->route('orders.create');

            case 'pending_approval':
            case 'rejected':
                return redirect()->route('onboarding.review');

            default:
                return $next($request);
        }
    }

    private function isOrderFlowRoute(Request $request): bool
    {
        return $request->routeIs(
            'orders.create',
            'orders.store',
            'orders.checkout',
            'orders.checkout.get',
            'orders.create-payment-intent',
            'orders.process-payment',
            'orders.paypal-process',
            'orders.create-crypto-payment',
            'orders.crypto-success',
            'orders.stripe.success',
            'orders.stripe.cancel',
        );
    }
}
