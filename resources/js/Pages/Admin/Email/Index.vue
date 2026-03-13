<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentCampaigns: Array,
    recentContacts: Array,
});

const statusColor = (s) => ({
    sent:      'bg-green-500/15 text-green-400',
    draft:     'bg-gray-500/15 text-gray-400',
    scheduled: 'bg-blue-500/15 text-blue-400',
    sending:   'bg-amber-500/15 text-amber-400',
    cancelled: 'bg-red-500/15 text-red-400',
})[s] ?? 'bg-gray-500/15 text-gray-400';
</script>

<template>
    <Head title="Email Marketing" />
    <AdminLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Email Marketing</h1>
                <p class="text-sm text-gray-500 mt-1">Campaigns, contacts, templates and automations</p>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.email.campaigns.create')"
                    class="inline-flex items-center gap-2 bg-amber-400 text-[#0b1e33] hover:bg-amber-300 rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Campaign
                </Link>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Contacts -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
                 style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent pointer-events-none"/>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-blue-500/50 rounded-b-2xl"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-3">Total Contacts</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ stats.total_contacts.toLocaleString() }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ stats.subscribed.toLocaleString() }} subscribed</p>
            </div>
            <!-- Campaigns sent -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
                 style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent pointer-events-none"/>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-amber-500/50 rounded-b-2xl"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-3">Campaigns Sent</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ stats.sent_campaigns }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ stats.total_campaigns }} total</p>
            </div>
            <!-- Open Rate -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
                 style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent pointer-events-none"/>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-green-500/50 rounded-b-2xl"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-3">Avg Open Rate</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ stats.avg_open_rate }}%</p>
                <p class="text-xs text-gray-500 mt-1">across sent campaigns</p>
            </div>
            <!-- Click Rate -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
                 style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent pointer-events-none"/>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-purple-500/50 rounded-b-2xl"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-3">Avg Click Rate</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ stats.avg_click_rate }}%</p>
                <p class="text-xs text-gray-500 mt-1">across sent campaigns</p>
            </div>
        </div>

        <!-- Quick links row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <Link :href="route('admin.email.contacts.index')" class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 hover:bg-[#0e2236] transition group">
                <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center group-hover:bg-blue-500/25 transition">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-[13px] font-semibold text-gray-300">Contacts</span>
            </Link>
            <Link :href="route('admin.email.templates.index')" class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 hover:bg-[#0e2236] transition group">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center group-hover:bg-amber-500/25 transition">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                </div>
                <span class="text-[13px] font-semibold text-gray-300">Templates</span>
            </Link>
            <Link :href="route('admin.email.segments.index')" class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 hover:bg-[#0e2236] transition group">
                <div class="w-10 h-10 rounded-xl bg-green-500/15 flex items-center justify-center group-hover:bg-green-500/25 transition">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="text-[13px] font-semibold text-gray-300">Segments</span>
            </Link>
            <Link :href="route('admin.email.automations.index')" class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 hover:bg-[#0e2236] transition group">
                <div class="w-10 h-10 rounded-xl bg-purple-500/15 flex items-center justify-center group-hover:bg-purple-500/25 transition">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-[13px] font-semibold text-gray-300">Automations</span>
            </Link>
        </div>

        <!-- Two-column: Recent Campaigns + Recent Contacts -->
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Recent Campaigns -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.06]">
                    <h2 class="text-[13px] font-semibold text-white">Recent Campaigns</h2>
                    <Link :href="route('admin.email.campaigns.index')" class="text-[11px] text-amber-400 hover:underline">View all</Link>
                </div>
                <div v-if="recentCampaigns.length === 0" class="px-5 py-10 text-center text-gray-600 text-sm">No campaigns yet.</div>
                <div v-for="c in recentCampaigns" :key="c.id" class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.04] last:border-0 hover:bg-white/[0.03] transition">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-white truncate">{{ c.name }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ c.segment }} · {{ c.sent_at ?? 'Not sent' }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span v-if="c.sent > 0" class="hidden sm:block text-[11px] text-gray-400">{{ c.open_rate }}% open</span>
                        <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold', statusColor(c.status)]">{{ c.status }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Contacts -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.06]">
                    <h2 class="text-[13px] font-semibold text-white">New Contacts</h2>
                    <Link :href="route('admin.email.contacts.index')" class="text-[11px] text-amber-400 hover:underline">View all</Link>
                </div>
                <div v-if="recentContacts.length === 0" class="px-5 py-10 text-center text-gray-600 text-sm">No contacts yet.</div>
                <div v-for="c in recentContacts" :key="c.id" class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.04] last:border-0 hover:bg-white/[0.03] transition">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500/20 to-blue-600/20 flex items-center justify-center text-[11px] font-bold text-blue-400 flex-shrink-0">
                        {{ (c.full_name || c.email).charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-white truncate">{{ c.full_name || c.email }}</p>
                        <p class="text-[11px] text-gray-500 truncate">{{ c.email }}</p>
                    </div>
                    <span class="text-[10px] text-gray-600 shrink-0">{{ c.created_at }}</span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
