<template>
    <MarketingLayout>
        <Head :title="article.title + ' | CORPIUS Knowledge Base'">
            <meta head-key="description" name="description" :content="article.excerpt ? article.excerpt.substring(0, 155) : (article.title + ' — Expert guide from CORPIUS Knowledge Base.')" />
            <link head-key="canonical" rel="canonical" :href="'https://corpius.net/knowledge-base/' + article.slug" />
            <meta head-key="og:title" property="og:title" :content="article.title + ' | CORPIUS'" />
            <meta head-key="og:description" property="og:description" :content="article.excerpt ? article.excerpt.substring(0, 155) : article.title" />
            <meta head-key="og:url" property="og:url" :content="'https://corpius.net/knowledge-base/' + article.slug" />
            <meta head-key="og:type" property="og:type" content="article" />
            <meta head-key="twitter:title" name="twitter:title" :content="article.title + ' | CORPIUS'" />
            <meta head-key="twitter:description" name="twitter:description" :content="article.excerpt ? article.excerpt.substring(0, 155) : article.title" />
        </Head>
        
        <!-- Article Header -->
        <article style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Breadcrumb -->
                <nav class="flex mb-8" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-2">
                        <li>
                            <Link :href="route('home')" class="text-gray-300 hover:text-yellow-400">
                                Home
                            </Link>
                        </li>
                        <li>
                            <span class="mx-2 text-gray-400">/</span>
                        </li>
                        <li>
                            <Link :href="route('marketing.knowledge-base.index')" class="text-gray-300 hover:text-yellow-400">
                                Knowledge Base
                            </Link>
                        </li>
                        <li>
                            <span class="mx-2 text-gray-400">/</span>
                        </li>
                        <li class="text-white font-medium">
                            {{ article.title }}
                        </li>
                    </ol>
                </nav>

                <!-- Article Meta -->
                <div class="mb-8">
                    <span class="inline-block px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full mb-4">
                        {{ article.category }}
                    </span>
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        {{ article.title }}
                    </h1>
                    <p class="text-xl text-gray-300 mb-6">
                        {{ article.excerpt }}
                    </p>
                    <div class="flex items-center justify-between pb-6 border-b border-gray-700">
                        <div class="flex items-center space-x-6 text-sm text-gray-400">
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ article.author }}
                            </span>
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ article.read_time }} min read
                            </span>
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ article.views }} views
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Article Content -->
                <div class="prose prose-lg max-w-none mb-12 prose-invert prose-headings:text-white prose-p:text-gray-300 prose-strong:text-white prose-li:text-gray-300 prose-a:text-yellow-400 prose-blockquote:text-gray-300 prose-blockquote:border-yellow-400" v-html="article.content"></div>

                <!-- Article Disclaimer -->
                <div class="mb-12 p-4 bg-blue-900/20 border-l-4 border-blue-400 rounded">
                    <p class="text-sm text-gray-300 italic leading-relaxed">
                        <strong>Disclaimer:</strong> This article is for informational purposes only and does not constitute legal advice. CORPIUS is not a law firm. For legal advice specific to your situation, please consult a licensed attorney.
                    </p>
                </div>

                <!-- Tags -->
                <div v-if="article.tags && article.tags.length > 0" class="mb-12">
                    <h3 class="text-sm font-semibold text-gray-300 mb-3">Tags:</h3>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in article.tags"
                            :key="tag"
                            class="px-3 py-1 text-sm bg-gray-700 text-gray-200 rounded-full"
                        >
                            {{ tag }}
                        </span>
                    </div>
                </div>

                <!-- CTA Box -->
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-xl p-8 text-center border-2 border-yellow-200">
                    <h3 class="text-2xl font-bold text-navy-900 mb-4">
                        Need Professional Assistance?
                    </h3>
                    <p class="text-gray-700 mb-6">
                        Our expert team can help you with {{ article.category.toLowerCase() }} and all your business needs.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <Link
                            :href="route('register')"
                            class="bg-navy-900 hover:bg-navy-800 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                        >
                            Get Started
                        </Link>
                        <Link
                            :href="route('marketing.contact')"
                            class="bg-white hover:bg-gray-50 text-navy-900 px-6 py-3 rounded-lg font-semibold border-2 border-navy-900 transition-colors"
                        >
                            Contact Us
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Related Articles -->
            <section v-if="related_articles && related_articles.length > 0" class="py-16" style="background-color: rgba(11, 30, 51, 0.7);">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold text-white mb-8">Related Articles</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <Link
                            v-for="related in related_articles"
                            :key="related.id"
                            :href="route('marketing.knowledge-base.show', related.slug)"
                            class="group bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 hover:border-yellow-400"
                        >
                            <div class="p-6">
                                <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full mb-3">
                                    {{ related.category }}
                                </span>
                                <h3 class="text-lg font-bold text-navy-900 mb-2 group-hover:text-yellow-600 transition-colors line-clamp-2">
                                    {{ related.title }}
                                </h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                    {{ related.excerpt }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ related.read_time }} min
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        {{ related.views }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div class="text-center mt-12">
                        <Link
                            :href="route('marketing.knowledge-base.index')"
                            class="inline-flex items-center text-yellow-400 hover:text-yellow-300 font-semibold transition-colors"
                        >
                            View All Articles
                            <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </section>
        </article>
    </MarketingLayout>
</template>

<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    article: Object,
    related_articles: Array,
});
</script>

<style>
.prose {
    color: #d1d5db;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4 {
    color: #ffffff;
    font-weight: 700;
}

.prose h2 {
    font-size: 1.875rem;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.prose h3 {
    font-size: 1.5rem;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.prose p {
    margin-bottom: 1.25rem;
    line-height: 1.75;
    color: #d1d5db;
}

.prose ul,
.prose ol {
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
}

.prose ul {
    list-style-type: disc;
}

.prose ol {
    list-style-type: decimal;
}

.prose li {
    margin-bottom: 0.5rem;
    color: #d1d5db;
}

.prose strong {
    font-weight: 600;
    color: #ffffff;
}

.prose a {
    color: #facc15;
    text-decoration: underline;
}

.prose a:hover {
    color: #fde047;
}

.prose blockquote {
    border-left: 4px solid #fbbf24;
    padding-left: 1rem;
    font-style: italic;
    color: #d1d5db;
    margin: 1.5rem 0;
}

.prose table {
    width: 100%;
    margin: 1.5rem 0;
    border-collapse: collapse;
}

.prose th,
.prose td {
    border: 1px solid #4b5563;
    padding: 0.75rem;
    text-align: left;
    color: #d1d5db;
}

.prose th {
    background-color: rgba(255, 255, 255, 0.05);
    font-weight: 600;
    color: #ffffff;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
