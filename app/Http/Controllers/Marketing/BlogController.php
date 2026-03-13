<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::where('published', true);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title',    'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $posts = $query->latest()->paginate(9)->withQueryString();

        // Append image_url accessor to each post
        $posts->getCollection()->transform(fn($p) => $p->append('image_url'));

        $featured = BlogPost::where('published', true)
            ->where('featured', true)
            ->latest()
            ->take(3)
            ->get()
            ->each(fn($p) => $p->append('image_url'));

        $categories = BlogPost::where('published', true)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();

        return Inertia::render('Marketing/Blog/Index', [
            'posts'      => $posts,
            'featured'   => $featured,
            'categories' => $categories,
            'filters'    => [
                'search'   => $request->search   ?? '',
                'category' => $request->category ?? 'all',
            ],
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        $post->increment('views');
        $post->append('image_url');

        $related = BlogPost::where('published', true)
            ->where('category', $post->category)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(3)
            ->get()
            ->each(fn($p) => $p->append('image_url'));

        return Inertia::render('Marketing/Blog/Show', [
            'post'    => $post,
            'related' => $related,
        ]);
    }

    /**
     * Generate RSS feed for blog posts (for Zapier/social media automation).
     */
    public function feed()
    {
        $posts = BlogPost::where('published', true)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $content = view('blog.feed', [
            'posts'   => $posts,
            'baseUrl' => config('app.url'),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}

