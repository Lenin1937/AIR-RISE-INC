<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    articles: Object, // Paginated data
    categories: Array,
    featured_articles: Array,
    filters: Object,
});

const searchTerm = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || 'all');

const articlesList = computed(() => {
    return props.articles?.data || [];
});

const performSearch = () => {
    router.get('/knowledge-base', {
        search: searchTerm.value,
        category: selectedCategory.value === 'all' ? '' : selectedCategory.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByCategory = (category) => {
    selectedCategory.value = category;
    router.get('/knowledge-base', {
        search: searchTerm.value,
        category: category === 'all' ? '' : category,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const viewArticle = (slug) => {
    router.visit(`/knowledge-base/${slug}`);
};
</script>

<template>
    <Head title="Knowledge Base - Business Formation Guides" />

    <AuthenticatedLayout>
        <div class="space-y-8">

            <!-- ── Header ── -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-8 h-8 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h1 class="text-[22px] font-bold text-white tracking-tight">Knowledge Base</h1>
                    </div>
                    <p class="text-[13px] text-gray-400 ml-11">Expert guides and resources for business formation, compliance, and growth</p>
                </div>
                <div class="text-[12px] text-gray-500 flex-shrink-0 mt-1">
                    {{ articles?.total || 0 }} articles
                </div>
            </div>

            <!-- ── Search + Category Filter ── -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 space-y-4">
                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        v-model="searchTerm"
                        @keyup.enter="performSearch"
                        placeholder="Search articles..."
                        class="w-full h-11 pl-10 pr-4 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"
                    />
                </div>
                <!-- Categories -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="category in categories"
                        :key="category"
                        @click="filterByCategory(category.toLowerCase())"
                        :class="['h-8 px-4 rounded-xl text-[12px] font-semibold transition-all',
                            selectedCategory === category.toLowerCase()
                                ? 'bg-amber-400 text-[#07101e] shadow-lg shadow-amber-400/20'
                                : 'border border-white/[0.08] bg-white/[0.03] text-gray-400 hover:border-amber-400/30 hover:text-white']"
                    >
                        {{ category }}
                    </button>
                </div>
            </div>

            <!-- ── Featured Articles ── -->
            <div v-if="featured_articles && featured_articles.length > 0">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-[11px] font-bold text-amber-400 uppercase tracking-widest">Featured</span>
                    <div class="flex-1 h-px bg-amber-400/10"/>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div
                        v-for="article in featured_articles"
                        :key="article.id"
                        @click="viewArticle(article.slug)"
                        class="group relative rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 hover:border-amber-400/40 hover:shadow-lg hover:shadow-amber-400/5 transition-all duration-200 cursor-pointer overflow-hidden"
                    >
                        <!-- ambient glow -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-400/[0.04] rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"/>
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Featured</span>
                        </div>
                        <h3 class="text-[15px] font-bold text-white mb-2 group-hover:text-amber-300 transition-colors leading-snug">
                            {{ article.title }}
                        </h3>
                        <p class="text-[12px] text-gray-400 mb-4 line-clamp-2 leading-relaxed">{{ article.excerpt }}</p>
                        <div class="flex items-center justify-between pt-3 border-t border-white/[0.05]">
                            <span class="text-[11px] text-gray-500 capitalize">{{ article.category.replace('-', ' ') }}</span>
                            <span class="text-[11px] text-gray-500">{{ article.read_time }} min read</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── All Articles ── -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                        {{ selectedCategory === 'all' ? 'All Articles' : selectedCategory.charAt(0).toUpperCase() + selectedCategory.slice(1).replace('-', ' ') }}
                    </span>
                    <div class="flex-1 h-px bg-white/[0.05]"/>
                </div>

                <!-- Empty state -->
                <div v-if="articlesList.length === 0" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-[17px] font-bold text-white mb-2">No articles found</h3>
                    <p class="text-[13px] text-gray-400">Try adjusting your search or filter criteria</p>
                </div>

                <!-- Articles grid -->
                <div v-else class="grid lg:grid-cols-2 gap-4">
                    <article
                        v-for="article in articlesList"
                        :key="article.id"
                        @click="viewArticle(article.slug)"
                        class="group relative rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 hover:border-amber-400/40 hover:shadow-lg hover:shadow-amber-400/5 transition-all duration-200 cursor-pointer overflow-hidden"
                    >
                        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-400/[0.03] rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"/>
                        <div class="flex items-center gap-2 mb-3">
                            <span :class="['text-[10px] font-bold px-2.5 py-0.5 rounded-full border uppercase tracking-wider',
                                article.featured
                                    ? 'bg-amber-400/10 text-amber-400 border-amber-400/20'
                                    : 'bg-white/[0.04] text-gray-400 border-white/[0.08]']">
                                {{ article.category.replace('-', ' ') }}
                            </span>
                            <span class="text-[11px] text-gray-600">·</span>
                            <span class="text-[11px] text-gray-500">{{ article.read_time }} min read</span>
                        </div>
                        <h3 class="text-[14px] font-bold text-white mb-2 group-hover:text-amber-300 transition-colors leading-snug">
                            {{ article.title }}
                        </h3>
                        <p class="text-[12px] text-gray-400 mb-4 line-clamp-2 leading-relaxed">{{ article.excerpt }}</p>
                        <div class="flex items-center justify-between pt-3 border-t border-white/[0.05]">
                            <span class="text-[11px] text-gray-500">By {{ article.author }}</span>
                            <span class="flex items-center gap-1 text-[11px] font-semibold text-amber-400 group-hover:gap-2 transition-all">
                                Read more
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </div>
                    </article>
                </div>

                <!-- Pagination -->
                <div v-if="articles && articles.last_page > 1" class="flex items-center justify-between mt-6 pt-1">
                    <span class="text-[12px] text-gray-500">Page {{ articles.current_page }} of {{ articles.last_page }}</span>
                    <div class="flex items-center gap-2">
                        <button v-if="articles.prev_page_url"
                            @click="router.visit(articles.prev_page_url)"
                            class="inline-flex items-center gap-1 h-8 px-4 rounded-xl border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-300 hover:border-amber-400/30 hover:text-amber-400 transition">
                            ← Prev
                        </button>
                        <button v-if="articles.next_page_url"
                            @click="router.visit(articles.next_page_url)"
                            class="inline-flex items-center gap-1 h-8 px-4 rounded-xl border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-300 hover:border-amber-400/30 hover:text-amber-400 transition">
                            Next →
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>