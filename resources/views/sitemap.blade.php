<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

{{-- Static Pages --}}
@foreach($staticPages as $page)
    <url>
        <loc>{{ $baseUrl }}{{ $page['url'] }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
@endforeach

{{-- Published Knowledge Base Articles --}}
@foreach($articles as $article)
    <url>
        <loc>{{ $baseUrl }}/knowledge-base/{{ $article->slug }}</loc>
        <lastmod>{{ $article->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
@endforeach

{{-- Published Blog Posts --}}
@foreach($blogPosts as $post)
    <url>
        <loc>{{ $baseUrl }}/blog/{{ $post->slug }}</loc>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach

</urlset>
