<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GreenCardDocument extends Model
{
    protected $fillable = [
        'application_id', 'document_type', 'label', 'file_path',
        'original_name', 'file_size', 'mime_type',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(GreenCardApplication::class, 'application_id');
    }
}
