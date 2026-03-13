<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AiChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'page_url',
        'page_name',
        'user_type',
        'ip_address',
        'user_agent',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'status',
        'is_lead',
        'message_count',
        'assigned_to',
        'last_message_at',
        'closed_at',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_lead' => 'boolean',
            'last_message_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Boot method to auto-generate session ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($chat) {
            if (!$chat->session_id) {
                $chat->session_id = 'chat_' . Str::random(32);
            }
        });
    }

    /**
     * Get the user that owns the chat
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages for this chat
     */
    public function messages()
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at');
    }

    /**
     * Get the admin assigned to this chat
     */
    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the last message
     */
    public function lastMessage()
    {
        return $this->hasOne(AiMessage::class)->latestOfMany();
    }

    /**
     * Scope for active chats
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for lead chats
     */
    public function scopeLeads($query)
    {
        return $query->where('is_lead', true);
    }

    /**
     * Scope for guest chats
     */
    public function scopeGuests($query)
    {
        return $query->where('user_type', 'guest');
    }

    /**
     * Scope for client chats
     */
    public function scopeClients($query)
    {
        return $query->where('user_type', 'client');
    }

    /**
     * Mark chat as closed
     */
    public function close()
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Mark chat as resolved
     */
    public function resolve()
    {
        $this->update([
            'status' => 'resolved',
            'closed_at' => now(),
        ]);
    }

    /**
     * Increment message count
     */
    public function incrementMessageCount()
    {
        $this->increment('message_count');
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Mark as lead
     */
    public function markAsLead($email, $additionalData = [])
    {
        $this->update([
            'is_lead' => true,
            'visitor_email' => $email,
            'visitor_name' => $additionalData['name'] ?? $this->visitor_name,
            'visitor_phone' => $additionalData['phone'] ?? $this->visitor_phone,
        ]);
    }
}
