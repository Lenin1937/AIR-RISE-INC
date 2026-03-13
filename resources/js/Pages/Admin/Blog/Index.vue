<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    posts:      { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] },
});

const data           = computed(() => props.posts?.data || []);
const publishedCount = computed(() => data.value.filter(p => p.published).length);
const featuredCount  = computed(() => data.value.filter(p => p.featured).length);
const totalViews     = computed(() => data.value.reduce((s, p) => s + (p.views || 0), 0));

const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';

const catColor = cat => {
    const c = (cat || '').toLowerCase();
    if (c.includes('business') || c.includes('formation')) return { bg: 'bg-blue-400/10',    text: 'text-blue-400' };
    if (c.includes('tax') || c.includes('compliance'))     return { bg: 'bg-emerald-400/10', text: 'text-emerald-400' };
    if (c.includes('immigration'))                          return { bg: 'bg-purple-400/10',  text: 'text-purple-400' };
    if (c.includes('news') || c.includes('update'))        return { bg: 'bg-cyan-400/10',    text: 'text-cyan-400' };
    return { bg: 'bg-amber-400/10', text: 'text-amber-400' };
};

const kpis = [
    { label: 'Total Posts',  value: () => (props.posts?.total || 0).toLocaleString(), accent: '#60a5fa', glow: 'rgba(96,165,250,0.15)',  bg: 'from-blue-500/[0.10]',    iconBg: 'bg-blue-500/15',    iconColor: 'text-blue-400',    icon: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' },
    { label: 'Published',    value: () => publishedCount.value.toLocaleString(),      accent: '#34d399', glow: 'rgba(52,211,153,0.15)',  bg: 'from-emerald-500/[0.10]', iconBg: 'bg-emerald-500/15', iconColor: 'text-emerald-400', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Featured',     value: () => featuredCount.value.toLocaleString(),       accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',  bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
    { label: 'Total Views',  value: () => totalViews.value.toLocaleString(),          accent: '#c084fc', glow: 'rgba(192,132,252,0.15)', bg: 'from-purple-500/[0.10]',  iconBg: 'bg-purple-500/15',  iconColor: 'text-purple-400',  icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
];

function deletePost(post) {
    if (confirm(`Delete "${post.title}"?`)) {
        router.delete(route('admin.blog.destroy', post.slug), { preserveScroll: true });
    }
}

function togglePublish(post) {
    router.patch(route('admin.blog.toggle-publish', post.slug), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Blog Management" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Blog Management</h1>
                <p class="text-[13px] text-gray-500 mt-1">Create and manage blog posts with images</p>
            </div>
            <div class="flex items-center gap-3 self-start sm:self-auto">
                <a :href="route('blog.index')" target="_blank"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Blog
                </a>
                <Link :href="route('admin.blog.create')"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-semibold text-[#0b1e33] hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    New Post
                </Link>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="kpi in kpis" :key="kpi.label"
                class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3"
                :style="{ boxShadow: '0 0 28px 0 ' + kpi.glow }">
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
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" :style="{ background: 'linear-gradient(90deg,' + kpi.accent + ',transparent)' }"></div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-white">All Posts
                    <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ props.posts?.total || 0 }}</span>
                </h2>
            </div>

            <!-- Empty -->
            <div v-if="!data.length" class="px-6 py-16 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <p class="text-[13px] font-semibold text-gray-600">No blog posts yet</p>
                <p class="text-[11px] text-gray-700 mt-1">Create your first blog post with an image.</p>
                <Link :href="route('admin.blog.create')"
                    class="inline-flex items-center gap-1.5 mt-4 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    New Post
                </Link>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-white/[0.05]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Post</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Category</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Status</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Views</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Date</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="post in data" :key="post.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.025] transition-colors">
                            <!-- Post -->
                            <td class="px-6 py-4 max-w-xs">
                                <div class="flex items-center gap-3">
                                    <!-- Thumbnail -->
                                    <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-white/[0.04] border border-white/[0.06]">
                                        <img v-if="post.image_url" :src="post.image_url" :alt="post.title" class="w-full h-full object-cover"/>
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-semibold text-gray-200 leading-snug line-clamp-1">{{ post.title }}</p>
                                        <p class="text-[11px] text-gray-600 mt-0.5 line-clamp-1">{{ post.excerpt?.substring(0, 60) }}…</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Category -->
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center text-[11px] font-semibold rounded-full px-2.5 py-1', catColor(post.category).bg, catColor(post.category).text]">
                                    {{ post.category }}
                                </span>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <button @click="togglePublish(post)"
                                        :class="post.published
                                            ? 'inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-emerald-400/10 text-emerald-400 hover:bg-emerald-400/20 transition'
                                            : 'inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-gray-400/10 text-gray-500 hover:bg-gray-400/20 transition'">
                                        <span :class="['w-1.5 h-1.5 rounded-full', post.published ? 'bg-emerald-400' : 'bg-gray-500']"></span>
                                        {{ post.published ? 'Published' : 'Draft' }}
                                    </button>
                                    <span v-if="post.featured" class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-amber-400/10 text-amber-400">
                                        ⭐ Featured
                                    </span>
                                </div>
                            </td>
                            <!-- Views -->
                            <td class="px-6 py-4 text-[13px] text-gray-400 font-medium">{{ (post.views || 0).toLocaleString() }}</td>
                            <!-- Date -->
                            <td class="px-6 py-4 text-[12px] text-gray-600">{{ formatDate(post.created_at) }}</td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    <Link :href="route('admin.blog.edit', post.slug)"
                                        class="text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">Edit</Link>
                                    <button @click="deletePost(post)"
                                        class="text-[12px] font-semibold text-gray-600 hover:text-red-400 transition-colors">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="posts.last_page > 1" class="px-6 py-4 border-t border-white/[0.05] flex items-center justify-between">
                    <p class="text-[12px] text-gray-600">Page {{ posts.current_page }} of {{ posts.last_page }}</p>
                    <div class="flex gap-2">
                        <Link v-if="posts.prev_page_url" :href="posts.prev_page_url"
                            class="px-3 h-8 rounded-lg border border-white/[0.08] bg-white/[0.03] text-[12px] font-medium text-gray-300 hover:bg-white/[0.07] transition flex items-center">
                            ← Prev
                        </Link>
                        <Link v-if="posts.next_page_url" :href="posts.next_page_url"
                            class="px-3 h-8 rounded-lg border border-white/[0.08] bg-white/[0.03] text-[12px] font-medium text-gray-300 hover:bg-white/[0.07] transition flex items-center">
                            Next →
                        </Link>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
