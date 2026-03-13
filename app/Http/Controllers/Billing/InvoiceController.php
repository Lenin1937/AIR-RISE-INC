<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Billing\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display a listing of user's invoices.
     */
    public function index()
    {
        $invoices = auth()->user()->invoices()
            ->with(['subscription.plan', 'payment'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Billing/Invoices', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Display a specific invoice.
     */
    public function show(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        $invoice->load(['items', 'subscription.plan', 'payment', 'user']);

        return Inertia::render('Billing/Invoice', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Download an invoice as PDF.
     */
    public function download(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        try {
            $pdfPath = $this->invoiceService->generatePdf($invoice);
            
            // Return PDF download
            // return response()->download($pdfPath, "invoice-{$invoice->invoice_number}.pdf");
            
            // For now, return a simple response
            return response()->json([
                'message' => 'PDF generation not implemented yet',
                'invoice_number' => $invoice->invoice_number,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get upcoming invoice for current subscription.
     */
    public function upcoming()
    {
        $user = auth()->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return response()->json(['error' => 'No active subscription'], 404);
        }

        // Calculate upcoming invoice based on subscription
        $upcomingInvoice = [
            'amount' => $subscription->plan->price,
            'currency' => $subscription->plan->currency,
            'billing_date' => $subscription->current_period_end,
            'plan_name' => $subscription->plan->name,
        ];

        return response()->json(['upcoming_invoice' => $upcomingInvoice]);
    }

    /**
     * Retry payment for an unpaid invoice.
     */
    public function retry(Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        if ($invoice->isPaid()) {
            return back()->withErrors(['error' => 'Invoice is already paid']);
        }

        // Redirect to payment page with invoice details
        return redirect()->route('billing.payment', [
            'invoice_id' => $invoice->id,
        ]);
    }
}
