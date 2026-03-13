<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate the XML sitemap for the site.
     */
    public function index(): Response
    {
        $baseUrl = config('app.url');

        // Static marketing pages with their priorities and change frequencies
        $staticPages = [
            ['url' => '/',                          'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => '/pricing',                   'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => '/about',                     'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => '/contact',                   'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/knowledge-base',            'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => '/blog',                      'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => '/services/llc',              'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => '/services/c-corporation',    'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => '/services/s-corporation',    'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => '/services/nonprofit',        'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/services/green-card-lottery','priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/services/income-tax-filing-planning', 'priority' => '0.8', 'changefreq' => 'monthly'],
            // Legal pages
            ['url' => '/privacy-policy',             'priority' => '0.4', 'changefreq' => 'yearly'],
            ['url' => '/terms-of-service',           'priority' => '0.4', 'changefreq' => 'yearly'],
            ['url' => '/cookie-policy',              'priority' => '0.3', 'changefreq' => 'yearly'],
            ['url' => '/compliance',                 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['url' => '/refund-policy',              'priority' => '0.4', 'changefreq' => 'yearly'],
            ['url' => '/legal-disclaimer',           'priority' => '0.3', 'changefreq' => 'yearly'],
            ['url' => '/security-policy',            'priority' => '0.4', 'changefreq' => 'yearly'],
            ['url' => '/incident-response-policy',   'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        // Published knowledge-base articles
        $articles = Article::published()
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Published blog posts
        $blogPosts = BlogPost::where('published', true)
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = view('sitemap', [
            'baseUrl'     => $baseUrl,
            'staticPages' => $staticPages,
            'articles'    => $articles,
            'blogPosts'   => $blogPosts,
            'now'         => now()->toAtomString(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * Generate Google News sitemap for recent blog posts (last 2 days).
     */
    public function news(): Response
    {
        $baseUrl = config('app.url');

        // Only include blog posts from the last 2 days for Google News
        $recentPosts = BlogPost::where('published', true)
            ->where('created_at', '>=', now()->subDays(2))
            ->select('title', 'slug', 'excerpt', 'author', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $content = view('sitemap-news', [
            'baseUrl'     => $baseUrl,
            'posts'       => $recentPosts,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
