{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>CORPIUS Blog - Business Formation &amp; Entrepreneurship</title>
        <link>{{ $baseUrl }}/blog</link>
        <description>Expert guides on LLC formation, business structure, tax planning, and entrepreneurship from CORPIUS.</description>
        <language>en-us</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ $baseUrl }}/blog/feed" rel="self" type="application/rss+xml"/>
        
        @foreach($posts as $post)
        <item>
            <title><![CDATA[{{ $post->title }}]]></title>
            <link>{{ $baseUrl }}/blog/{{ $post->slug }}</link>
            <guid isPermaLink="true">{{ $baseUrl }}/blog/{{ $post->slug }}</guid>
            <description><![CDATA[{{ $post->excerpt }}]]></description>
            <content:encoded><![CDATA[{!! $post->content !!}]]></content:encoded>
            <pubDate>{{ $post->created_at->toRssString() }}</pubDate>
            <author>{{ $post->author }}</author>
            <category>{{ $post->category }}</category>
            @if($post->image_url)
            <enclosure url="{{ $post->image_url }}" type="image/jpeg"/>
            @endif
        </item>
        @endforeach
    </channel>
</rss>
