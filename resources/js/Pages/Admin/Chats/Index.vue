<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { __ } = useTranslations();

const props = defineProps({
    chats:   { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const searchQuery  = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');

const applyFilters = () => {
    router.get(route('admin.chats.index'), {
        search: searchQuery.value,
        status: statusFilter.value !== 'all' ? statusFilter.value : '',
    }, { preserveState: true, preserveScroll: true });
};

const chatsData = computed(() => props.chats?.data || []);

const activeCount = computed(() => chatsData.value.filter(c => c.status === 'active').length);
const leadCount   = computed(() => chatsData.value.filter(c => c.is_lead).length);
const todayCount  = computed(() => chatsData.value.filter(c => new Date(c.created_at).toDateString() === new Date().toDateString()).length);

const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }) : '—';

const statusCfg = {
    active:   { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', label: 'Active' },
    closed:   { bg: 'bg-gray-400/10',    text: 'text-gray-400',    dot: 'bg-gray-400',    label: 'Closed' },
    archived: { bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400',    label: 'Archived' },
    lead:     { bg: 'bg-amber-400/10',   text: 'text-amber-400',   dot: 'bg-amber-400',   label: 'Lead' },
};
const getStatus = s => statusCfg[(s||'').toLowerCase()] || statusCfg.closed;

const initials    = n => (n||'G').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
const avatarGrad  = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600'];
    return p[[...(name||'G')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};

const viewChat = id => router.visit(route('admin.chats.show', id));

const kpis = [
    { label: 'Total Chats',      value: () => (props.chats?.total || 0).toLocaleString(), accent: '#60a5fa', glow: 'rgba(96,165,250,0.15)',  bg: 'from-blue-500/[0.10]',   iconBg: 'bg-blue-500/15',   iconColor: 'text-blue-400',   icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z' },
    { label: 'Active Chats',     value: () => activeCount.value.toLocaleString(),          accent: '#34d399', glow: 'rgba(52,211,153,0.15)',  bg: 'from-emerald-500/[0.10]',iconBg: 'bg-emerald-500/15',iconColor: 'text-emerald-400', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Leads Generated',  value: () => leadCount.value.toLocaleString(),            accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',  bg: 'from-amber-500/[0.12]',  iconBg: 'bg-amber-500/15',  iconColor: 'text-amber-400',  icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
    { label: 'Created Today',    value: () => todayCount.value.toLocaleString(),           accent: '#c084fc', glow: 'rgba(192,132,252,0.15)', bg: 'from-purple-500/[0.10]', iconBg: 'bg-purple-500/15', iconColor: 'text-purple-400', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
];

const inp = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
</script>

<template>
    <Head title="AI Chat Logs" />
    <AdminLayout>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white tracking-tight">AI Chat Logs</h1>
            <p class="text-[13px] text-gray-500 mt-1">Monitor and manage all AI chat conversations across the platform</p>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="searchQuery" type="text" placeholder="Search by session ID, email, or page…" :class="inp.replace('px-3.5','pl-9 pr-3.5')" @keyup.enter="applyFilters"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Status</label>
                    <select v-model="statusFilter" :class="sel" @change="applyFilters">
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                        <option value="lead">Lead</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="applyFilters"
                        class="w-full h-10 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="kpi in kpis" :key="kpi.label"
                class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3 hover:border-white/[0.12] transition-all duration-200"
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
                <h2 class="text-[14px] font-bold text-white">All Conversations
                    <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ props.chats?.total || 0 }}</span>
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-white/[0.05]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">User / Session</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Page Name</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Messages</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Status</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Created At</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!chatsData.length">
                            <td colspan="6" class="px-6 py-14 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                <p class="text-[13px] font-semibold text-gray-600">No chats found</p>
                                <p class="text-[11px] text-gray-700 mt-1">Try adjusting your filters.</p>
                            </td>
                        </tr>
                        <tr v-for="chat in chatsData" :key="chat.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.025] transition-colors cursor-pointer"
                            @click="viewChat(chat.id)">
                            <!-- User / Session -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold bg-gradient-to-br', avatarGrad(chat.user?.name)]">
                                        {{ initials(chat.user?.name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12px] font-semibold text-gray-200 leading-none">{{ chat.user?.name || 'Guest' }}</p>
                                        <p class="text-[10px] text-gray-600 mt-0.5 truncate max-w-[180px]">{{ chat.user?.email || (chat.session_id || '').substring(0, 18) + '…' }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Page -->
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-[12px] text-gray-200 truncate">{{ chat.page_name }}</p>
                                <p class="text-[10px] text-gray-600 mt-0.5">{{ chat.page_type }}</p>
                            </td>
                            <!-- Messages -->
                            <td class="px-6 py-4">
                                <span class="text-[12px] text-gray-400">{{ chat.message_count || 0 }} messages</span>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', getStatus(chat.status).bg, getStatus(chat.status).text]">
                                        <span :class="['w-1.5 h-1.5 rounded-full', getStatus(chat.status).dot]"></span>
                                        {{ getStatus(chat.status).label }}
                                    </span>
                                    <span v-if="chat.is_lead" class="inline-flex items-center gap-1 text-[10px] font-bold rounded-full px-2 py-0.5 bg-amber-400/10 text-amber-400">
                                        ★ Lead
                                    </span>
                                </div>
                            </td>
                            <!-- Date -->
                            <td class="px-6 py-4 text-[12px] text-gray-600">{{ formatDate(chat.created_at) }}</td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-right" @click.stop>
                                <Link :href="route('admin.chats.show', chat.id)" class="text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">View</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-[12px] text-gray-600">
                    Showing <span class="text-gray-400 font-medium">{{ props.chats?.from || 0 }}</span>
                    to <span class="text-gray-400 font-medium">{{ props.chats?.to || 0 }}</span>
                    of <span class="text-gray-400 font-medium">{{ props.chats?.total || 0 }}</span> results
                </p>
                <div v-if="props.chats?.links && props.chats.links.length > 3" class="flex items-center gap-1">
                    <Link v-for="link in props.chats.links" :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            'inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-lg text-[12px] font-medium transition-all',
                            link.active  ? 'bg-amber-400 text-[#0b1e33] font-bold shadow-sm' : 'text-gray-500 hover:text-white hover:bg-white/[0.06]',
                            !link.url    ? 'opacity-30 pointer-events-none' : ''
                        ]"
                    />
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
