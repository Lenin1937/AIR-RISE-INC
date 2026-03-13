<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'username',
        'email',
        'phone',
        'telegram_username',
        'company_name',
        'password',
        'profile_picture',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'country',
        'citizenship',
        'preferred_language',
        'email_notifications',
        'sms_notifications',
        'terms_accepted',
        'terms_accepted_at',
        'privacy_accepted',
        'privacy_accepted_at',
        'marketing_consent',
        'timezone',
        'stripe_customer_id',
        'registration_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'registration_source',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ssn_encrypted',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified' => 'boolean',
            'kyc_verified' => 'boolean',
            'kyc_verified_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'terms_accepted' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted' => 'boolean',
            'privacy_accepted_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'data_retention_until' => 'datetime',
            'last_login_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->name ?: 'Unknown User';
    }

    /**
     * Get the user's name attribute (for compatibility).
     */
    public function getNameAttribute(): string
    {
        // If name is set in database, use it
        if (!empty($this->attributes['name'])) {
            return $this->attributes['name'];
        }
        
        // Otherwise, construct from first and last name
        return trim("{$this->first_name} {$this->last_name}") ?: 'Unknown User';
    }

    /**
     * Get the user's profile picture URL.
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return '/storage/' . $this->profile_picture;
        }
        
        // Generate initials avatar as fallback
        $initials = substr($this->first_name ?? $this->name, 0, 1) . substr($this->last_name ?? '', 0, 1);
        if (empty(trim($initials))) {
            $initials = substr($this->name ?? 'U', 0, 2);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&color=d4a02f&background=0b1e33&size=200';
    }

    /**
     * Check if user is admin (admin, staff, or super-admin)
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['administrator', 'staff', 'super-admin']);
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    /**
     * Get user's orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user's documents
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get user's payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get sent messages
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get received messages
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get user's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get user's KYC documents
     */
    public function kycDocuments()
    {
        return $this->hasMany(KycDocument::class);
    }

    /**
     * Get user's PayPal subscriptions
     * Note: Stripe subscriptions are handled by Cashier's Billable trait via subscriptions()
     */
    public function paypalSubscriptions()
    {
        return $this->hasMany(PayPalSubscription::class);
    }

    /**
     * Get user's active PayPal subscription
     */
    public function activePayPalSubscription()
    {
        return $this->hasOne(PayPalSubscription::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Get user's invoices
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get user's billable metrics
     */
    public function billableMetrics()
    {
        return $this->hasMany(BillableMetric::class);
    }

    /**
     * Check if user has an active subscription (Stripe or PayPal)
     */
    public function hasActiveSubscription(): bool
    {
        // Check Cashier Stripe subscriptions
        if ($this->subscribed()) {
            return true;
        }
        
        // Check PayPal subscriptions
        return $this->activePayPalSubscription()->exists();
    }

    /**
     * Check if user is subscribed to a specific plan
     */
    public function subscribedToPlan(int $planId): bool
    {
        // Check PayPal subscriptions
        return $this->activePayPalSubscription()
            ->where('plan_id', $planId)
            ->exists();
    }

    /**
     * Get all active subscriptions (both Stripe and PayPal)
     */
    public function allActiveSubscriptions()
    {
        $stripeSubscriptions = $this->subscriptions()
            ->where(function ($query) {
                $query->where('stripe_status', 'active')
                      ->orWhere('stripe_status', 'trialing');
            })
            ->get();
        
        $paypalSubscriptions = $this->paypalSubscriptions()
            ->active()
            ->get();
        
        return collect([
            'stripe' => $stripeSubscriptions,
            'paypal' => $paypalSubscriptions,
        ]);
    }
}
