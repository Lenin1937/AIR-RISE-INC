<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_interval',
        'billing_interval_count',
        'trial_days',
        'features',
        'max_users',
        'is_active',
        'stripe_price_id',
        'paypal_plan_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'billing_interval_count' => 'integer',
        'trial_days' => 'integer',
        'max_users' => 'integer',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Get the PayPal subscriptions for the plan.
     */
    public function paypalSubscriptions(): HasMany
    {
        return $this->hasMany(PayPalSubscription::class);
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get the billing interval description.
     */
    public function getBillingIntervalDescriptionAttribute(): string
    {
        $interval = $this->billing_interval_count > 1 
            ? $this->billing_interval_count . ' ' . str_plural($this->billing_interval)
            : $this->billing_interval;
            
        return 'per ' . $interval;
    }
}
