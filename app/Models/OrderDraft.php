<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDraft extends Model
{
    protected $fillable = ['user_id', 'form_data'];

    protected $casts = ['form_data' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
