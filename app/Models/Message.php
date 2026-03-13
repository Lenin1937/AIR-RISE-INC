<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'sender_id',
        'recipient_id',
        'order_id',
        'subject',
        'body',
        'type',
        'status',
        'priority',
        'thread_id',
        'reply_to_id',
        'is_thread_starter',
        'is_read',
        'read_at',
        'delivered_at',
        'attachments',
        'has_attachments',
        'sender_type',
        'sender_name',
        'sender_email',
        'is_internal',
        'is_automated',
        'requires_response',
        'is_pinned',
        'is_archived',
        'response_due_at',
        'assigned_to',
        'resolved_at',
        'resolution_notes',
        'template_id',
        'template_variables',
        'metadata',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'template_variables' => 'array',
            'metadata' => 'array',
            'is_thread_starter' => 'boolean',
            'is_read' => 'boolean',
            'has_attachments' => 'boolean',
            'is_internal' => 'boolean',
            'is_automated' => 'boolean',
            'requires_response' => 'boolean',
            'is_pinned' => 'boolean',
            'is_archived' => 'boolean',
            'read_at' => 'datetime',
            'delivered_at' => 'datetime',
            'response_due_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Generate unique message ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($message) {
            if (!$message->message_id) {
                $message->message_id = 'MSG-' . date('Y') . '-' . str_pad(
                    static::whereYear('created_at', date('Y'))->count() + 1, 
                    6, '0', STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Get the sender user
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient user
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the order this message belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the parent thread
     */
    public function thread()
    {
        return $this->belongsTo(Message::class, 'thread_id');
    }

    /**
     * Get the message this is replying to
     */
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Get replies to this message
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    /**
     * Get the assigned user
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all messages in this thread
     */
    public function threadMessages()
    {
        return $this->hasMany(Message::class, 'thread_id');
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Get type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'general' => 'General Message',
            'order_update' => 'Order Update',
            'document_request' => 'Document Request',
            'payment_reminder' => 'Payment Reminder',
            'compliance_notice' => 'Compliance Notice',
            'support_ticket' => 'Support Ticket',
            'system_notification' => 'System Notification',
            'welcome' => 'Welcome Message',
            'completion_notice' => 'Completion Notice',
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
     * Get sender display name
     */
    public function getSenderDisplayNameAttribute(): string
    {
        if ($this->sender_name) {
            return $this->sender_name;
        }
        
        if ($this->sender && $this->sender->first_name) {
            return $this->sender->first_name . ' ' . $this->sender->last_name;
        }
        
        if ($this->sender && $this->sender->name) {
            return $this->sender->name;
        }
        
        return 'System';
    }

    /**
     * Check if message is overdue for response
     */
    public function isOverdue(): bool
    {
        return $this->requires_response && 
               $this->response_due_at && 
               $this->response_due_at->isPast() && 
               !$this->resolved_at;
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for messages of specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for high priority messages
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Scope for thread starters only
     */
    public function scopeThreadStarters($query)
    {
        return $query->where('is_thread_starter', true);
    }

    /**
     * Scope for internal messages
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope for messages requiring response
     */
    public function scopeRequiringResponse($query)
    {
        return $query->where('requires_response', true)->whereNull('resolved_at');
    }
}