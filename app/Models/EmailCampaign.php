<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $fillable = [
        'name', 'template_id', 'segment_id', 'status',
        'from_name', 'from_email', 'reply_to',
        'scheduled_at', 'sent_at',
        'total_sent', 'total_delivered', 'total_opened',
        'total_clicked', 'total_bounced', 'total_unsubscribed',
        'ab_test', 'created_by',
    ];

    protected $casts = [
        'ab_test'      => 'array',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public function template(): BelongsTo  { return $this->belongsTo(EmailTemplate::class); }
    public function segment(): BelongsTo   { return $this->belongsTo(EmailSegment::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function events(): HasMany      { return $this->hasMany(EmailCampaignEvent::class, 'campaign_id'); }

    public function getOpenRateAttribute(): float
    {
        return $this->total_delivered > 0 ? round($this->total_opened / $this->total_delivered * 100, 1) : 0;
    }

    public function getClickRateAttribute(): float
    {
        return $this->total_delivered > 0 ? round($this->total_clicked / $this->total_delivered * 100, 1) : 0;
    }
}
