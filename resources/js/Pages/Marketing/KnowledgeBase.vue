<template>
    <MarketingLayout>
        <Head title="Knowledge Base | Business Formation Resources | CORPIUS">
            <meta head-key="description" name="description" content="Browse CORPIUS knowledge base for expert guides on LLC formation, corporate law, tax planning, and immigration. Free resources for entrepreneurs." />
            <link head-key="canonical" rel="canonical" href="https://corpius.net/knowledge-base" />
            <meta head-key="og:title" property="og:title" content="Knowledge Base | Business Formation Resources | CORPIUS" />
            <meta head-key="og:description" property="og:description" content="Browse CORPIUS knowledge base for expert guides on LLC formation, corporate law, tax planning, and immigration." />
            <meta head-key="og:url" property="og:url" content="https://corpius.net/knowledge-base" />
            <meta head-key="twitter:title" name="twitter:title" content="Knowledge Base | Business Formation Resources | CORPIUS" />
            <meta head-key="twitter:description" name="twitter:description" content="Expert guides on LLC formation, corporate law, tax planning, and immigration from CORPIUS." />
        </Head>
        
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-navy-900 via-navy-800 to-navy-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Educational Content Disclaimer -->
                <div class="mb-12 max-w-4xl mx-auto">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-yellow-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-yellow-900 mb-2">⚠️ Educational Content Only</h3>
                                <p class="text-yellow-800 text-sm leading-relaxed">
                                    The information provided in our Knowledge Base is for general informational and educational purposes only. It is not legal advice and should not be relied upon as such. <strong>CORPIUS is not a law firm.</strong> For specific legal guidance regarding your business formation or compliance matters, please consult with a licensed attorney in your jurisdiction.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-bold text-yellow-400 mb-6 gradient-text-shimmer">
                        Knowledge Base
                    </h1>
                    <p class="text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
                        {{ __('marketing.kb_description') }}
                    </p>
                    
                    <!-- Search Bar -->
                    <div class="max-w-2xl mx-auto">
                        <form @submit.prevent="performSearch">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    :placeholder="__('marketing.search_articles')"
                                    class="w-full px-6 py-4 pl-12 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                                />
                                <svg class="absolute left-4 top-4 h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Articles -->
        <section v-if="featured_articles && featured_articles.length > 0" class="py-16" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-bold text-white">{{ __('marketing.featured_articles') }}</h2>
                    <svg class="h-8 w-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <Link
                        v-for="article in featured_articles"
                        :key="article.id"
                        :href="route('marketing.knowledge-base.show', article.slug)"
                        class="group bg-gradient-to-br from-yellow-50 to-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border-2 border-yellow-200"
                    >
                        <div class="p-6">
                            <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full mb-3">
                                {{ article.category }}
                            </span>
                            <h3 class="text-xl font-bold text-navy-900 mb-2 group-hover:text-yellow-600 transition-colors line-clamp-2">
                                {{ article.title }}
                            </h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ article.excerpt }}
                            </p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ article.read_time }} min
                                </span>
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ article.views }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Category Filter -->
        <section class="py-12" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap justify-center gap-4">
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="filterByCategory(cat)"
                        :class="[
                            'px-6 py-3 rounded-lg font-medium transition-all duration-200',
                            (filters.category === 'all' && cat === 'All') || filters.category === cat
                                ? 'bg-yellow-400 text-navy-900 shadow-lg'
                                : 'bg-white text-gray-700 hover:bg-yellow-50 shadow'
                        ]"
                    >
                        {{ cat }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Articles Grid -->
        <section class="py-16" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-white mb-8">All Articles</h2>
                
                <div v-if="articles.data && articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link
                        v-for="article in articles.data"
                        :key="article.id"
                        :href="route('marketing.knowledge-base.show', article.slug)"
                        class="group bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 hover:border-yellow-400"
                    >
                        <div class="p-6">
                            <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full mb-3">
                                {{ article.category }}
                            </span>
                            <h3 class="text-lg font-bold text-navy-900 mb-2 group-hover:text-yellow-600 transition-colors line-clamp-2">
                                {{ article.title }}
                            </h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ article.excerpt }}
                            </p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ article.read_time }} min
                                </span>
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ article.views }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-white">No articles found</h3>
                    <p class="mt-2 text-gray-300">Try adjusting your search or filter</p>
                </div>

                <!-- Pagination -->
                <div v-if="articles.data && articles.data.length > 0" class="mt-12 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <Link
                            v-if="articles.prev_page_url"
                            :href="articles.prev_page_url"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Previous
                        </Link>
                        <span class="px-4 py-2 text-sm text-gray-700">
                            Page {{ articles.current_page }} of {{ articles.last_page }}
                        </span>
                        <Link
                            v-if="articles.next_page_url"
                            :href="articles.next_page_url"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Next
                        </Link>
                    </nav>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-gradient-to-r from-yellow-400 to-yellow-500 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-navy-900 mb-4">
                    Ready to Start Your Business?
                </h2>
                <p class="text-lg text-navy-800 mb-8">
                    Get expert help with your business formation and compliance needs.
                </p>
                <div class="flex justify-center space-x-4">
                    <Link
                        :href="route('register')"
                        class="bg-navy-900 hover:bg-navy-800 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl"
                    >
                        Get Started
                    </Link>
                    <Link
                        :href="route('marketing.contact')"
                        class="bg-white hover:bg-gray-50 text-navy-900 px-8 py-4 rounded-lg text-lg font-semibold border-2 border-navy-900 transition-all duration-300 shadow-lg hover:shadow-xl"
                    >
                        Contact Us
                    </Link>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>

<style scoped>
@keyframes gradient-text-shimmer {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.gradient-text-shimmer {
    background: linear-gradient(
        90deg,
        #facc15 0%,
        #ffffff 20%,
        #fde047 40%,
        #facc15 60%,
        #ffffff 80%,
        #facc15 100%
    );
    background-size: 300% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradient-text-shimmer 2s linear infinite;
}
</style>

<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { __ } = useTranslations();

const props = defineProps({
    articles: Object,
    featured_articles: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const categories = ['All', 'Business Formation', 'Tax & Compliance', 'Getting Started', 'Immigration'];

function performSearch() {
    router.get(route('marketing.knowledge-base.index'), {
        search: searchQuery.value,
        category: props.filters?.category || 'all'
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function filterByCategory(category) {
    router.get(route('marketing.knowledge-base.index'), {
        category: category === 'All' ? 'all' : category,
        search: searchQuery.value
    }, {
        preserveState: true,
        preserveScroll: false,
    });
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
