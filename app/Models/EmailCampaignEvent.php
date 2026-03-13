<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['campaign_id', 'contact_email', 'event_type', 'url', 'metadata', 'occurred_at'];

    protected $casts = [
        'metadata'    => 'array',
        'occurred_at' => 'datetime',
    ];

    public function campaign(): BelongsTo { return $this->belongsTo(EmailCampaign::class); }
}
