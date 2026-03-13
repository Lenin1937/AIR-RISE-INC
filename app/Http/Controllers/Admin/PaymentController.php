<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        // Get filters
        $search = $request->get('search');
        $status = $request->get('status');
        $paymentMethod = $request->get('payment_method');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query
        $query = Payment::with(['order.user']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('stripe_payment_intent_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('order.user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply payment method filter
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get paginated payments
        $payments = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Transform payments data
        $paymentsData = $payments->through(function ($payment) {
            return [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id ?? 'N/A',
                'order_number' => $payment->order?->order_number ?? 'N/A',
                'order_id' => $payment->order_id,
                'client_name' => $payment->order?->user?->full_name ?? $payment->order?->user?->name ?? 'N/A',
                'client_email' => $payment->order?->user?->email ?? 'N/A',
                'client_avatar' => $payment->order?->user?->profile_picture ? \Storage::url($payment->order->user->profile_picture) : null,
                'amount' => $payment->amount ?? 0,
                'status' => $payment->status,
                'status_display' => $this->getStatusDisplay($payment->status),
                'status_color' => $this->getStatusColor($payment->status),
                'payment_method' => $payment->payment_method ?? 'N/A',
                'payment_method_display' => $this->getPaymentMethodDisplay($payment->payment_method),
                'created_at' => $payment->created_at->toISOString(),
                'created_at_human' => $payment->created_at->format('M j, Y g:i A'),
                'metadata' => $payment->metadata ?? [],
            ];
        });

        // Calculate statistics
        $stats = [
            'total' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount') ?? 0,
            'this_month_revenue' => Payment::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount') ?? 0,
        ];

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $paymentsData,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $payment->load(['order.user', 'order.payments']);

        $order  = $payment->order;
        $user   = $order?->user;

        // Try to extract cardholder name from stripe_response if available
        $stripeResponse  = $payment->stripe_response ?? [];
        $cardHolderName  = data_get($stripeResponse, 'billing_details.name')
                        ?? data_get($stripeResponse, 'payment_method_details.card.wallet.dynamic_last4')
                        ?? null;

        $billingDetails  = $payment->billing_address ?? [];
        if (empty($billingDetails) && !empty($stripeResponse)) {
            $billingDetails = data_get($stripeResponse, 'billing_details', []);
        }

        $paymentData = [
            'id'                          => $payment->id,
            'transaction_id'              => $payment->transaction_id ?? 'N/A',
            'payment_id'                  => $payment->payment_id,
            // Stripe IDs
            'stripe_payment_intent_id'    => $payment->stripe_payment_intent_id,
            'stripe_charge_id'            => $payment->stripe_charge_id,
            'stripe_customer_id'          => $payment->stripe_customer_id,
            'stripe_payment_method_id'    => $payment->stripe_payment_method_id,
            // Order
            'order'  => $order ? [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'service_type' => $order->service_type_name ?? $order->service_type,
                'entity_name'  => $order->entity_name,
                'state'        => $order->state,
                'package_type' => $order->package_type,
            ] : null,
            // Client / User registration info
            'client' => $user ? [
                'id'                  => $user->id,
                'name'                => $user->name,
                'first_name'          => $user->first_name,
                'last_name'           => $user->last_name,
                'email'               => $user->email,
                'phone'               => $user->phone,
                'company_name'        => $user->company_name,
                'address_line_1'      => $user->address_line_1,
                'address_line_2'      => $user->address_line_2,
                'city'                => $user->city,
                'state'               => $user->state,
                'zip_code'            => $user->zip_code,
                'country'             => $user->country,
                'citizenship'         => $user->citizenship,
                'registered_at'       => $user->created_at?->toISOString(),
                'profile_picture_url' => $user->profile_picture ? \Storage::url($user->profile_picture) : null,
            ] : null,
            // Amounts
            'amount'                 => $payment->amount,
            'currency'               => strtoupper($payment->currency ?? 'USD'),
            'processing_fee'         => $payment->processing_fee,
            'net_amount'             => $payment->net_amount,
            'refunded_amount'        => $payment->refunded_amount,
            // Status
            'status'                 => $payment->status,
            'status_display'         => $this->getStatusDisplay($payment->status),
            'failure_code'           => $payment->failure_code,
            'failure_message'        => $payment->failure_message,
            // Payment method
            'payment_method'         => $payment->payment_method,
            'payment_method_display' => $this->getPaymentMethodDisplay($payment->payment_method),
            // Card info
            'card_brand'             => $payment->card_brand,
            'card_last_four'         => $payment->card_last_four,
            'card_exp_month'         => $payment->card_exp_month,
            'card_exp_year'          => $payment->card_exp_year,
            'card_holder_name'       => $cardHolderName,
            // Billing address
            'billing_address'        => $billingDetails,
            // Receipt
            'receipt_url'            => $payment->receipt_url,
            'invoice_number'         => $payment->invoice_number,
            'processed_at'           => $payment->processed_at?->toISOString(),
            'metadata'               => [],
            'created_at'             => $payment->created_at->toISOString(),
            'updated_at'             => $payment->updated_at->toISOString(),
        ];

        return Inertia::render('Admin/Payments/Show', [
            'payment' => $paymentData,
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment->update([
            'status' => $validated['status'],
        ]);

        // If marking as completed, you might want to trigger order status update
        if ($validated['status'] === 'completed' && $payment->order) {
            $totalPaid = $payment->order->payments()->where('status', 'completed')->sum('amount');
            
            // If fully paid, you might want to update order status
            if ($totalPaid >= $payment->order->total_amount) {
                // Add logic here if needed
            }
        }

        return back()->with('success', 'Payment updated successfully!');
    }

    private function getStatusDisplay($status)
    {
        $displays = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
        ];
        
        return $displays[$status] ?? ucfirst($status);
    }

    private function getStatusColor($status)
    {
        $colors = [
            'pending' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'refunded' => 'orange',
            'cancelled' => 'gray',
        ];
        
        return $colors[$status] ?? 'gray';
    }

    private function getPaymentMethodDisplay($method)
    {
        $displays = [
            'credit_card' => 'Credit Card',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
        ];
        
        return $displays[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }

    /**
     * Store a manual payment record
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'amount' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:stripe,bank_transfer,cash,check',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Find the order by order number
        $order = Order::where('order_number', $validated['order_number'])->first();

        if (!$order) {
            return back()->withErrors([
                'order_number' => 'Order not found with this order number.'
            ]);
        }

        // Create the payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? 'MANUAL-' . strtoupper(uniqid()),
            'status' => 'completed', // Manual payments are marked as completed
            'metadata' => [
                'manual_entry' => true,
                'recorded_by' => auth()->id(),
                'recorded_by_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                'recorded_at' => now()->toISOString(),
                'notes' => $validated['notes'] ?? null,
            ],
        ]);

        // Update order status if needed
        if ($order->status === 'pending_payment') {
            $order->update(['status' => 'in_progress']);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Manual payment recorded successfully.');
    }
}
