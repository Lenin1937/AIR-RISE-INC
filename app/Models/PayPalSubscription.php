<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PayPalSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'type',
        'paypal_subscription_id',
        'paypal_plan_id',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'canceled_at',
        'ends_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan associated with the subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Determine if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->ends_at || $this->ends_at->isFuture());
    }

    /**
     * Determine if the subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled' || $this->canceled_at !== null;
    }

    /**
     * Determine if the subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the subscription is within its grace period.
     */
    public function onGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Filter query to active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('ends_at')
                              ->orWhere('ends_at', '>', now());
                    });
    }

    /**
     * Filter query to cancelled subscriptions.
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled')
                    ->orWhereNotNull('canceled_at');
    }

    /**
     * Determine if the subscription is recurring and not on grace period.
     */
    public function recurring(): bool
    {
        return !$this->onGracePeriod() && $this->isActive();
    }

    /**
     * Determine if the subscription has ended.
     */
    public function ended(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }
}
