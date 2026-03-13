<template>
    <MarketingLayout>
        <Head :title="`${post.meta_title || post.title} | CORPIUS Blog`">
            <meta head-key="description" name="description" :content="post.meta_description || post.excerpt" />
            <link head-key="canonical" rel="canonical" :href="`https://corpius.net/blog/${post.slug}`" />
            <meta head-key="og:title" property="og:title" :content="post.meta_title || post.title" />
            <meta head-key="og:description" property="og:description" :content="post.meta_description || post.excerpt" />
            <meta head-key="og:image" property="og:image" :content="post.image_url || 'https://corpius.net/blog-post-previwe.jpg'" />
            <meta head-key="og:url" property="og:url" :content="`https://corpius.net/blog/${post.slug}`" />
            
            <!-- Article Structured Data for Google News & SEO -->
            <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "NewsArticle",
                    "headline": "{{ post.title }}",
                    "description": "{{ post.excerpt || post.meta_description }}",
                    "image": "{{ post.image_url || 'https://corpius.net/images/default-blog.jpg' }}",
                    "datePublished": "{{ post.created_at }}",
                    "dateModified": "{{ post.updated_at }}",
                    "author": {
                        "@type": "Person",
                        "name": "{{ post.author }}"
                    },
                    "publisher": {
                        "@type": "Organization",
                        "name": "CORPIUS",
                        "logo": {
                            "@type": "ImageObject",
                            "url": "https://corpius.net/images/logo.png"
                        }
                    },
                    "mainEntityOfPage": {
                        "@type": "WebPage",
                        "@id": "https://corpius.net/blog/{{ post.slug }}"
                    },
                    "articleSection": "{{ post.category }}",
                    "keywords": "{{ (post.tags || []).join(', ') }}"
                }
            </script>
        </Head>

        <!-- Hero / Featured Image -->
        <div class="relative" style="background-color: #0b1e33;">
            <div v-if="post.image_url" class="relative h-72 md:h-96 overflow-hidden">
                <img :src="post.image_url" :alt="post.title" class="w-full h-full object-cover"/>
                <div class="absolute inset-0 bg-gradient-to-t from-[#0b1e33] via-[#0b1e33]/40 to-transparent"></div>
            </div>
            <div v-else class="h-24"></div>

            <!-- Post header overlaid on image (or just below) -->
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8" :class="post.image_url ? '-mt-24 relative pb-8' : 'pt-12 pb-8'">
                <!-- Back / breadcrumb -->
                <div class="mb-6">
                    <Link :href="route('blog.index')" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-yellow-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Blog
                    </Link>
                </div>

                <span class="inline-block px-3 py-1 text-xs font-bold text-yellow-400 bg-yellow-400/10 rounded-full mb-4">{{ post.category }}</span>
                <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight mb-5">{{ post.title }}</h1>

                <!-- Meta row -->
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400 pb-6 border-b border-white/[0.08]">
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ post.author }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ formatDate(post.created_at) }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ post.read_time }} min read
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ post.views }} views
                    </span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <section style="background-color: #0b1e33;" class="pb-16">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Tags -->
                <div v-if="post.tags && post.tags.length" class="flex flex-wrap gap-2 mb-8">
                    <span v-for="tag in post.tags" :key="tag"
                        class="px-3 py-1 text-xs font-semibold text-gray-400 bg-white/[0.05] border border-white/[0.07] rounded-full">
                        #{{ tag }}
                    </span>
                </div>

                <!-- Article body -->
                <div class="prose-blog" v-html="post.content"></div>

                <!-- Share this post -->
                <div class="mt-10 pt-8 border-t border-white/[0.08]">
                    <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Share this post</p>
                    <div class="flex flex-wrap gap-3">
                        <a :href="`https://twitter.com/intent/tweet?url=https://corpius.net/blog/${post.slug}&text=${encodeURIComponent(post.title)}`"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.06] hover:bg-white/[0.10] text-white text-sm font-medium transition-colors"
                           :aria-label="`Share ${post.title} on X (Twitter)`">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622Zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            Share on X
                        </a>
                        <a :href="`https://www.facebook.com/sharer/sharer.php?u=https://corpius.net/blog/${post.slug}`"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.06] hover:bg-white/[0.10] text-white text-sm font-medium transition-colors"
                           :aria-label="`Share ${post.title} on Facebook`">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Share on Facebook
                        </a>
                        <a :href="`https://www.linkedin.com/shareArticle?mini=true&url=https://corpius.net/blog/${post.slug}&title=${encodeURIComponent(post.title)}`"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.06] hover:bg-white/[0.10] text-white text-sm font-medium transition-colors"
                           :aria-label="`Share ${post.title} on LinkedIn`">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            Share on LinkedIn
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Posts -->
        <section v-if="related && related.length" class="py-16 border-t border-white/[0.05]" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-white mb-8">Related Posts</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link v-for="rel in related" :key="rel.id"
                        :href="route('blog.show', rel.slug)"
                        class="group rounded-2xl overflow-hidden border border-white/[0.07] bg-[#0c1c30] hover:border-yellow-400/30 transition-all duration-300">
                        <div class="h-40 overflow-hidden bg-[#0a1a2e]">
                            <img v-if="rel.image_url" :src="rel.image_url" :alt="rel.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="p-4">
                            <span class="text-xs font-semibold text-yellow-400">{{ rel.category }}</span>
                            <h3 class="text-sm font-bold text-white mt-1 line-clamp-2 group-hover:text-yellow-300 transition-colors">{{ rel.title }}</h3>
                            <p class="text-xs text-gray-500 mt-2">{{ rel.read_time }} min read</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-gradient-to-r from-yellow-400 to-yellow-500 py-14">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl font-bold text-[#0b1e33] mb-3">Ready to Form Your Business?</h2>
                <p class="text-[#0b2040] mb-6">Our experts are here to guide you through every step.</p>
                <div class="flex justify-center flex-wrap gap-4">
                    <Link :href="route('register')" class="bg-[#0b1e33] hover:bg-[#0d2545] text-white px-7 py-3 rounded-xl text-sm font-bold transition-all shadow-lg">Get Started</Link>
                    <Link :href="route('blog.index')" class="bg-white hover:bg-gray-50 text-[#0b1e33] px-7 py-3 rounded-xl text-sm font-bold border-2 border-[#0b1e33] transition-all">More Posts</Link>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>

