<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    stats:            { type: Object,  default: () => ({}) },
    recent_orders:    { type: Array,   default: () => [] },
    recent_users:     { type: Array,   default: () => [] },
    sales_by_state:   { type: Array,   default: () => [] },
    order_chart_data: { type: Object,  default: () => ({ labels: [], data: [], revenue: [] }) },
});

// ── helpers ───────────────────────────────────────────────────────────────────
const formatCurrency = v => '$' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
const formatDate     = d => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
const initials       = n => (n || 'U').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
const avatarGrad     = (name) => {
    const palettes = [
        'from-violet-500 to-indigo-600',
        'from-amber-400 to-orange-500',
        'from-emerald-400 to-teal-600',
        'from-rose-400 to-pink-600',
        'from-sky-400 to-cyan-600',
        'from-fuchsia-500 to-purple-700',
    ];
    const idx = [...(name || 'U')].reduce((a, c) => a + c.charCodeAt(0), 0) % palettes.length;
    return palettes[idx];
};

const statusConfig = {
    pending:    { label: 'Pending',    bg: 'bg-amber-400/10',   text: 'text-amber-400',   dot: 'bg-amber-400'   },
    processing: { label: 'Processing', bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400'    },
    completed:  { label: 'Completed',  bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400' },
    cancelled:  { label: 'Cancelled',  bg: 'bg-red-400/10',     text: 'text-red-400',     dot: 'bg-red-400'     },
    refunded:   { label: 'Refunded',   bg: 'bg-gray-400/10',    text: 'text-gray-400',    dot: 'bg-gray-400'    },
};
const getStatus = s => statusConfig[s?.toLowerCase()] || statusConfig.pending;

// ── KPI cards ─────────────────────────────────────────────────────────────────
const kpis = computed(() => [
    {
        label:    'Total Revenue',
        value:    formatCurrency(props.stats.total_revenue),
        sub:      props.stats.revenue_change >= 0 ? '+' + (props.stats.revenue_change || 0) + '% from last month' : (props.stats.revenue_change || 0) + '% from last month',
        positive: (props.stats.revenue_change || 0) >= 0,
        accent:   '#f4b840',
        glow:     'rgba(244,184,64,0.18)',
        bg:       'from-amber-500/[0.12] to-transparent',
        iconBg:   'bg-amber-500/15',
        iconColor: 'text-amber-400',
        icon:     'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label:    'Total Orders',
        value:    (props.stats.total_orders || 0).toLocaleString(),
        sub:      (props.stats.pending_orders || 0) + ' pending review',
        positive: true,
        accent:   '#60a5fa',
        glow:     'rgba(96,165,250,0.15)',
        bg:       'from-blue-500/[0.10] to-transparent',
        iconBg:   'bg-blue-500/15',
        iconColor: 'text-blue-400',
        icon:     'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        label:    'Total Customers',
        value:    (props.stats.total_users || 0).toLocaleString(),
        sub:      (props.stats.new_users || 0) + ' joined this month',
        positive: true,
        accent:   '#34d399',
        glow:     'rgba(52,211,153,0.15)',
        bg:       'from-emerald-500/[0.10] to-transparent',
        iconBg:   'bg-emerald-500/15',
        iconColor: 'text-emerald-400',
        icon:     'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
        label:    'Pending Orders',
        value:    (props.stats.pending_orders || 0).toLocaleString(),
        sub:      'Awaiting processing',
        positive: (props.stats.pending_orders || 0) === 0,
        accent:   '#c084fc',
        glow:     'rgba(192,132,252,0.15)',
        bg:       'from-purple-500/[0.10] to-transparent',
        iconBg:   'bg-purple-500/15',
        iconColor: 'text-purple-400',
        icon:     'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
]);

// ── Chart.js ──────────────────────────────────────────────────────────────────
const chartCanvas = ref(null);
let chartInstance = null;

onMounted(() => {
    import('chart.js').then(({ Chart, registerables }) => {
        Chart.register(...registerables);

        const labels  = props.order_chart_data?.labels  || ['Jan','Feb','Mar','Apr','May','Jun','Jul'];
        const orders  = props.order_chart_data?.data    || [0,0,0,0,0,0,0];
        const revenue = props.order_chart_data?.revenue || [0,0,0,0,0,0,0];

        const ctx = chartCanvas.value?.getContext('2d');
        if (!ctx) return;

        const gradGold = ctx.createLinearGradient(0, 0, 0, 260);
        gradGold.addColorStop(0,   'rgba(244,184,64,0.30)');
        gradGold.addColorStop(1,   'rgba(244,184,64,0.00)');

        const gradBlue = ctx.createLinearGradient(0, 0, 0, 260);
        gradBlue.addColorStop(0,   'rgba(96,165,250,0.25)');
        gradBlue.addColorStop(1,   'rgba(96,165,250,0.00)');

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Revenue ($)',
                        data: revenue,
                        borderColor: '#f4b840',
                        backgroundColor: gradGold,
                        fill: true,
                        tension: 0.45,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#f4b840',
                        pointBorderColor: '#0a1628',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        yAxisID: 'y1',
                    },
                    {
                        label: 'Orders',
                        data: orders,
                        borderColor: '#60a5fa',
                        backgroundColor: gradBlue,
                        fill: true,
                        tension: 0.45,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#60a5fa',
                        pointBorderColor: '#0a1628',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { color: '#94a3b8', usePointStyle: true, pointStyleWidth: 8, boxHeight: 6, font: { size: 12 }, padding: 20 },
                    },
                    tooltip: {
                        backgroundColor: '#0f2040',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                        padding: 12,
                        titleColor: '#e2e8f0',
                        bodyColor: '#94a3b8',
                        callbacks: {
                            label: ctx => ctx.datasetIndex === 0
                                ? ' Revenue: $' + Number(ctx.parsed.y).toLocaleString()
                                : ' Orders: '   + ctx.parsed.y,
                        },
                    },
                },
                scales: {
                    x: {
                        grid:  { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: '#475569', font: { size: 11 } },
                        border: { color: 'transparent' },
                    },
                    y: {
                        position: 'left',
                        grid:  { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: '#475569', font: { size: 11 }, stepSize: 1 },
                        border: { color: 'transparent' },
                    },
                    y1: {
                        position: 'right',
                        grid:  { drawOnChartArea: false },
                        ticks: { color: '#475569', font: { size: 11 }, callback: v => '$' + v.toLocaleString() },
                        border: { color: 'transparent' },
                    },
                },
            },
        });
    });
});

