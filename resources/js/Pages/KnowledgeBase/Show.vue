<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    article: Object,
    related_articles: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`${article.title} | CORPIUS Knowledge Base`" />

    <AuthenticatedLayout>
        <div class="space-y-7">

            <!-- Breadcrumb + back -->
            <div class="flex items-center gap-2 text-sm">
                <Link href="/knowledge-base" class="flex items-center gap-1.5 text-gray-400 hover:text-amber-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Knowledge Base
                </Link>
                <svg class="w-3.5 h-3.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-500 capitalize">{{ article.category.replace(/-/g, ' ') }}</span>
                <svg class="w-3.5 h-3.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-300 truncate max-w-[260px]">{{ article.title }}</span>
            </div>

            <div class="max-w-4xl">

                <!-- Article Card -->
                <article class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden" style="box-shadow:0 0 40px 0 rgba(244,184,64,.05)">
                    <div class="p-7 lg:p-10">

                        <!-- Meta row -->
                        <div class="flex flex-wrap items-center gap-3 mb-5">
                            <span :class="[
                                'text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider',
                                article.featured
                                    ? 'bg-amber-400/10 text-amber-400 border border-amber-400/20'
                                    : 'bg-white/[0.06] text-gray-400 border border-white/[0.08]'
                            ]">
                                {{ article.category.replace(/-/g, ' ') }}
                            </span>
                            <span class="text-xs text-gray-500">{{ article.read_time }} min read</span>
                            <span class="text-xs text-gray-500">{{ formatDate(article.created_at) }}</span>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl lg:text-4xl font-bold text-white mb-4 leading-tight">
                            {{ article.title }}
                        </h1>

                        <!-- Excerpt -->
                        <p class="text-[15px] text-gray-400 mb-7 leading-relaxed">
                            {{ article.excerpt }}
                        </p>

                        <!-- Author + views -->
                        <div class="flex flex-wrap items-center gap-6 pb-7 mb-7 border-b border-white/[0.06]">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ article.author }}</p>
                                    <p class="text-xs text-gray-500">Legal &amp; Tax Expert</p>
                                </div>
                            </div>
                            <div v-if="article.views > 0" class="flex items-center gap-2 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span class="text-xs">{{ article.views }} views</span>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="prose prose-lg prose-invert max-w-none article-content">
                            <div v-html="article.content"></div>
                        </div>

                        <!-- Tags -->
                        <div v-if="article.tags && article.tags.length > 0" class="mt-10 pt-7 border-t border-white/[0.06]">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-[11px] font-bold text-amber-400 uppercase tracking-widest">Related Topics</span>
                                <div class="flex-1 h-px bg-amber-400/10"/>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in article.tags"
                                    :key="tag"
                                    class="px-3 py-1 rounded-full text-xs font-medium bg-white/[0.05] text-gray-400 border border-white/[0.08] hover:border-amber-400/30 hover:text-amber-400 transition-colors cursor-default"
                                >
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>
                </article>
                <!-- Related Articles -->
                <div v-if="related_articles && related_articles.length > 0" class="mt-7">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="text-[11px] font-bold text-amber-400 uppercase tracking-widest">Related Articles</span>
                        <div class="flex-1 h-px bg-amber-400/10"/>
                    </div>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Link
                            v-for="relatedArticle in related_articles"
                            :key="relatedArticle.id"
                            :href="`/knowledge-base/${relatedArticle.slug}`"
                            class="group rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 hover:border-amber-400/20 transition-all duration-300"
                        >
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider bg-white/[0.06] text-gray-500 border border-white/[0.08] capitalize">
                                {{ relatedArticle.category.replace(/-/g, ' ') }}
                            </span>
                            <h3 class="text-[14px] font-bold text-white mt-3 mb-2 group-hover:text-amber-400 transition-colors leading-snug line-clamp-2">
                                {{ relatedArticle.title }}
                            </h3>
                            <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ relatedArticle.excerpt }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-600">{{ relatedArticle.read_time }} min read</span>
                                <span class="text-xs text-amber-400 font-semibold inline-flex items-center gap-1">
                                    Read more
                                    <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- CTA -->
                <div class="mt-4 rounded-2xl border border-amber-400/10 bg-amber-400/[0.04] p-7 text-center">
                    <div class="w-12 h-12 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Ready to Start Your Business?</h3>
                    <p class="text-sm text-gray-400 mb-5 max-w-xl mx-auto">
                        Let our expert team handle your business formation while you focus on growing your company.
                    </p>
                    <Link
                        href="/orders/create"
                        class="inline-flex items-center gap-2 bg-amber-400 text-[#07101e] text-sm font-bold px-6 py-2.5 rounded-xl hover:bg-amber-300 transition-colors"
                    >
                        Start Your Business Formation
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.article-content :deep(h2) { @apply text-xl font-bold text-white mt-8 mb-3; }
.article-content :deep(h3) { @apply text-lg font-semibold text-white mt-6 mb-2; }
.article-content :deep(p)  { @apply text-gray-300 leading-relaxed mb-4 text-[15px]; }
.article-content :deep(ul),
.article-content :deep(ol) { @apply text-gray-300 mb-4 pl-5; }
.article-content :deep(li) { @apply mb-1.5; }
.article-content :deep(strong) { @apply text-white font-semibold; }
.article-content :deep(table) { @apply w-full border-collapse my-6; }
.article-content :deep(th) { @apply bg-[#0a1628] text-white font-semibold p-3 border border-white/[0.08] text-sm; }
.article-content :deep(td) { @apply text-gray-300 p-3 border border-white/[0.08] text-sm; }
.article-content :deep(a)  { @apply text-amber-400 hover:text-amber-300 transition; }
.article-content :deep(blockquote) { @apply border-l-2 border-amber-400/40 pl-4 text-gray-400 italic my-4; }
.article-content :deep(code) { @apply bg-[#0a1628] text-amber-400 px-1.5 py-0.5 rounded text-sm font-mono; }
.article-content :deep(pre)  { @apply bg-[#0a1628] border border-white/[0.06] rounded-xl p-4 my-4 overflow-x-auto; }
.article-content :deep(pre code) { @apply bg-transparent p-0 text-gray-300; }
.article-content :deep(hr)   { @apply border-white/[0.06] my-6; }
</style>
