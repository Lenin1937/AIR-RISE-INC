<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'user_id',
        'order_id',
        'amount',
        'currency',
        'type',
        'description',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_customer_id',
        'stripe_payment_method_id',
        'status',
        'payment_method',
        'card_last_four',
        'card_brand',
        'card_exp_month',
        'card_exp_year',
        'billing_address',
        'processing_fee',
        'net_amount',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'receipt_url',
        'invoice_number',
        'receipt_sent',
        'stripe_response',
        'failure_code',
        'failure_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'billing_address' => 'array',
            'stripe_response' => 'array',
            'receipt_sent' => 'boolean',
            'refunded_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Generate unique payment ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (!$payment->payment_id) {
                $payment->payment_id = 'PAY-' . date('Y') . '-' . str_pad(
                    static::whereYear('created_at', date('Y'))->count() + 1, 
                    6, '0', STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Get the user that owns the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order this payment belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted net amount
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return '$' . number_format($this->net_amount, 2);
    }

    /**
     * Get formatted processing fee
     */
    public function getFormattedProcessingFeeAttribute(): string
    {
        return '$' . number_format($this->processing_fee, 2);
    }

    /**
     * Get payment type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'order_payment' => 'Order Payment',
            'additional_fee' => 'Additional Fee',
            'refund' => 'Refund',
            'renewal' => 'Renewal Payment',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get payment method display name
     */
    public function getPaymentMethodDisplayNameAttribute(): string
    {
        return match($this->payment_method) {
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'ach' => 'ACH Transfer',
            'wire' => 'Wire Transfer',
            'check' => 'Check',
            'cash' => 'Cash',
            'apple_pay' => 'Apple Pay',
            'google_pay' => 'Google Pay',
            'crypto' => 'Cryptocurrency',
            'bitcoin' => 'Bitcoin',
            'ethereum' => 'Ethereum',
            'usdc' => 'USD Coin',
            'usdt' => 'Tether',
            'paypal' => 'PayPal',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    /**
     * Get masked card number
     */
    public function getMaskedCardNumberAttribute(): string
    {
        if ($this->card_last_four) {
            return '**** **** **** ' . $this->card_last_four;
        }
        return '';
    }

    /**
     * Get card display information
     */
    public function getCardDisplayAttribute(): string
    {
        if ($this->card_brand && $this->card_last_four) {
            return ucfirst($this->card_brand) . ' ending in ' . $this->card_last_four;
        }
        return $this->payment_method_display_name;
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->isSuccessful() && 
               $this->type !== 'refund' && 
               $this->refunded_amount < $this->amount &&
               $this->created_at->gt(now()->subDays(90)); // 90-day refund window
    }

    /**
     * Calculate remaining refundable amount
     */
    public function getRemainingRefundableAmountAttribute(): float
    {
        return $this->amount - $this->refunded_amount;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'succeeded' => 'green',
            'pending', 'processing' => 'yellow',
            'failed', 'cancelled' => 'red',
            'refunded', 'partially_refunded' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'succeeded');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'cancelled']);
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', ['refunded', 'partially_refunded']);
    }

    /**
     * Scope for payments by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
