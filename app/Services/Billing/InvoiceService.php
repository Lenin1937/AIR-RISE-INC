<?php

namespace App\Services\Billing;

use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Models\Payment;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Create an invoice for a subscription.
     */
    public function createInvoiceForSubscription(Subscription $subscription): Invoice
    {
        $plan = $subscription->plan;
        $user = $subscription->user;

        // Calculate amounts
        $subtotal = $plan->price;
        $tax = $this->calculateTax($subtotal, $user);
        $discount = 0; // You can implement discount logic here
        $total = $subtotal + $tax - $discount;

        // Create invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'currency' => $plan->currency,
            'status' => 'pending',
            'due_date' => now()->addDays(7),
        ]);

        // Create invoice item
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => "Subscription: {$plan->name} - {$plan->billing_interval_description}",
            'quantity' => 1,
            'unit_price' => $plan->price,
            'amount' => $plan->price,
        ]);

        return $invoice;
    }

    /**
     * Create an invoice for usage-based billing.
     */
    public function createInvoiceForUsage(User $user, Subscription $subscription, array $metrics): Invoice
    {
        $subtotal = 0;

        // Create invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'subtotal' => 0, // Will be updated
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'currency' => $subscription->plan->currency,
            'status' => 'pending',
            'due_date' => now()->addDays(7),
        ]);

        // Create invoice items for each metric
        foreach ($metrics as $metric) {
            $amount = $metric['quantity'] * $metric['unit_price'];
            $subtotal += $amount;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $metric['description'],
                'quantity' => $metric['quantity'],
                'unit_price' => $metric['unit_price'],
                'amount' => $amount,
                'billable_metric_id' => $metric['billable_metric_id'] ?? null,
            ]);
        }

        // Calculate totals
        $tax = $this->calculateTax($subtotal, $user);
        $total = $subtotal + $tax;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        return $invoice;
    }

    /**
     * Mark an invoice as paid.
     */
    public function markInvoiceAsPaid(Invoice $invoice, ?Payment $payment = null): void
    {
        $invoice->markAsPaid($payment?->id);
    }

    /**
     * Mark overdue invoices.
     */
    public function markOverdueInvoices(): int
    {
        return Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }

    /**
     * Generate invoice for the current billing period.
     */
    public function generatePeriodInvoices(): void
    {
        // Get all active subscriptions where current period is ending today
        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('current_period_end', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            // Check if invoice already exists for this period
            $existingInvoice = Invoice::where('subscription_id', $subscription->id)
                ->whereBetween('created_at', [
                    $subscription->current_period_start,
                    $subscription->current_period_end,
                ])
                ->first();

            if (!$existingInvoice) {
                $this->createInvoiceForSubscription($subscription);
            }
        }
    }

    /**
     * Calculate tax based on user location.
     * This is a simple implementation - you should customize based on your tax rules.
     */
    protected function calculateTax(float $amount, User $user): float
    {
        // Example: 10% tax for specific countries
        $taxableCountries = ['US', 'GB', 'CA'];
        $taxRate = 0.10; // 10%

        if (in_array($user->country, $taxableCountries)) {
            return round($amount * $taxRate, 2);
        }

        return 0;
    }

    /**
     * Send invoice to customer via email.
     */
    public function sendInvoice(Invoice $invoice): void
    {
        // Implement email sending logic
        // You can use Laravel's Mail facade here
        // Mail::to($invoice->user->email)->send(new InvoiceMail($invoice));
    }

    /**
     * Generate invoice PDF.
     */
    public function generatePdf(Invoice $invoice): string
    {
        // Implement PDF generation logic
        // You can use libraries like dompdf or snappy
        // Return path to generated PDF
        
        return '';
    }

    /**
     * Void an invoice.
     */
    public function voidInvoice(Invoice $invoice): void
    {
        if ($invoice->status === 'paid') {
            throw new \Exception('Cannot void a paid invoice');
        }

        $invoice->update(['status' => 'void']);
    }

    /**
     * Apply discount to invoice.
     */
    public function applyDiscount(Invoice $invoice, float $discountAmount): void
    {
        if ($invoice->status === 'paid') {
            throw new \Exception('Cannot apply discount to a paid invoice');
        }

        $newTotal = $invoice->subtotal + $invoice->tax - $discountAmount;

        $invoice->update([
            'discount' => $discountAmount,
            'total' => max(0, $newTotal), // Ensure total is not negative
        ]);
    }
}
