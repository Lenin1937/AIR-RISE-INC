<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Article;

class KnowledgeBaseController extends Controller
{
    /**
     * Service categories mapping
     */
    private $serviceCategories = [
        'c-corporation-formation' => 'C-Corporation Formation',
        's-corporation-formation' => 'S-Corporation Formation',
        'llc-formation' => 'LLC Formation',
        'nonprofit-organization' => 'Nonprofit Organization',
        'green-card-lottery-services' => 'Green Card Lottery Services'
    ];

    /**
     * Display knowledge base main page with service categories
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search', '');
        
        $query = Article::published();
        
        // Apply category filter
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }
        
        // Apply search filter
        if ($search) {
            $query->search($search);
        }
        
        // Get articles with pagination
        $articles = $query->orderBy('featured', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(12);
        
        // Get featured articles for each service
        $featured_articles = Article::published()
                                   ->featured()
                                   ->orderBy('created_at', 'desc')
                                   ->limit(5)
                                   ->get();
        
        // Get service categories with article counts
        $service_categories = [];
        foreach ($this->serviceCategories as $key => $name) {
            $service_categories[] = [
                'key' => $key,
                'name' => $name,
                'slug' => $key,
                'count' => Article::published()->where('category', $key)->count(),
                'description' => $this->getCategoryDescription($key),
                'icon' => $this->getCategoryIcon($key),
                'featured_article' => Article::published()
                                           ->where('category', $key)
                                           ->featured()
                                           ->first()
            ];
        }

        return Inertia::render('KnowledgeBase/Index', [
            'articles' => $articles,
            'service_categories' => $service_categories,
            'featured_articles' => $featured_articles,
            'filters' => [
                'category' => $category,
                'search' => $search
            ],
            'stats' => [
                'total_articles' => Article::published()->count(),
                'total_categories' => count($this->serviceCategories)
            ]
        ]);
    }

    /**
     * Display articles for a specific service category
     */
    public function category($category)
    {
        if (!array_key_exists($category, $this->serviceCategories)) {
            abort(404);
        }

        $articles = Article::published()
                          ->where('category', $category)
                          ->orderBy('featured', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(12);
        
        $category_info = [
            'key' => $category,
            'name' => $this->serviceCategories[$category],
            'description' => $this->getCategoryDescription($category),
            'icon' => $this->getCategoryIcon($category)
        ];
        
        // Get featured article for this category
        $featured_article = Article::published()
                                  ->where('category', $category)
                                  ->featured()
                                  ->first();

        return Inertia::render('Marketing/KnowledgeBaseCategory', [
            'articles' => $articles,
            'category' => $category_info,
            'featured_article' => $featured_article,
            'service_categories' => $this->getServiceCategoriesForMenu()
        ]);
    }

    /**
     * Display the specified article
     */
    public function show($slug)
    {
        $article = Article::published()
                          ->where('slug', $slug)
                          ->firstOrFail();
        
        // Increment views count
        $article->increment('views');
        
        // Get related articles (same category, excluding current article)
        $related_articles = Article::published()
                                  ->where('category', $article->category)
                                  ->where('id', '!=', $article->id)
                                  ->orderBy('created_at', 'desc')
                                  ->limit(4)
                                  ->get();

        // Get category info
        $category_info = [
            'key' => $article->category,
            'name' => $this->serviceCategories[$article->category] ?? $article->category,
            'description' => $this->getCategoryDescription($article->category)
        ];

        return Inertia::render('KnowledgeBase/Show', [
            'article' => $article,
            'related_articles' => $related_articles,
            'category' => $category_info,
            'service_categories' => $this->getServiceCategoriesForMenu()
        ]);
    }

    /**
     * Search knowledge base articles
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $category = $request->get('category', 'all');
        
        $articlesQuery = Article::published();
        
        if ($query) {
            $articlesQuery->search($query);
        }
        
        if ($category && $category !== 'all') {
            $articlesQuery->where('category', $category);
        }
        
        $results = $articlesQuery->orderBy('featured', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->limit(20)
                                ->get()
                                ->map(function ($article) {
                                    return [
                                        'id' => $article->id,
                                        'title' => $article->title,
                                        'excerpt' => $article->excerpt,
                                        'slug' => $article->slug,
                                        'category' => $article->category,
                                        'category_name' => $this->serviceCategories[$article->category] ?? $article->category,
                                        'read_time' => $article->read_time,
                                        'views' => $article->views,
                                        'created_at' => $article->created_at->format('M j, Y')
                                    ];
                                });
        
        return response()->json([
            'results' => $results,
            'query' => $query,
            'category' => $category,
            'total' => $results->count()
        ]);
    }

    /**
     * Get popular articles across all categories
     */
    public function popular()
    {
        $articles = Article::published()
                          ->orderBy('views', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(12);
        
        return Inertia::render('Marketing/KnowledgeBasePopular', [
            'articles' => $articles,
            'service_categories' => $this->getServiceCategoriesForMenu()
        ]);
    }

    /**
     * Get service categories for navigation menu
     */
    private function getServiceCategoriesForMenu()
    {
        $categories = [];
        foreach ($this->serviceCategories as $key => $name) {
            $categories[] = [
                'key' => $key,
                'name' => $name,
                'slug' => $key,
                'count' => Article::published()->where('category', $key)->count()
            ];
        }
        return $categories;
    }

    /**
     * Get category description
     */
    private function getCategoryDescription($category)
    {
        $descriptions = [
            'c-corporation-formation' => 'Complete guides for forming C-Corporations, including Delaware incorporation, investor requirements, and corporate governance.',
            's-corporation-formation' => 'Expert guidance on S-Corporation formation, tax elections, and optimization strategies for small businesses.',
            'llc-formation' => 'Comprehensive resources for LLC formation, operating agreements, and multi-state business operations.',
            'nonprofit-organization' => 'Step-by-step guides for 501(c)(3) formation, tax-exempt status, and nonprofit compliance requirements.',
            'green-card-lottery-services' => 'Complete assistance with Diversity Visa lottery applications, winner processing, and immigration procedures.'
        ];

        return $descriptions[$category] ?? 'Professional business formation and legal services.';
    }

    /**
     * Get category icon
     */
    private function getCategoryIcon($category)
    {
        $icons = [
            'c-corporation-formation' => 'BuildingOfficeIcon',
            's-corporation-formation' => 'DocumentTextIcon',
            'llc-formation' => 'ShieldCheckIcon',
            'nonprofit-organization' => 'HeartIcon',
            'green-card-lottery-services' => 'GlobeAltIcon'
        ];

        return $icons[$category] ?? 'DocumentIcon';
    }
}