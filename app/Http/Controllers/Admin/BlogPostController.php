<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BlogPostController extends Controller
{
    private array $categories = [
        'General', 'Business Formation', 'Tax & Compliance',
        'Getting Started', 'Immigration', 'News & Updates',
    ];

    // ─── Index ──────────────────────────────────────────────────────────────
    public function index()
    {
        $posts = BlogPost::orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('Admin/Blog/Index', [
            'posts'      => $posts,
            'categories' => $this->categories,
        ]);
    }

    // ─── Create ─────────────────────────────────────────────────────────────
    public function create()
    {
        return Inertia::render('Admin/Blog/Create', [
            'categories' => $this->categories,
        ]);
    }

    // ─── Store ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'excerpt'          => 'nullable|string|max:600',
            'content'          => 'required|string',
            'category'         => 'required|string|max:100',
            'author'           => 'nullable|string|max:255',
            'tags'             => 'nullable|string',
            'read_time'        => 'nullable|integer|min:1|max:120',
            'featured'         => 'boolean',
            'published'        => 'boolean',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blog-images', 'public');
        }

        BlogPost::create([
            'title'            => $validated['title'],
            'slug'             => BlogPost::generateSlug($validated['title']),
            'excerpt'          => $validated['excerpt'] ?? '',
            'content'          => $validated['content'],
            'image'            => $imagePath,
            'category'         => $validated['category'],
            'author'           => $validated['author'] ?? 'CORPIUS Team',
            'tags'             => $validated['tags']
                                    ? array_filter(array_map('trim', explode(',', $validated['tags'])))
                                    : [],
            'read_time'        => $validated['read_time'] ?? $this->calcReadTime($validated['content']),
            'featured'         => $validated['featured'] ?? false,
            'published'        => $validated['published'] ?? false,
            'meta_title'       => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        return Redirect::route('admin.blog.index')
            ->with('success', 'Blog post created successfully.');
    }

    // ─── Edit ───────────────────────────────────────────────────────────────
    public function edit(BlogPost $blog)
    {
        return Inertia::render('Admin/Blog/Edit', [
            'post'       => $blog,
            'categories' => $this->categories,
        ]);
    }

    // ─── Update ─────────────────────────────────────────────────────────────
    public function update(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'excerpt'          => 'nullable|string|max:600',
            'content'          => 'required|string',
            'category'         => 'required|string|max:100',
            'author'           => 'nullable|string|max:255',
            'tags'             => 'nullable|string',
            'read_time'        => 'nullable|integer|min:1|max:120',
            'featured'         => 'boolean',
            'published'        => 'boolean',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_image'     => 'boolean',
        ]);

        $imagePath = $blog->image;

        // Remove existing image
        if ($request->boolean('remove_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        // Upload new image
        if ($request->hasFile('image')) {
            // Delete old
            if ($blog->image) {
                Storage::disk('public')->delete($blog->image);
            }
            $imagePath = $request->file('image')->store('blog-images', 'public');
        }

        // Regenerate slug only if title changed
        $slug = $blog->slug;
        if ($blog->title !== $validated['title']) {
            $slug = BlogPost::generateSlug($validated['title'], $blog->id);
        }

        $blog->update([
            'title'            => $validated['title'],
            'slug'             => $slug,
            'excerpt'          => $validated['excerpt'] ?? '',
            'content'          => $validated['content'],
            'image'            => $imagePath,
            'category'         => $validated['category'],
            'author'           => $validated['author'] ?? 'CORPIUS Team',
            'tags'             => $validated['tags']
                                    ? array_filter(array_map('trim', explode(',', $validated['tags'])))
                                    : [],
            'read_time'        => $validated['read_time'] ?? $this->calcReadTime($validated['content']),
            'featured'         => $validated['featured'] ?? false,
            'published'        => $validated['published'] ?? false,
            'meta_title'       => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        return Redirect::route('admin.blog.index')
            ->with('success', 'Blog post updated successfully.');
    }

    // ─── Destroy ────────────────────────────────────────────────────────────
    public function destroy(BlogPost $blog)
    {
        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }
        $blog->delete();

        return Redirect::route('admin.blog.index')
            ->with('success', 'Blog post deleted.');
    }

    // ─── Toggle publish ─────────────────────────────────────────────────────
    public function togglePublish(BlogPost $blog)
    {
        $blog->update(['published' => !$blog->published]);

        return back()->with('success', $blog->published ? 'Post published.' : 'Post unpublished.');
    }

    // ─── AI Generate ────────────────────────────────────────────────────────
    public function generateAI(Request $request, OpenAiService $openai)
    {
        $validated = $request->validate([
            'topic'    => 'required|string|max:500',
            'category' => 'nullable|string|max:100',
            'tone'     => 'nullable|string|in:professional,friendly,technical,educational',
        ]);

        $result = $openai->generateBlogPost(
            $validated['topic'],
            $validated['category'] ?? 'General',
            $validated['tone']     ?? 'professional'
        );

        if (! $result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result['data']);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────
    private function calcReadTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, (int) ceil($wordCount / 200));
    }
}
