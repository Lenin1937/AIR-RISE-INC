<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ articles: { type: Object, default: () => ({}) } });

const data            = computed(() => props.articles?.data || []);
const publishedCount  = computed(() => data.value.filter(a => a.published).length);
const featuredCount   = computed(() => data.value.filter(a => a.featured).length);
const totalViews      = computed(() => data.value.reduce((s, a) => s + (a.views || 0), 0));

const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';

const catColor = cat => {
    const c = (cat||'').toLowerCase();
    if (c.includes('business') || c.includes('formation')) return { bg: 'bg-blue-400/10', text: 'text-blue-400' };
    if (c.includes('tax') || c.includes('compliance'))     return { bg: 'bg-emerald-400/10', text: 'text-emerald-400' };
    if (c.includes('immigration'))                          return { bg: 'bg-purple-400/10', text: 'text-purple-400' };
    if (c.includes('started'))                              return { bg: 'bg-amber-400/10', text: 'text-amber-400' };
    return { bg: 'bg-gray-400/10', text: 'text-gray-400' };
};

const kpis = [
    { label: 'Total Articles', value: () => (props.articles?.total || 0).toLocaleString(), accent: '#60a5fa', glow: 'rgba(96,165,250,0.15)',  bg: 'from-blue-500/[0.10]',   iconBg: 'bg-blue-500/15',   iconColor: 'text-blue-400',   icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { label: 'Published',      value: () => publishedCount.value.toLocaleString(),          accent: '#34d399', glow: 'rgba(52,211,153,0.15)',  bg: 'from-emerald-500/[0.10]',iconBg: 'bg-emerald-500/15',iconColor: 'text-emerald-400', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Featured',       value: () => featuredCount.value.toLocaleString(),           accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',  bg: 'from-amber-500/[0.12]',  iconBg: 'bg-amber-500/15',  iconColor: 'text-amber-400',  icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
    { label: 'Total Views',    value: () => totalViews.value.toLocaleString(),              accent: '#c084fc', glow: 'rgba(192,132,252,0.15)', bg: 'from-purple-500/[0.10]', iconBg: 'bg-purple-500/15', iconColor: 'text-purple-400', icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
];
</script>

<template>
    <Head title="Articles Management" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Articles Management</h1>
                <p class="text-[13px] text-gray-500 mt-1">Manage your knowledge base articles and content</p>
            </div>
            <Link :href="route('admin.articles.create')"
                class="inline-flex items-center gap-2 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-semibold text-[#0b1e33] hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Article
            </Link>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="kpi in kpis" :key="kpi.label"
                class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3"
                :style="{boxShadow: '0 0 28px 0 ' + kpi.glow}">
                <div :class="['absolute inset-0 bg-gradient-to-br opacity-60 pointer-events-none to-transparent', kpi.bg]"></div>
                <div class="relative">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', kpi.iconBg]">
                        <svg :class="['w-5 h-5', kpi.iconColor]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="kpi.icon"/>
                        </svg>
                    </div>
                </div>
                <div class="relative">
                    <p class="text-[22px] font-extrabold text-white leading-none">{{ kpi.value() }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600 mt-1.5">{{ kpi.label }}</p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" :style="{background: 'linear-gradient(90deg,' + kpi.accent + ',transparent)'}"></div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-white">All Articles
                    <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ props.articles?.total || 0 }}</span>
                </h2>
            </div>

            <!-- Empty -->
            <div v-if="!data.length" class="px-6 py-16 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-[13px] font-semibold text-gray-600">No articles yet</p>
                <p class="text-[11px] text-gray-700 mt-1">Get started by creating your first article.</p>
                <Link :href="route('admin.articles.create')"
                    class="inline-flex items-center gap-1.5 mt-4 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Article
                </Link>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-white/[0.05]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Article</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Category</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Status</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Views</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Date</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="article in data" :key="article.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.025] transition-colors">
                            <!-- Article -->
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-[13px] font-semibold text-gray-200 leading-snug line-clamp-1">{{ article.title }}</p>
                                <p class="text-[11px] text-gray-600 mt-0.5 line-clamp-1">{{ article.excerpt?.substring(0, 70) }}…</p>
                            </td>
                            <!-- Category -->
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center text-[11px] font-semibold rounded-full px-2.5 py-1', catColor(article.category).bg, catColor(article.category).text]">
                                    {{ article.category }}
                                </span>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-if="article.published" class="inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-emerald-400/10 text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Published
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-gray-400/10 text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>Draft
                                    </span>
                                    <span v-if="article.featured" class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-amber-400/10 text-amber-400">
                                        ⭐ Featured
                                    </span>
                                </div>
                            </td>
                            <!-- Views -->
                            <td class="px-6 py-4 text-[13px] text-gray-400 font-medium">{{ (article.views || 0).toLocaleString() }}</td>
                            <!-- Date -->
                            <td class="px-6 py-4 text-[12px] text-gray-600">{{ formatDate(article.created_at) }}</td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    <Link :href="route('admin.articles.edit', article.slug)"
                                        class="text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">Edit</Link>
                                    <Link :href="route('admin.articles.destroy', article.slug)" method="delete" as="button"
                                        class="text-[12px] font-semibold text-gray-600 hover:text-red-400 transition-colors">Delete</Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="articles.links && data.length" class="px-6 py-4 border-t border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-[12px] text-gray-600">
                    Showing <span class="text-gray-400 font-medium">{{ articles.from }}</span> to
                    <span class="text-gray-400 font-medium">{{ articles.to }}</span> of
                    <span class="text-gray-400 font-medium">{{ articles.total }}</span> articles
                </p>
                <div class="flex flex-wrap gap-1">
                    <Link v-for="link in articles.links" :key="link.label" :href="link.url || '#'"
                        :class="['px-3 py-1.5 text-[12px] font-semibold rounded-lg transition-colors', link.active ? 'bg-amber-400 text-[#0b1e33]' : 'bg-white/[0.04] text-gray-500 hover:text-gray-300 hover:bg-white/[0.07]', !link.url ? 'opacity-30 pointer-events-none' : '']"
                        v-html="link.label"/>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
