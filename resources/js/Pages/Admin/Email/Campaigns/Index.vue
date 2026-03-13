<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    campaigns: Object,
    stats: Object,
});

const statusColor = (s) => ({
    sent:      'bg-green-500/15 text-green-400',
    draft:     'bg-gray-500/15 text-gray-400',
    scheduled: 'bg-blue-500/15 text-blue-400',
    sending:   'bg-amber-500/15 text-amber-400',
    cancelled: 'bg-red-500/15 text-red-400',
})[s] ?? 'bg-gray-500/15 text-gray-400';

const sendCampaign = (id) => {
    if (!confirm('Send this campaign now?')) return;
    router.post(route('admin.email.campaigns.send', id), {}, { preserveScroll: true });
};
const del = (id) => {
    if (!confirm('Delete this campaign?')) return;
    router.delete(route('admin.email.campaigns.destroy', id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Email Campaigns" />
    <AdminLayout>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Campaigns</h1>
                <p class="text-sm text-gray-500 mt-1">Send and manage email campaigns</p>
            </div>
            <Link :href="route('admin.email.campaigns.create')" class="inline-flex items-center gap-2 bg-amber-400 text-[#0b1e33] hover:bg-amber-300 rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Campaign
            </Link>
        </div>

        <!-- KPI -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="(card, i) in [
                { label: 'Total', value: stats.total, color: 'blue' },
                { label: 'Sent', value: stats.sent, color: 'green' },
                { label: 'Scheduled', value: stats.scheduled, color: 'amber' },
                { label: 'Emails Sent', value: stats.total_sent.toLocaleString(), color: 'purple' },
            ]" :key="i" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
               style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div :class="`absolute inset-0 bg-gradient-to-br from-${card.color}-500/5 to-transparent pointer-events-none`"/>
                <div :class="`absolute bottom-0 left-0 right-0 h-[2px] bg-${card.color}-500/50 rounded-b-2xl`"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-2">{{ card.label }}</p>
                <p class="text-2xl font-bold text-white tabular-nums">{{ card.value }}</p>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600">Campaign</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden md:table-cell">Template</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden sm:table-cell">Sent</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden lg:table-cell">Open Rate</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600">Status</th>
                        <th class="px-5 py-3 text-right text-[10px] uppercase tracking-widest text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="campaigns.data.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-gray-600 text-sm">No campaigns yet.</td>
                    </tr>
                    <tr v-for="c in campaigns.data" :key="c.id" class="border-t border-white/[0.04] hover:bg-white/[0.03] transition">
                        <td class="px-5 py-3.5">
                            <p class="text-[13px] font-semibold text-white">{{ c.name }}</p>
                            <p class="text-[11px] text-gray-500">{{ c.segment }}</p>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell text-[12px] text-gray-400">{{ c.template }}</td>
                        <td class="px-5 py-3.5 hidden sm:table-cell text-[12px] text-gray-400">{{ c.total_sent.toLocaleString() }}</td>
                        <td class="px-5 py-3.5 hidden lg:table-cell text-[12px] text-gray-400">{{ c.open_rate }}%</td>
                        <td class="px-5 py-3.5">
                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold', statusColor(c.status)]">{{ c.status }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('admin.email.campaigns.show', c.id)" class="text-[11px] text-gray-500 hover:text-white transition font-medium">Stats</Link>
                                <button v-if="['draft','scheduled'].includes(c.status)" @click="sendCampaign(c.id)"
                                    class="text-[11px] text-amber-400 hover:text-amber-300 transition font-semibold">Send</button>
                                <button @click="del(c.id)" class="text-[11px] text-gray-600 hover:text-red-400 transition font-medium">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination -->
            <div v-if="campaigns.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-white/[0.06]">
                <p class="text-[12px] text-gray-600">{{ campaigns.from }}–{{ campaigns.to }} of {{ campaigns.total }}</p>
                <div class="flex gap-1">
                    <Link v-if="campaigns.prev_page_url" :href="campaigns.prev_page_url" class="px-3 h-7 rounded-lg border border-white/[0.07] text-[12px] text-gray-400 hover:text-white hover:bg-white/[0.06] transition flex items-center">Prev</Link>
                    <Link v-if="campaigns.next_page_url" :href="campaigns.next_page_url" class="px-3 h-7 rounded-lg border border-white/[0.07] text-[12px] text-gray-400 hover:text-white hover:bg-white/[0.06] transition flex items-center">Next</Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
