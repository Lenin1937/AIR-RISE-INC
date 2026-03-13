<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GreenCardFamilyMember extends Model
{
    protected $fillable = [
        'application_id', 'type', 'first_name', 'last_name',
        'date_of_birth', 'country_of_birth', 'gender', 'photo_path',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(GreenCardApplication::class, 'application_id');
    }
}
