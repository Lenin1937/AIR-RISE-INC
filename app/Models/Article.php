<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'read_time',
        'author',
        'tags',
        'featured',
        'published',
        'views',
        'meta_title',
        'meta_description',
        'featured_image',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'featured' => 'boolean',
            'published' => 'boolean',
            'views' => 'integer',
            'read_time' => 'integer',
        ];
    }

    /**
     * Get the author relationship (author is User model)
     */
    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    /**
     * Automatically categorize article based on content
     */
    public static function autoDetectCategory($title, $content, $tags = [])
    {
        $text = strtolower($title . ' ' . $content . ' ' . implode(' ', $tags));
        
        // C-Corporation keywords
        if (preg_match('/\b(c-corp|c corporation|venture capital|vc|ipo|investor|delaware|stock|shares|equity)\b/', $text)) {
            return 'c-corporation-formation';
        }
        
        // S-Corporation keywords  
        if (preg_match('/\b(s-corp|s corporation|form 2553|pass-through|self-employment tax|payroll)\b/', $text)) {
            return 's-corporation-formation';
        }
        
        // LLC keywords
        if (preg_match('/\b(llc|limited liability|operating agreement|single member|multi-member)\b/', $text)) {
            return 'llc-formation';
        }
        
        // Nonprofit keywords
        if (preg_match('/\b(nonprofit|non-profit|501\(c\)\(3\)|charity|charitable|form 1023|tax-exempt)\b/', $text)) {
            return 'nonprofit-organization';
        }
        
        // Green Card Lottery keywords
        if (preg_match('/\b(green card|diversity visa|dv|lottery|immigration|consular|interview)\b/', $text)) {
            return 'green-card-lottery-services';
        }
        
        // Default fallback
        return 'business-formation';
    }

    /**
     * Automatically generate slug from title
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($article) {
            if (!$article->slug) {
                $article->slug = Str::slug($article->title);
            }
            
            // Auto-categorize if category not set
            if (!$article->category) {
                $article->category = static::autoDetectCategory(
                    $article->title, 
                    $article->content, 
                    $article->tags ?? []
                );
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && !$article->isDirty('slug')) {
                $article->slug = Str::slug($article->title);
            }
            
            // Re-categorize if content changed significantly
            if ($article->isDirty('title') || $article->isDirty('content') || $article->isDirty('tags')) {
                if (!$article->isDirty('category')) {
                    $article->category = static::autoDetectCategory(
                        $article->title, 
                        $article->content, 
                        $article->tags ?? []
                    );
                }
            }
        });
    }

    /**
     * Scope for published articles
     */
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    /**
     * Scope for featured articles
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for articles by category
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for searching articles
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'ILIKE', "%{$search}%")
              ->orWhere('excerpt', 'ILIKE', "%{$search}%")
              ->orWhere('content', 'ILIKE', "%{$search}%")
              ->orWhereJsonContains('tags', $search);
        });
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Increment view count
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Get related articles
     */
    public function getRelatedArticles($limit = 3)
    {
        return static::published()
            ->where('category', $this->category)
            ->where('id', '!=', $this->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get formatted reading time
     */
    public function getFormattedReadTimeAttribute()
    {
        return $this->read_time . ' min read';
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayAttribute()
    {
        return ucwords(str_replace('-', ' ', $this->category));
    }

    /**
     * Get excerpt or truncated content
     */
    public function getExcerptOrContentAttribute()
    {
        return $this->excerpt ?: Str::limit(strip_tags($this->content), 200);
    }

    /**
     * Get all available categories
     */
    public static function getCategories()
    {
        return static::published()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(function ($category) {
                return ucwords(str_replace('-', ' ', $category));
            })
            ->toArray();
    }

    /**
     * Get popular articles
     */
    public static function getPopular($limit = 5)
    {
        return static::published()
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent articles
     */
    public static function getRecent($limit = 5)
    {
        return static::published()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}