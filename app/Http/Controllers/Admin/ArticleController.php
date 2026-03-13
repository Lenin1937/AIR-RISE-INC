<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class ArticleController extends Controller
{
    /**
     * Service categories for auto-categorization
     */
    private $serviceCategories = [
        'c-corporation-formation' => [
            'keywords' => ['c-corp', 'c corp', 'corporation', 'corporate', 'venture capital', 'vc', 'startup', 'investment', 'shares', 'stock', 'board of directors', 'bylaws'],
            'name' => 'C-Corporation Formation'
        ],
        's-corporation-formation' => [
            'keywords' => ['s-corp', 's corp', 's corporation', 'form 2553', 'pass-through', 'self-employment tax', 'reasonable salary', 'payroll'],
            'name' => 'S-Corporation Formation'
        ],
        'llc-formation' => [
            'keywords' => ['llc', 'limited liability company', 'operating agreement', 'member', 'single member', 'multi-member', 'flexible', 'pass-through taxation'],
            'name' => 'LLC Formation'
        ],
        'nonprofit-organization' => [
            'keywords' => ['nonprofit', 'non-profit', '501(c)(3)', 'charity', 'charitable', 'tax-exempt', 'donation', 'fundraising', 'irs form 1023'],
            'name' => 'Nonprofit Organization'
        ],
        'green-card-lottery-services' => [
            'keywords' => ['green card', 'diversity visa', 'dv lottery', 'immigration', 'consular processing', 'visa interview', 'immigrant visa'],
            'name' => 'Green Card Lottery Services'
        ]
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
            'categories' => $this->getServiceCategories()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Articles/Create', [
            'categories' => $this->getServiceCategories()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'read_time' => 'nullable|integer|min:1|max:60',
            'author' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'featured' => 'boolean',
            'published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
        ]);

        // Auto-categorize based on content
        $category = $this->autoCategorize($validated['title'] . ' ' . $validated['excerpt'] . ' ' . $validated['content']);
        $validated['category'] = $category;

        // Auto-generate read time if not provided
        if (!isset($validated['read_time'])) {
            $validated['read_time'] = $this->calculateReadTime($validated['content']);
        }

        // Set default author
        if (!isset($validated['author'])) {
            $validated['author'] = 'iCorp Pro Legal Team';
        }

        // Auto-generate slug
        $validated['slug'] = Str::slug($validated['title']);

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Article::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $article = Article::create($validated);

        return Redirect::route('admin.articles.index')
            ->with('success', 'Article created successfully and auto-categorized as: ' . $this->serviceCategories[$category]['name']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return Inertia::render('Admin/Articles/Show', [
            'article' => $article
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        return Inertia::render('Admin/Articles/Edit', [
            'article' => $article,
            'categories' => $this->getServiceCategories()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'read_time' => 'nullable|integer|min:1|max:60',
            'author' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'featured' => 'boolean',
            'published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
        ]);

        // Re-categorize if content changed significantly
        if ($article->title !== $validated['title'] || $article->content !== $validated['content']) {
            $category = $this->autoCategorize($validated['title'] . ' ' . $validated['excerpt'] . ' ' . $validated['content']);
            $validated['category'] = $category;
        }

        // Auto-generate read time if not provided
        if (!isset($validated['read_time'])) {
            $validated['read_time'] = $this->calculateReadTime($validated['content']);
        }

        // Update slug if title changed
        if ($article->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Article::where('slug', $validated['slug'])->where('id', '!=', $article->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $article->update($validated);

        return Redirect::route('admin.articles.index')
            ->with('success', 'Article updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return Redirect::route('admin.articles.index')
            ->with('success', 'Article deleted successfully!');
    }

    /**
     * Auto-categorize article based on content
     */
    private function autoCategorize(string $content): string
    {
        $content = strtolower($content);
        $categoryScores = [];

        foreach ($this->serviceCategories as $category => $data) {
            $score = 0;
            foreach ($data['keywords'] as $keyword) {
                $keywordCount = substr_count($content, strtolower($keyword));
                $score += $keywordCount * strlen($keyword); // Weight by keyword length
            }
            $categoryScores[$category] = $score;
        }

        // Return category with highest score, default to LLC formation
        $bestCategory = array_keys($categoryScores, max($categoryScores))[0];
        return max($categoryScores) > 0 ? $bestCategory : 'llc-formation';
    }

    /**
     * Calculate reading time based on content
     */
    private function calculateReadTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingSpeed = 200; // Average words per minute
        $readTime = ceil($wordCount / $readingSpeed);
        
        return max(1, $readTime); // Minimum 1 minute
    }

    /**
     * Get service categories for dropdown
     */
    private function getServiceCategories(): array
    {
        $categories = [];
        foreach ($this->serviceCategories as $key => $data) {
            $categories[] = [
                'value' => $key,
                'label' => $data['name']
            ];
        }
        return $categories;
    }

    /**
     * Bulk actions for articles
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,unpublish,feature,unfeature,delete',
            'article_ids' => 'required|array',
            'article_ids.*' => 'exists:articles,id'
        ]);

        $articles = Article::whereIn('id', $validated['article_ids']);

        switch ($validated['action']) {
            case 'publish':
                $articles->update(['published' => true]);
                $message = 'Articles published successfully!';
                break;
            case 'unpublish':
                $articles->update(['published' => false]);
                $message = 'Articles unpublished successfully!';
                break;
            case 'feature':
                $articles->update(['featured' => true]);
                $message = 'Articles featured successfully!';
                break;
            case 'unfeature':
                $articles->update(['featured' => false]);
                $message = 'Articles unfeatured successfully!';
                break;
            case 'delete':
                $articles->delete();
                $message = 'Articles deleted successfully!';
                break;
        }

        return Redirect::route('admin.articles.index')->with('success', $message);
    }

    /**
     * Get statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total' => Article::count(),
            'published' => Article::published()->count(),
            'featured' => Article::featured()->count(),
            'drafts' => Article::where('published', false)->count(),
            'by_category' => []
        ];

        foreach ($this->serviceCategories as $key => $data) {
            $stats['by_category'][$key] = [
                'name' => $data['name'],
                'count' => Article::where('category', $key)->count()
            ];
        }

        return response()->json($stats);
    }
}
