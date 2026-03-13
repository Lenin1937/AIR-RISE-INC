<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillableMetric extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'metric_name',
        'value',
        'aggregation_type',
        'unit',
        'recorded_at',
        'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'recorded_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the metric.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that the metric belongs to.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the invoice items for this metric.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get aggregated value for a user and metric.
     */
    public static function getAggregatedValue(int $userId, string $metricName, $start = null, $end = null)
    {
        $query = static::where('user_id', $userId)
            ->where('metric_name', $metricName);

        if ($start) {
            $query->where('recorded_at', '>=', $start);
        }

        if ($end) {
            $query->where('recorded_at', '<=', $end);
        }

        $metric = $query->first();
        
        if (!$metric) {
            return 0;
        }

        switch ($metric->aggregation_type) {
            case 'sum':
                return $query->sum('value');
            case 'count':
                return $query->count();
            case 'max':
                return $query->max('value');
            case 'last':
                return $query->orderBy('recorded_at', 'desc')->first()->value ?? 0;
            default:
                return $query->sum('value');
        }
    }

    /**
     * Record a new metric.
     */
    public static function record(int $userId, string $metricName, float $value, array $options = []): self
    {
        return static::create([
            'user_id' => $userId,
            'subscription_id' => $options['subscription_id'] ?? null,
            'metric_name' => $metricName,
            'value' => $value,
            'aggregation_type' => $options['aggregation_type'] ?? 'sum',
            'unit' => $options['unit'] ?? null,
            'recorded_at' => $options['recorded_at'] ?? now(),
            'metadata' => $options['metadata'] ?? null,
        ]);
    }
}
