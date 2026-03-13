<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_chat_id',
        'role',
        'content',
        'metadata',
        'model',
        'tokens_used',
        'response_time_ms',
        'is_streamed',
        'is_error',
        'error_message',
        'is_flagged',
        'page_context',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_streamed' => 'boolean',
            'is_error' => 'boolean',
            'is_flagged' => 'boolean',
        ];
    }

    /**
     * Get the chat that owns the message
     */
    public function chat()
    {
        return $this->belongsTo(AiChat::class, 'ai_chat_id');
    }

    /**
     * Scope for user messages
     */
    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    /**
     * Scope for assistant messages
     */
    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }

    /**
     * Scope for error messages
     */
    public function scopeErrors($query)
    {
        return $query->where('is_error', true);
    }

    /**
     * Mark as flagged
     */
    public function flag()
    {
        $this->update(['is_flagged' => true]);
    }

    /**
     * Unflag message
     */
    public function unflag()
    {
        $this->update(['is_flagged' => false]);
    }
}
