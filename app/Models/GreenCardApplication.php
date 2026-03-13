<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GreenCardApplication extends Model
{
    protected $fillable = [
        'session_token', 'user_id', 'email', 'package_type', 'package_price', 'status',
        'current_step', 'first_name', 'middle_name', 'last_name', 'gender',
        'date_of_birth', 'city_of_birth', 'country_of_birth', 'country_of_eligibility',
        'passport_number', 'passport_country', 'passport_expiry',
        'address_line_1', 'address_line_2', 'city', 'state_province', 'postal_code',
        'country', 'phone', 'education_level', 'has_spouse', 'has_children',
        'primary_photo_path', 'payment_intent_id', 'payment_method', 'paid', 'paid_at',
        'confirmed_accuracy', 'confirmed_single_entry', 'confirmed_not_govt', 'confirmed_tos',
        'confirmation_number', 'internal_notes', 'submitted_at',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'passport_expiry' => 'date',
        'has_spouse'      => 'boolean',
        'has_children'    => 'boolean',
        'paid'            => 'boolean',
        'paid_at'         => 'datetime',
        'submitted_at'    => 'datetime',
        'confirmed_accuracy'     => 'boolean',
        'confirmed_single_entry' => 'boolean',
        'confirmed_not_govt'     => 'boolean',
        'confirmed_tos'          => 'boolean',
    ];

    public function familyMembers(): HasMany
    {
        return $this->hasMany(GreenCardFamilyMember::class, 'application_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GreenCardDocument::class, 'application_id');
    }

    public static function packagePrice(string $package): float
    {
        return match($package) {
            'family'  => 89.00,
            'premium' => 149.00,
            default   => 49.00,
        };
    }
}
