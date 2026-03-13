<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    orders:  { type: Object, default: () => ({}) },
    stats:   { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const search            = ref(props.filters?.search       || '');
const statusFilter      = ref(props.filters?.status       || '');
const serviceTypeFilter = ref(props.filters?.service_type || '');
const dateFrom          = ref(props.filters?.date_from    || '');
const dateTo            = ref(props.filters?.date_to      || '');

const formatCurrency = v => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(v || 0);

const applyFilters = () => router.get(route('admin.orders.index'), {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    service_type: serviceTypeFilter.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
}, { preserveState: true, preserveScroll: true });

const clearFilters = () => {
    search.value = ''; statusFilter.value = ''; serviceTypeFilter.value = ''; dateFrom.value = ''; dateTo.value = '';
    applyFilters();
};

const statusCfg = {
    pending:      { bg: 'bg-amber-400/10',   text: 'text-amber-400',   dot: 'bg-amber-400'   },
    in_progress:  { bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400'    },
    under_review: { bg: 'bg-purple-400/10',  text: 'text-purple-400',  dot: 'bg-purple-400'  },
    completed:    { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400' },
    cancelled:    { bg: 'bg-red-400/10',     text: 'text-red-400',     dot: 'bg-red-400'     },
    on_hold:      { bg: 'bg-gray-500/10',    text: 'text-gray-400',    dot: 'bg-gray-400'    },
    refunded:     { bg: 'bg-rose-400/10',    text: 'text-rose-400',    dot: 'bg-rose-400'    },
};
const colorMap = {
    yellow: statusCfg.pending,
    amber:  statusCfg.pending,
    blue:   statusCfg.in_progress,
    purple: statusCfg.under_review,
    green:  statusCfg.completed,
    red:    statusCfg.cancelled,
    gray:   statusCfg.on_hold,
    orange: { bg: 'bg-orange-400/10', text: 'text-orange-400', dot: 'bg-orange-400' },
};
const getBadge  = (color) => colorMap[color] || statusCfg.on_hold;
const getStatus = (key)   => statusCfg[key?.toLowerCase().replace(' ','_')] || statusCfg.on_hold;

const initials = n => (n||'U').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
const avatarGrad = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600','from-fuchsia-500 to-purple-700'];
    return p[[...(name||'U')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};

const kpis = [
    { label: 'Total Orders',   key: 'total',         accent: '#60a5fa', glow: 'rgba(96,165,250,0.15)',   bg: 'from-blue-500/[0.10]',    iconBg: 'bg-blue-500/15',    iconColor: 'text-blue-400',    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { label: 'Pending',        key: 'pending',       accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',   bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'In Progress',    key: 'in_progress',   accent: '#818cf8', glow: 'rgba(129,140,248,0.15)',  bg: 'from-indigo-500/[0.10]',  iconBg: 'bg-indigo-500/15',  iconColor: 'text-indigo-400',  icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { label: 'Completed',      key: 'completed',     accent: '#34d399', glow: 'rgba(52,211,153,0.15)',   bg: 'from-emerald-500/[0.10]', iconBg: 'bg-emerald-500/15', iconColor: 'text-emerald-400', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Total Revenue',  key: 'total_revenue', accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',   bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', currency: true },
];

const inp = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
</script>

<template>
    <Head title="Order Management" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Order Management</h1>
                <p class="text-[13px] text-gray-500 mt-1">Manage all client orders and business formation requests</p>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div v-for="kpi in kpis" :key="kpi.key"
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
                    <p class="text-[22px] font-extrabold text-white leading-none">
                        {{ kpi.currency ? formatCurrency(stats?.[kpi.key]) : (stats?.[kpi.key] || 0).toLocaleString() }}
                    </p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600 mt-1.5">{{ kpi.label }}</p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" :style="{background: 'linear-gradient(90deg,' + kpi.accent + ',transparent)'}"></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Order #, Client, Business…" :class="inp.replace('px-3.5','pl-9 pr-3.5')"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Status</label>
                    <select v-model="statusFilter" @change="applyFilters" :class="sel">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="under_review">Under Review</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="on_hold">On Hold</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Service Type</label>
                    <select v-model="serviceTypeFilter" @change="applyFilters" :class="sel">
                        <option value="">All Services</option>
                        <option value="llc">LLC Formation</option>
                        <option value="c_corp">C-Corporation</option>
                        <option value="s_corp">S-Corporation</option>
                        <option value="nonprofit">Nonprofit</option>
                        <option value="green_card">Green Card</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Date From</label>
                    <input v-model="dateFrom" @change="applyFilters" type="date" :class="inp"/>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Date To</label>
                    <input v-model="dateTo" @change="applyFilters" type="date" :class="inp"/>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-3">
                <button @click="clearFilters"
                    class="inline-flex items-center gap-1.5 px-4 h-9 rounded-lg border border-white/[0.08] bg-white/[0.03] text-[13px] font-medium text-gray-400 hover:text-white hover:bg-white/[0.07] transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear Filters
                </button>
                <button @click="applyFilters"
                    class="inline-flex items-center gap-1.5 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-semibold text-[#0b1e33] hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h2 class="text-[14px] font-bold text-white">All Orders
                        <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ orders?.total || orders?.data?.length || 0 }}</span>
                    </h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-white/[0.05]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Order #</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Client</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Service</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Business Name</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Status</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Payment</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Amount</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Date</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Empty state -->
                        <tr v-if="!orders?.data?.length">
                            <td colspan="9" class="px-6 py-14 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-[13px] font-semibold text-gray-600">No orders found</p>
                                <p class="text-[11px] text-gray-700 mt-1">Try adjusting your search or filter criteria.</p>
                            </td>
                        </tr>
                        <!-- Rows -->
                        <tr v-for="order in orders?.data || []" :key="order.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.025] transition-colors">
                            <!-- Order # -->
                            <td class="px-6 py-4">
                                <span class="font-mono text-[12px] text-amber-400/80 font-semibold">#{{ order.order_number }}</span>
                            </td>
                            <!-- Client -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full flex-shrink-0 overflow-hidden">
                                      <img v-if="order.client_avatar" :src="order.client_avatar" :alt="order.client_name" class="w-full h-full object-cover" />
                                      <div v-else :class="['w-full h-full flex items-center justify-center text-white text-[10px] font-bold bg-gradient-to-br', avatarGrad(order.client_name)]">
                                        {{ initials(order.client_name) }}
                                      </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12px] font-semibold text-gray-200 leading-none truncate">{{ order.client_name || '—' }}</p>
                                        <p class="text-[10px] text-gray-600 truncate mt-0.5">{{ order.client_email }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Service -->
                            <td class="px-6 py-4">
                                <span v-if="order.service_type_badge"
                                    :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', getBadge(order.service_type_badge.color).bg, getBadge(order.service_type_badge.color).text]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getBadge(order.service_type_badge.color).dot]"></span>
                                    {{ order.service_type_badge.label }}
                                </span>
                                <span v-else class="text-gray-600">—</span>
                            </td>
                            <!-- Business Name -->
                            <td class="px-6 py-4 text-[13px] text-gray-400 font-medium max-w-[160px] truncate">{{ order.entity_name || '—' }}</td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', getBadge(order.status_color).bg, getBadge(order.status_color).text]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getBadge(order.status_color).dot]"></span>
                                    {{ order.status_display }}
                                </span>
                            </td>
                            <!-- Payment -->
                            <td class="px-6 py-4">
                                <span v-if="order.payment_status"
                                    :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', getBadge(order.payment_status.color).bg, getBadge(order.payment_status.color).text]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getBadge(order.payment_status.color).dot]"></span>
                                    {{ order.payment_status.label }}
                                </span>
                                <span v-else class="text-gray-600">—</span>
                            </td>
                            <!-- Amount -->
                            <td class="px-6 py-4 text-right font-semibold" :class="(order.total_amount || 0) > 0 ? 'text-amber-400' : 'text-gray-600'">
                                {{ formatCurrency(order.total_amount) }}
                            </td>
                            <!-- Date -->
                            <td class="px-6 py-4 text-[12px] text-gray-600">{{ order.created_at_human }}</td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <Link :href="route('admin.orders.show', { order: order.id })"
                                        class="text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">View</Link>
                                    <Link :href="route('admin.orders.edit', { order: order.id })"
                                        class="text-[12px] font-semibold text-blue-400 hover:text-blue-300 transition-colors">Edit</Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="orders?.links && orders.links.length > 3" class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-between">
                <p class="text-[12px] text-gray-600">
                    Showing {{ orders.from }}–{{ orders.to }} of {{ orders.total }} orders
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in orders.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url"
                            :class="['inline-flex items-center justify-center w-8 h-8 rounded-lg text-[12px] font-medium transition-all',
                                link.active ? 'bg-amber-400 text-[#0b1e33] font-bold' : 'text-gray-500 hover:text-white hover:bg-white/[0.06]']"
                            v-html="link.label"/>
                        <span v-else
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-[12px] text-gray-700"
                            v-html="link.label"/>
                    </template>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
