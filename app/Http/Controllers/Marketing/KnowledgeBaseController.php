<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::where('published', true);

        // Filter by category
        if ($request->category && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Get paginated articles
        $articles = $query->latest()->paginate(12);

        // Get featured articles
        $featuredArticles = Article::where('published', true)
            ->where('featured', true)
            ->latest()
            ->take(3)
            ->get();

        return Inertia::render('Marketing/KnowledgeBase', [
            'articles' => $articles,
            'featured_articles' => $featuredArticles,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category ?? 'all',
            ],
        ]);
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        // Get related articles from the same category
        $relatedArticles = Article::where('published', true)
            ->where('category', $article->category)
            ->where('id', '!=', $article->id)
            ->latest()
            ->take(3)
            ->get();

        // Increment view count
        $article->increment('views');

        return Inertia::render('Marketing/KnowledgeBaseShow', [
            'article' => $article,
            'related_articles' => $relatedArticles
        ]);
    }
}