onBeforeUnmount(() => { chartInstance?.destroy(); });
</script>

<template>
    <AdminLayout>

        <!-- ── KPI cards ─────────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
            <div
                v-for="kpi in kpis" :key="kpi.label"
                class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-4 group hover:border-white/[0.12] transition-all duration-200"
                :style="{boxShadow: '0 0 32px 0 ' + kpi.glow}"
            >
                <!-- gradient wash -->
                <div :class="['absolute inset-0 bg-gradient-to-br opacity-60 pointer-events-none', kpi.bg]"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500">{{ kpi.label }}</p>
                        <p class="mt-2 text-[26px] font-extrabold tracking-tight text-white leading-none">{{ kpi.value }}</p>
                    </div>
                    <div :class="['w-11 h-11 rounded-xl flex items-center justify-center shrink-0', kpi.iconBg]">
                        <svg :class="['w-5 h-5', kpi.iconColor]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="kpi.icon"/>
                        </svg>
                    </div>
                </div>
                <div class="relative flex items-center gap-1.5">
                    <span :class="['text-[11px] font-medium', kpi.positive ? 'text-emerald-400' : 'text-red-400']">{{ kpi.sub }}</span>
                </div>
                <!-- bottom accent bar -->
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" :style="{background: 'linear-gradient(90deg,' + kpi.accent + ',transparent)'}"></div>
            </div>
        </div>

        <!-- ── Chart + Recent users ───────────────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-8">

            <!-- Revenue / Orders Chart -->
            <div class="xl:col-span-2 rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-[14px] font-bold text-white">Revenue & Orders</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Last 7 months performance</p>
                    </div>
                </div>
                <div class="relative h-[240px]">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[14px] font-bold text-white">New Customers</h2>
                    <span class="text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2.5 py-0.5">{{ recent_users.length }} recent</span>
                </div>
                <div class="flex-1 overflow-auto">
                    <div v-if="recent_users.length === 0" class="flex flex-col items-center justify-center h-full text-gray-600 py-10">
                        <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <p class="text-[12px]">No customers yet</p>
                    </div>
                    <ul v-else class="space-y-3">
                        <li v-for="u in recent_users" :key="u.id" class="flex items-center gap-3 group">
                            <div class="w-9 h-9 rounded-full flex-shrink-0 overflow-hidden">
                                <img v-if="u.avatar" :src="u.avatar" :alt="u.name" class="w-full h-full object-cover" />
                                <div v-else :class="['w-full h-full flex items-center justify-center text-white text-[11px] font-bold bg-gradient-to-br', avatarGrad(u.name)]">
                                    {{ initials(u.name) }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-semibold text-gray-200 truncate">{{ u.name }}</p>
                                <p class="text-[10px] text-gray-600 truncate">{{ u.email }}</p>
                            </div>
                            <p class="text-[11px] text-gray-600 shrink-0">{{ formatDate(u.created_at) }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ── Recent Orders ───────────────────────────────────────────────────── -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h2 class="text-[14px] font-bold text-white">Recent Orders</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">Latest transactions</p>
                </div>
                <Link :href="route('admin.orders.index')" class="text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors flex items-center gap-1">
                    View all
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[580px] text-[13px]">
                    <thead>
                        <tr class="border-b border-white/[0.05]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Customer</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Order</th>
                            <th class="hidden sm:table-cell text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Plan</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Status</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="recent_orders.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-600">
                                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-[12px]">No orders yet</p>
                            </td>
                        </tr>
                        <tr v-for="order in recent_orders" :key="order.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.025] transition-colors group cursor-pointer">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex-shrink-0 overflow-hidden">
                                        <img v-if="order.user?.avatar" :src="order.user.avatar" :alt="order.user.name" class="w-full h-full object-cover" />
                                        <div v-else :class="['w-full h-full flex items-center justify-center text-white text-[10px] font-bold bg-gradient-to-br', avatarGrad(order.user?.name)]">
                                            {{ initials(order.user?.name) }}
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12px] font-semibold text-gray-200 truncate leading-none">{{ order.user?.name || '—' }}</p>
                                        <p class="text-[10px] text-gray-600 truncate mt-0.5">{{ order.user?.email || '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-[11px] text-amber-400/80">#{{ String(order.id).padStart(5,'0') }}</td>
                            <td class="hidden sm:table-cell px-6 py-4 text-gray-300 font-medium">{{ order.plan?.name || '—' }}</td>
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', getStatus(order.status).bg, getStatus(order.status).text]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getStatus(order.status).dot]"></span>
                                    {{ getStatus(order.status).label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-white">{{ formatCurrency(order.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AdminLayout>
</template>
