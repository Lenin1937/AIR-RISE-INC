<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAutomation extends Model
{
    protected $fillable = [
        'name', 'description', 'trigger', 'trigger_conditions',
        'status', 'steps', 'enrolled_count', 'completed_count',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'steps'              => 'array',
    ];

    public function getStepCountAttribute(): int
    {
        return count($this->steps ?? []);
    }
}
