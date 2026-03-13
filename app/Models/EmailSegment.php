<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSegment extends Model
{
    protected $fillable = ['name', 'description', 'conditions', 'contact_count', 'auto_update'];

    protected $casts = [
        'conditions' => 'array',
        'auto_update' => 'boolean',
    ];

    public function refreshCount(): void
    {
        $this->update(['contact_count' => $this->resolveContacts()->count()]);
    }

    public function resolveContacts()
    {
        $query = EmailContact::query();
        foreach ($this->conditions ?? [] as $i => $cond) {
            $method = $i === 0 || ($cond['logic'] ?? 'AND') === 'AND' ? 'where' : 'orWhere';
            $field    = $cond['field']    ?? null;
            $operator = $cond['operator'] ?? '=';
            $value    = $cond['value']    ?? null;

            if (!$field) continue;

            match ($operator) {
                'contains'     => $query->{$method}($field, 'like', "%{$value}%"),
                'not_contains' => $query->{"{$method}Not"}($field, 'like', "%{$value}%"),
                'is_null'      => $query->{"{$method}Null"}($field),
                'is_not_null'  => $query->{"{$method}NotNull"}($field),
                default        => $query->{$method}($field, $operator, $value),
            };
        }
        return $query;
    }
}
