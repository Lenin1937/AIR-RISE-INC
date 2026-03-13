<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ campaign: Object });

const statusColor = (s) => ({
    sent:      'bg-green-500/15 text-green-400',
    draft:     'bg-gray-500/15 text-gray-400',
    scheduled: 'bg-blue-500/15 text-blue-400',
    sending:   'bg-amber-500/15 text-amber-400',
    cancelled: 'bg-red-500/15 text-red-400',
})[s] ?? 'bg-gray-500/15 text-gray-400';
</script>

<template>
    <Head :title="campaign.name" />
    <AdminLayout>
        <div class="flex items-center gap-3 mb-8">
            <Link :href="route('admin.email.campaigns.index')" class="w-8 h-8 rounded-lg border border-white/[0.08] bg-white/[0.04] text-gray-500 hover:text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </Link>
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-white truncate">{{ campaign.name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Campaign analytics</p>
            </div>
            <span :class="['inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold', statusColor(campaign.status)]">{{ campaign.status }}</span>
        </div>

        <!-- Stats cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="(card, i) in [
                { label: 'Sent', value: campaign.total_sent.toLocaleString(), color: 'blue' },
                { label: 'Delivered', value: campaign.total_delivered.toLocaleString(), color: 'green' },
                { label: 'Open Rate', value: campaign.open_rate + '%', color: 'amber' },
                { label: 'Click Rate', value: campaign.click_rate + '%', color: 'purple' },
            ]" :key="i" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
               style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div :class="`absolute inset-0 bg-gradient-to-br from-${card.color}-500/5 to-transparent pointer-events-none`"/>
                <div :class="`absolute bottom-0 left-0 right-0 h-[2px] bg-${card.color}-500/50 rounded-b-2xl`"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-2">{{ card.label }}</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ card.value }}</p>
            </div>
        </div>

        <!-- Details -->
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 space-y-4">
                <h2 class="text-[13px] font-bold text-white">Campaign Details</h2>
                <dl class="space-y-3">
                    <div v-for="row in [
                        { label: 'From', value: `${campaign.from_name} <${campaign.from_email}>` },
                        { label: 'Reply-To', value: campaign.reply_to || '—' },
                        { label: 'Template', value: campaign.template || '—' },
                        { label: 'Segment', value: campaign.segment },
                        { label: 'Scheduled', value: campaign.scheduled_at || '—' },
                        { label: 'Sent At', value: campaign.sent_at || '—' },
                        { label: 'Created', value: campaign.created_at },
                    ]" :key="row.label" class="flex gap-4">
                        <dt class="text-[12px] text-gray-600 w-24 shrink-0">{{ row.label }}</dt>
                        <dd class="text-[13px] text-white">{{ row.value }}</dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 space-y-4">
                <h2 class="text-[13px] font-bold text-white">Delivery Breakdown</h2>
                <div class="space-y-3">
                    <div v-for="row in [
                        { label: 'Total Sent', value: campaign.total_sent, max: campaign.total_sent, color: 'blue' },
                        { label: 'Delivered', value: campaign.total_delivered, max: campaign.total_sent, color: 'green' },
                        { label: 'Opened', value: campaign.total_opened, max: campaign.total_sent, color: 'amber' },
                        { label: 'Clicked', value: campaign.total_clicked, max: campaign.total_sent, color: 'purple' },
                        { label: 'Bounced', value: campaign.total_bounced, max: campaign.total_sent, color: 'red' },
                    ]" :key="row.label" class="flex items-center gap-3">
                        <p class="text-[12px] text-gray-500 w-20 shrink-0">{{ row.label }}</p>
                        <div class="flex-1 h-1.5 bg-white/[0.04] rounded-full overflow-hidden">
                            <div :class="`h-full bg-${row.color}-500/60 rounded-full transition-all duration-700`"
                                :style="{ width: row.max > 0 ? Math.round((row.value / row.max) * 100) + '%' : '0%' }"/>
                        </div>
                        <p class="text-[12px] text-white font-semibold tabular-nums w-10 text-right">{{ row.value.toLocaleString() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
