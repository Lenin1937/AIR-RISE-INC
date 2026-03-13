<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Payment;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get real payment methods from orders where user has made payments
        $recent_payments = Payment::where('user_id', $user->id)
            ->successful()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency ?? 'usd',
                    'status' => $payment->status,
                    'description' => $payment->description,
                    'created_at' => $payment->created_at->toISOString(),
                    'method' => [
                        'type' => 'card',
                        'card_last4' => $payment->card_last_four ?? '0000',
                        'card_brand' => $payment->card_brand ?? 'unknown'
                    ]
                ];
            });

        // Extract unique payment methods from payments
        $payment_methods = $recent_payments
            ->filter(function ($payment) {
                return $payment['method']['card_last4'] !== '0000';
            })
            ->groupBy(function ($payment) {
                return $payment['method']['card_brand'] . '-' . $payment['method']['card_last4'];
            })
            ->map(function ($group, $key) {
                $first_payment = $group->first();
                return [
                    'id' => crc32($key), // Generate consistent ID from brand-last4
                    'type' => 'card',
                    'card_last4' => $first_payment['method']['card_last4'],
                    'card_brand' => $first_payment['method']['card_brand'],
                    'card_exp_month' => 12, // Default values since we don't store this yet
                    'card_exp_year' => date('Y') + 2,
                    'is_default' => $group->count() > 1, // Most used card becomes default
                    'created_at' => $group->min('created_at'),
                ];
            })
            ->values();

        return Inertia::render('PaymentMethods/Index', [
            'paymentMethods' => $payment_methods,
            'recent_payments' => $recent_payments,
            'stats' => [
                'total_methods' => $payment_methods->count(),
                'total_payments' => $recent_payments->count(),
                'total_spent' => $recent_payments->sum('amount')
            ]
        ]);
    }

    /**
     * Store a new payment method
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|string', // Stripe payment method ID
            'is_default' => 'boolean',
            'billing_address' => 'required|array',
            'billing_address.line1' => 'required|string',
            'billing_address.line2' => 'nullable|string',
            'billing_address.city' => 'required|string',
            'billing_address.state' => 'required|string',
            'billing_address.postal_code' => 'required|string',
            'billing_address.country' => 'required|string|size:2'
        ]);

        // TODO: Integrate with Stripe to save payment method
        // For now, just return success response
        
        return response()->json([
            'message' => 'Payment method added successfully!',
            'data' => $validated
        ]);
    }

    /**
     * Update the specified payment method
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'is_default' => 'boolean',
            'billing_address' => 'required|array',
            'billing_address.line1' => 'required|string',
            'billing_address.line2' => 'nullable|string',
            'billing_address.city' => 'required|string',
            'billing_address.state' => 'required|string',
            'billing_address.postal_code' => 'required|string',
            'billing_address.country' => 'required|string|size:2'
        ]);

        // TODO: Update payment method in Stripe
        // For now, just return success response
        
        return response()->json([
            'message' => 'Payment method updated successfully!',
            'payment_method_id' => $id,
            'data' => $validated
        ]);
    }

    /**
     * Remove the specified payment method
     */
    public function destroy($id)
    {
        // TODO: Remove payment method from Stripe
        // For now, just return success response
        
        return response()->json([
            'message' => 'Payment method removed successfully!',
            'payment_method_id' => $id
        ]);
    }

    /**
     * Set payment method as default
     */
    public function setDefault($id)
    {
        // TODO: Update default payment method
        // For now, just return success response
        
        return response()->json([
            'message' => 'Default payment method updated successfully!',
            'payment_method_id' => $id
        ]);
    }
}