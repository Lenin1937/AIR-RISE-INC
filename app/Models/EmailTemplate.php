<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name', 'subject', 'preheader', 'body_html', 'body_text',
        'category', 'ai_generated', 'created_by',
    ];

    protected $casts = ['ai_generated' => 'boolean'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
