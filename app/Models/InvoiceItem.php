<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'billable_metric_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the billable metric associated with this item.
     */
    public function billableMetric(): BelongsTo
    {
        return $this->belongsTo(BillableMetric::class);
    }

    /**
     * Calculate the amount based on quantity and unit price.
     */
    public function calculateAmount(): void
    {
        $this->amount = $this->quantity * $this->unit_price;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if ($item->isDirty('quantity') || $item->isDirty('unit_price')) {
                $item->calculateAmount();
            }
        });
    }
}
