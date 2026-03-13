<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">

{{-- Recent Blog Posts for Google News (Last 2 Days) --}}
@foreach($posts as $post)
    <url>
        <loc>{{ $baseUrl }}/blog/{{ $post->slug }}</loc>
        <news:news>
            <news:publication>
                <news:name>CORPIUS</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>{{ $post->created_at->toAtomString() }}</news:publication_date>
            <news:title>{{ $post->title }}</news:title>
        </news:news>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach

</urlset>