<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    post:    { type: Object, required: true },
    related: { type: Array,  default: () => [] },
});

const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '';
</script>

<style>
/* Rich text content styles */
.prose-blog {
    color: #d1d5db;
    font-size: 1rem;
    line-height: 1.8;
}
.prose-blog h1, .prose-blog h2, .prose-blog h3, .prose-blog h4 {
    color: #ffffff;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
    line-height: 1.3;
}
.prose-blog h1 { font-size: 1.875rem; }
.prose-blog h2 { font-size: 1.5rem; }
.prose-blog h3 { font-size: 1.25rem; }
.prose-blog h4 { font-size: 1.125rem; }
.prose-blog p   { margin-bottom: 1.25rem; }
.prose-blog ul, .prose-blog ol { padding-left: 1.5rem; margin-bottom: 1.25rem; }
.prose-blog li  { margin-bottom: 0.5rem; }
.prose-blog ul  { list-style-type: disc; }
.prose-blog ol  { list-style-type: decimal; }
.prose-blog a   { color: #facc15; text-decoration: underline; }
.prose-blog a:hover { color: #fde047; }
.prose-blog blockquote {
    border-left: 4px solid #facc15;
    padding-left: 1rem;
    margin: 1.5rem 0;
    color: #9ca3af;
    font-style: italic;
}
.prose-blog img {
    max-width: 100%;
    border-radius: 0.75rem;
    margin: 1.5rem 0;
}
.prose-blog code {
    background: rgba(255,255,255,0.06);
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    color: #fcd34d;
}
.prose-blog pre {
    background: #0a1628;
    padding: 1.25rem;
    border-radius: 0.75rem;
    overflow-x: auto;
    margin-bottom: 1.25rem;
}
.prose-blog pre code {
    background: none;
    padding: 0;
    color: #d1fae5;
}
.prose-blog strong { color: #f9fafb; font-weight: 700; }
.prose-blog hr { border-color: rgba(255,255,255,0.08); margin: 2rem 0; }

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
