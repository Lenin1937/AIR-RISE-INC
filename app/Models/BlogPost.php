<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'image',
        'category', 'author', 'tags', 'featured', 'published',
        'read_time', 'views', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'tags'      => 'array',
        'featured'  => 'boolean',
        'published' => 'boolean',
        'views'     => 'integer',
        'read_time' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    // Helpers
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        // If already a full URL return as-is
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    public static function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
