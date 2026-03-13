<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    templates: Array,
    stats: Object,
});

const del = (id) => {
    if (!confirm('Delete this template?')) return;
    router.delete(route('admin.email.templates.destroy', id), { preserveScroll: true });
};

const categoryBadge = (c) => ({
    marketing:     'bg-amber-500/15 text-amber-400',
    transactional: 'bg-blue-500/15 text-blue-400',
})[c] ?? 'bg-gray-500/15 text-gray-400';
</script>

<template>
    <Head title="Email Templates" />
    <AdminLayout>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Email Templates</h1>
                <p class="text-sm text-gray-500 mt-1">Reusable HTML email templates</p>
            </div>
            <Link :href="route('admin.email.templates.create')" class="inline-flex items-center gap-2 bg-amber-400 text-[#0b1e33] hover:bg-amber-300 rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Template
            </Link>
        </div>

        <!-- KPI -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="(card, i) in [
                { label: 'Total', value: stats.total, color: 'blue' },
                { label: 'Marketing', value: stats.marketing, color: 'amber' },
                { label: 'Transactional', value: stats.transactional, color: 'green' },
                { label: 'AI-Generated', value: stats.ai_generated, color: 'purple' },
            ]" :key="i" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
               style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div :class="`absolute inset-0 bg-gradient-to-br from-${card.color}-500/5 to-transparent pointer-events-none`"/>
                <div :class="`absolute bottom-0 left-0 right-0 h-[2px] bg-${card.color}-500/50 rounded-b-2xl`"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-2">{{ card.label }}</p>
                <p class="text-2xl font-bold text-white tabular-nums">{{ card.value }}</p>
            </div>
        </div>

        <!-- Grid of templates -->
        <div v-if="templates.length === 0" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-16 text-center text-gray-600">
            No templates yet. <Link :href="route('admin.email.templates.create')" class="text-amber-400 hover:underline">Create one</Link>.
        </div>
        <div v-else class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <div v-for="t in templates" :key="t.id" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3 hover:border-white/[0.12] transition">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-white truncate">{{ t.name }}</p>
                        <p class="text-[12px] text-gray-500 mt-0.5 truncate">{{ t.subject }}</p>
                    </div>
                    <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold shrink-0', categoryBadge(t.category)]">{{ t.category }}</span>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-gray-600 flex-wrap">
                    <span v-if="t.ai_generated" class="inline-flex items-center gap-1 bg-purple-500/10 text-purple-400 rounded-full px-2 py-0.5 text-[10px] font-semibold">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        AI
                    </span>
                    <span>by {{ t.created_by }}</span>
                    <span>· {{ t.updated_at }}</span>
                </div>
                <div class="flex items-center gap-2 mt-auto pt-2 border-t border-white/[0.06]">
                    <a :href="route('admin.email.templates.preview', t.id)" target="_blank"
                        class="flex-1 text-center border border-white/[0.10] bg-white/[0.04] text-gray-400 hover:text-white rounded-lg h-8 text-[12px] font-semibold transition flex items-center justify-center">Preview</a>
                    <Link :href="route('admin.email.templates.edit', t.id)"
                        class="flex-1 text-center border border-amber-400/20 bg-amber-400/5 text-amber-400 hover:bg-amber-400/15 rounded-lg h-8 text-[12px] font-semibold transition flex items-center justify-center">Edit</Link>
                    <button @click="del(t.id)" class="w-8 h-8 rounded-lg border border-white/[0.07] text-gray-600 hover:text-red-400 hover:border-red-500/20 hover:bg-red-500/5 transition flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
