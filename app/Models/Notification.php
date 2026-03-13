<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'user_id',
        'order_id',
        'title',
        'message',
        'type',
        'is_read',
        'priority',
        'is_actionable',
        'action_url',
        'action_text',
        'action_data',
        'sent_email',
        'sent_sms',
        'sent_push',
        'email_sent_at',
        'sms_sent_at',
        'read_at',
        'clicked_at',
        'dismissed_at',
        'metadata',
        'correlation_id',
        'expires_at',
        'auto_delete_after_read',
    ];

    protected function casts(): array
    {
        return [
            'action_data' => 'array',
            'metadata' => 'array',
            'is_read' => 'boolean',
            'is_actionable' => 'boolean',
            'sent_email' => 'boolean',
            'sent_sms' => 'boolean',
            'sent_push' => 'boolean',
            'auto_delete_after_read' => 'boolean',
            'email_sent_at' => 'datetime',
            'sms_sent_at' => 'datetime',
            'read_at' => 'datetime',
            'clicked_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Generate unique notification ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($notification) {
            if (!$notification->notification_id) {
                $notification->notification_id = 'NOT-' . date('Y') . '-' . str_pad(
                    static::whereYear('created_at', date('Y'))->count() + 1, 
                    6, '0', STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order this notification belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            // Auto-delete if configured
            if ($this->auto_delete_after_read) {
                $this->delete();
            }
        }
    }

    /**
     * Mark notification as clicked
     */
    public function markAsClicked()
    {
        $this->update(['clicked_at' => now()]);
        
        if (!$this->is_read) {
            $this->markAsRead();
        }
    }

    /**
     * Dismiss notification
     */
    public function dismiss()
    {
        $this->update(['dismissed_at' => now()]);
    }

    /**
     * Get type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'order_update' => 'Order Update',
            'payment_received' => 'Payment Received',
            'document_ready' => 'Document Ready',
            'document_required' => 'Document Required',
            'compliance_reminder' => 'Compliance Reminder',
            'welcome' => 'Welcome',
            'completion' => 'Completion Notice',
            'system_alert' => 'System Alert',
            'promotion' => 'Promotion',
            'security' => 'Security Alert',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            'low' => 'gray',
            default => 'blue',
        };
    }

    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for notifications of specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for high priority notifications
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Scope for actionable notifications
     */
    public function scopeActionable($query)
    {
        return $query->where('is_actionable', true);
    }

    /**
     * Scope for non-expired notifications
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}