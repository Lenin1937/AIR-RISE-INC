<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'gateway',
        'event_type',
        'payload',
        'processed',
        'processed_at',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Check if the event has already been processed.
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * Mark the event as processed.
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark the event as failed with an error message.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'error' => $error,
        ]);
    }

    /**
     * Check if an event has already been recorded.
     */
    public static function exists(string $eventId, string $gateway): bool
    {
        return static::where('event_id', $eventId)
            ->where('gateway', $gateway)
            ->exists();
    }

    /**
     * Record a new webhook event.
     */
    public static function record(string $eventId, string $gateway, string $eventType, array $payload): self
    {
        return static::create([
            'event_id' => $eventId,
            'gateway' => $gateway,
            'event_type' => $eventType,
            'payload' => $payload,
        ]);
    }

    /**
     * Scope a query to only include unprocessed events.
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope a query to only include processed events.
     */
    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

    /**
     * Scope a query to only include failed events.
     */
    public function scopeFailed($query)
    {
        return $query->where('processed', true)
            ->whereNotNull('error');
    }
}
