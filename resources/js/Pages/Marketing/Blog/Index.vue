<template>
    <MarketingLayout>
        <Head title="Blog | Business Insights & Updates | CORPIUS">
            <meta head-key="description" name="description" content="Read the latest business insights, LLC formation tips, corporate law updates, and immigration news from the CORPIUS team." />
            <link head-key="canonical" rel="canonical" href="https://corpius.net/blog" />
            <meta head-key="og:title" property="og:title" content="Blog | Business Insights & Updates | CORPIUS" />
            <meta head-key="og:description" property="og:description" content="Latest business tips, LLC formation guides, and corporate law updates from CORPIUS." />
            <meta head-key="og:url" property="og:url" content="https://corpius.net/blog" />
            <meta head-key="og:image" property="og:image" content="https://corpius.net/blog-post-previwe.jpg" />
        </Head>

        <!-- Hero Section -->
        <section class="py-20" style="background: linear-gradient(135deg, #0b1e33 0%, #0d2440 50%, #0b1e33 100%);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-block px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-yellow-400 bg-yellow-400/10 rounded-full mb-6">CORPIUS Blog</span>
                <h1 class="text-4xl md:text-5xl font-bold text-yellow-400 mb-6 gradient-text-shimmer">Business Insights &amp; Updates</h1>
                <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto">Expert tips on LLC formation, corporate law, tax planning, and immigration — straight from the CORPIUS team.</p>

                <!-- Search -->
                <form @submit.prevent="performSearch" class="max-w-xl mx-auto">
                    <div class="relative">
                        <input v-model="searchQuery" type="text" placeholder="Search posts…"
                            class="w-full px-5 py-3.5 pl-12 rounded-xl text-gray-900 bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400 text-base shadow-lg"/>
                        <svg class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button type="submit" class="absolute right-2 top-2 px-4 h-9 rounded-lg bg-yellow-400 hover:bg-yellow-500 text-sm font-bold text-[#0b1e33] transition">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Featured Posts -->
        <section v-if="featured && featured.length" class="py-16" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 mb-8">
                    <svg class="h-6 w-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <h2 class="text-2xl font-bold text-white">Featured Posts</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link v-for="post in featured" :key="post.id"
                        :href="route('blog.show', post.slug)"
                        class="group rounded-2xl overflow-hidden border border-white/[0.07] bg-[#0c1c30] hover:border-yellow-400/30 hover:shadow-xl hover:shadow-yellow-400/5 transition-all duration-300">
                        <!-- Image -->
                        <div class="relative h-48 overflow-hidden bg-[#0a1a2e]">
                            <img v-if="post.image_url" :src="post.image_url" :alt="post.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="absolute top-3 left-3 px-2.5 py-1 text-xs font-bold bg-yellow-400 text-[#0b1e33] rounded-full">⭐ Featured</span>
                        </div>
                        <div class="p-5">
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold text-yellow-400 bg-yellow-400/10 rounded-full mb-3">{{ post.category }}</span>
                            <h3 class="text-base font-bold text-white mb-2 line-clamp-2 group-hover:text-yellow-300 transition-colors">{{ post.title }}</h3>
                            <p class="text-sm text-gray-400 line-clamp-2 mb-4">{{ post.excerpt }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ post.read_time }} min read
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ post.views }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Category Filter -->
        <section class="pt-6 pb-2" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-3">
                    <button @click="filterByCategory('all')"
                        :class="filters.category === 'all' ? 'bg-yellow-400 text-[#0b1e33] shadow-lg' : 'bg-white/[0.06] text-gray-300 hover:bg-white/[0.10]'"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">All</button>
                    <button v-for="cat in categories" :key="cat" @click="filterByCategory(cat)"
                        :class="filters.category === cat ? 'bg-yellow-400 text-[#0b1e33] shadow-lg' : 'bg-white/[0.06] text-gray-300 hover:bg-white/[0.10]'"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200">{{ cat }}</button>
                </div>
            </div>
        </section>

        <!-- All Posts Grid -->
        <section class="py-14" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-white mb-8">
                    {{ filters.search ? `Results for "${filters.search}"` : filters.category !== 'all' ? filters.category : 'All Posts' }}
                </h2>

                <!-- Posts grid -->
                <div v-if="posts.data && posts.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="post in posts.data" :key="post.id"
                        :href="route('blog.show', post.slug)"
                        class="group rounded-2xl overflow-hidden border border-white/[0.07] bg-[#0c1c30] hover:border-yellow-400/30 hover:shadow-xl hover:shadow-yellow-400/5 transition-all duration-300 flex flex-col">
                        <!-- Image -->
                        <div class="relative h-44 overflow-hidden bg-[#0a1a2e] flex-shrink-0">
                            <img v-if="post.image_url" :src="post.image_url" :alt="post.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-1">
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold text-yellow-400 bg-yellow-400/10 rounded-full mb-3 self-start">{{ post.category }}</span>
                            <h3 class="text-base font-bold text-white mb-2 line-clamp-2 group-hover:text-yellow-300 transition-colors flex-1">{{ post.title }}</h3>
                            <p class="text-sm text-gray-400 line-clamp-2 mb-4">{{ post.excerpt }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ post.author }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ post.read_time }} min
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-20">
                    <svg class="mx-auto h-14 w-14 text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-400 mb-2">No posts found</h3>
                    <p class="text-gray-500 mb-6">Try a different search or category.</p>
                    <button @click="filterByCategory('all')" class="px-5 py-2.5 rounded-lg bg-yellow-400 text-[#0b1e33] text-sm font-bold hover:bg-yellow-500 transition">
                        View All Posts
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="posts.data && posts.data.length && posts.last_page > 1" class="mt-12 flex justify-center items-center gap-3">
                    <Link v-if="posts.prev_page_url" :href="posts.prev_page_url"
                        class="px-5 py-2.5 rounded-xl border border-white/[0.10] bg-white/[0.04] text-sm font-semibold text-gray-300 hover:text-white hover:bg-white/[0.08] transition">
                        ← Previous
                    </Link>
                    <span class="px-4 py-2.5 text-sm text-gray-500">Page {{ posts.current_page }} of {{ posts.last_page }}</span>
                    <Link v-if="posts.next_page_url" :href="posts.next_page_url"
                        class="px-5 py-2.5 rounded-xl border border-white/[0.10] bg-white/[0.04] text-sm font-semibold text-gray-300 hover:text-white hover:bg-white/[0.08] transition">
                        Next →
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-gradient-to-r from-yellow-400 to-yellow-500 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-[#0b1e33] mb-4">Ready to Start Your Business?</h2>
                <p class="text-lg text-[#0b2040] mb-8">Get expert help with your business formation and compliance.</p>
                <div class="flex justify-center flex-wrap gap-4">
                    <Link :href="route('register')" class="bg-[#0b1e33] hover:bg-[#0d2545] text-white px-8 py-4 rounded-xl text-base font-bold transition-all shadow-lg">Get Started</Link>
                    <Link :href="route('marketing.contact')" class="bg-white hover:bg-gray-50 text-[#0b1e33] px-8 py-4 rounded-xl text-base font-bold border-2 border-[#0b1e33] transition-all shadow-lg">Contact Us</Link>
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
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    posts:      { type: Object, default: () => ({}) },
    featured:   { type: Array,  default: () => [] },
    categories: { type: Array,  default: () => [] },
    filters:    { type: Object, default: () => ({ search: '', category: 'all' }) },
});

const searchQuery = ref(props.filters?.search || '');

function performSearch() {
    router.get(route('blog.index'), {
        search:   searchQuery.value,
        category: props.filters?.category || 'all',
    }, { preserveState: true, preserveScroll: true });
}

function filterByCategory(category) {
    router.get(route('blog.index'), {
        category: category === 'All' ? 'all' : category,
        search:   searchQuery.value,
    }, { preserveState: true, preserveScroll: false });
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
