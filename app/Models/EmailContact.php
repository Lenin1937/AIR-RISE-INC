<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailContact extends Model
{
    protected $fillable = [
        'email', 'first_name', 'last_name', 'country', 'state', 'language',
        'client_type', 'service_type', 'source', 'tags',
        'subscribed_marketing', 'subscribed_transactional', 'unsubscribed_at', 'user_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'subscribed_marketing' => 'boolean',
        'subscribed_transactional' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: $this->email;
    }

    public function scopeSubscribed($query)
    {
        return $query->where('subscribed_marketing', true)->whereNull('unsubscribed_at');
    }

    public function scopeActive($query)
    {
        return $query->where('client_type', 'active');
    }
}
