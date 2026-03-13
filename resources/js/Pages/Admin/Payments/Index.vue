<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    payments: { type: Object, default: () => ({}) },
    stats:    { type: Object, default: () => ({}) },
});

const searchQuery  = ref('');
const statusFilter = ref('');
const methodFilter = ref('');
const dateFilter   = ref('');
const showModal    = ref(false);
const processing   = ref(false);

const form = ref({ order_number: '', amount: '', payment_method: '', transaction_id: '', notes: '' });

const paymentsArray   = computed(() => props.payments?.data || []);
const totalRevenue    = computed(() => props.stats?.total_revenue || 0);
const pendingPayments = computed(() => props.stats?.pending || 0);
const monthlyRevenue  = computed(() => props.stats?.this_month_revenue || 0);

const filteredPayments = computed(() => {
    let list = paymentsArray.value;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(p =>
            (p.transaction_id||'').toLowerCase().includes(q) ||
            (p.client_name||'').toLowerCase().includes(q) ||
            (p.order_number||'').toLowerCase().includes(q)
        );
    }
    if (statusFilter.value) list = list.filter(p => p.status === statusFilter.value);
    if (methodFilter.value) list = list.filter(p => p.payment_method === methodFilter.value);
    return list;
});

const formatCurrency = v => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(v || 0);
const formatDate     = d => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';

const statusCfg = {
    succeeded: { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', icon: 'M5 13l4 4L19 7' },
    completed: { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', icon: 'M5 13l4 4L19 7' },
    pending:   { bg: 'bg-amber-400/10',   text: 'text-amber-400',   dot: 'bg-amber-400',   icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    failed:    { bg: 'bg-red-400/10',     text: 'text-red-400',     dot: 'bg-red-400',     icon: 'M6 18L18 6M6 6l12 12' },
    refunded:  { bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400',    icon: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6' },
};
const getStatus = s => statusCfg[(s||'').toLowerCase()] || statusCfg.pending;

const methodIcon = m => ({
    stripe:        { label: 'Stripe',        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    paypal:        { label: 'PayPal',         icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    bank_transfer: { label: 'Bank Transfer',  icon: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z' },
    cash:          { label: 'Cash',           icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    check:         { label: 'Check',          icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
})[(m||'').toLowerCase()] || { label: (m||'').replace('_',' '), icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' };

const initials   = n => (n||'U').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
const avatarGrad = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600','from-fuchsia-500 to-purple-700'];
    return p[[...(name||'U')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};

const viewPayment = id => router.visit(`/admin/payments/${id}`);
const refundPayment = id => {
    if (confirm('Refund this payment?')) router.patch(route('admin.payments.update', id), { status: 'refunded' });
};

const submitManual = () => {
    processing.value = true;
    router.post(route('admin.payments.manual'), {
        ...form.value,
        amount: Math.round(parseFloat(form.value.amount) * 100),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            processing.value = false;
            form.value = { order_number: '', amount: '', payment_method: '', transaction_id: '', notes: '' };
        },
        onError: () => { processing.value = false; },
    });
};

const kpis = [
    { label: 'Total Revenue',  value: () => formatCurrency(totalRevenue.value),    accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',  bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Total Payments', value: () => (props.stats?.total || 0).toLocaleString(), accent: '#60a5fa', glow: 'rgba(96,165,250,0.15)',  bg: 'from-blue-500/[0.10]',    iconBg: 'bg-blue-500/15',    iconColor: 'text-blue-400',    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { label: 'Pending',        value: () => pendingPayments.value.toLocaleString(), accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',  bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'This Month',     value: () => formatCurrency(monthlyRevenue.value),   accent: '#c084fc', glow: 'rgba(192,132,252,0.15)', bg: 'from-purple-500/[0.10]',  iconBg: 'bg-purple-500/15',  iconColor: 'text-purple-400',  icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
];

const inp = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
</script>

<template>
    <Head title="Payment Management" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Payment Management</h1>
                <p class="text-[13px] text-gray-500 mt-1">Track and manage all client payments and transactions</p>
            </div>
            <button @click="showModal = true"
                class="inline-flex items-center gap-2 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-semibold text-[#0b1e33] hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Manual Payment
            </button>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
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

        <!-- Filters -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="searchQuery" type="text" placeholder="Search payments…" :class="inp.replace('px-3.5','pl-9 pr-3.5')"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Status</label>
                    <select v-model="statusFilter" :class="sel">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Payment Method</label>
                    <select v-model="methodFilter" :class="sel">
                        <option value="">All Methods</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Date Range</label>
                    <select v-model="dateFilter" :class="sel">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-white">All Payments
                    <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ props.payments?.total || filteredPayments.length }}</span>
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-white/[0.05]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Payment</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Client</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Amount</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Method</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Status</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Date</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredPayments.length">
                            <td colspan="7" class="px-6 py-14 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <p class="text-[13px] font-semibold text-gray-600">No payments found</p>
                                <p class="text-[11px] text-gray-700 mt-1">Try adjusting your filters.</p>
                            </td>
                        </tr>
                        <tr v-for="payment in filteredPayments" :key="payment.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.025] transition-colors">
                            <!-- Payment ID -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0', getStatus(payment.status).bg]">
                                        <svg :class="['w-4 h-4', getStatus(payment.status).text]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getStatus(payment.status).icon"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12px] font-mono text-gray-200 leading-none">{{ payment.transaction_id }}</p>
                                        <p class="text-[10px] text-gray-600 mt-0.5">Order: {{ payment.order_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Client -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full flex-shrink-0 overflow-hidden">
                                      <img v-if="payment.client_avatar" :src="payment.client_avatar" :alt="payment.client_name" class="w-full h-full object-cover" />
                                      <div v-else :class="['w-full h-full flex items-center justify-center text-white text-[10px] font-bold bg-gradient-to-br', avatarGrad(payment.client_name)]">
                                        {{ initials(payment.client_name) }}
                                      </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12px] font-semibold text-gray-200 leading-none truncate">{{ payment.client_name || '—' }}</p>
                                        <p class="text-[10px] text-gray-600 truncate mt-0.5">{{ payment.client_email }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Amount -->
                            <td class="px-6 py-4 text-right">
                                <p class="font-bold text-white">{{ formatCurrency(payment.amount) }}</p>
                                <p v-if="payment.fee" class="text-[10px] text-gray-600 mt-0.5">Fee: ${{ payment.fee?.toFixed(2) }}</p>
                            </td>
                            <!-- Method -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-white/[0.05] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="methodIcon(payment.payment_method).icon"/>
                                        </svg>
                                    </div>
                                    <span class="text-[12px] text-gray-300 capitalize">{{ methodIcon(payment.payment_method).label }}</span>
                                </div>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 capitalize', getStatus(payment.status).bg, getStatus(payment.status).text]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getStatus(payment.status).dot]"></span>
                                    {{ payment.status }}
                                </span>
                            </td>
                            <!-- Date -->
                            <td class="px-6 py-4 text-[12px] text-gray-600">{{ formatDate(payment.created_at) }}</td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <button @click="viewPayment(payment.id)" class="text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">View</button>
                                    <button v-if="payment.status === 'completed' || payment.status === 'succeeded'" @click="refundPayment(payment.id)" class="text-[12px] font-semibold text-gray-600 hover:text-red-400 transition-colors">Refund</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination info -->
            <div class="px-6 py-4 border-t border-white/[0.06]">
                <p class="text-[12px] text-gray-600">
                    Showing <span class="text-gray-400 font-medium">{{ props.payments?.from || 0 }}</span>
                    to <span class="text-gray-400 font-medium">{{ props.payments?.to || 0 }}</span>
                    of <span class="text-gray-400 font-medium">{{ props.payments?.total || 0 }}</span> results
                </p>
            </div>
        </div>

        <!-- Manual Payment Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showModal = false"></div>
                    <!-- Panel -->
                    <div class="relative w-full max-w-md rounded-2xl border border-white/[0.10] bg-[#0d1e35] shadow-2xl overflow-hidden" style="box-shadow:0 0 60px 0 rgba(0,0,0,0.5),0 0 0 1px rgba(255,255,255,0.07)">
                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4 border-b border-white/[0.06] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-white">Record Manual Payment</h3>
                                    <p class="text-[11px] text-gray-600">Add an offline or manual transaction</p>
                                </div>
                            </div>
                            <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-600 hover:text-white hover:bg-white/[0.08] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Body -->
                        <form @submit.prevent="submitManual" class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Order Number <span class="text-amber-400">*</span></label>
                                <input v-model="form.order_number" type="text" required placeholder="e.g. ORD-2024-001" :class="inp"/>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Amount (USD) <span class="text-amber-400">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-600 text-[13px] font-semibold">$</span>
                                    <input v-model="form.amount" type="number" step="0.01" min="0" required placeholder="0.00" :class="inp.replace('px-3.5','pl-7 pr-3.5')"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Payment Method <span class="text-amber-400">*</span></label>
                                <select v-model="form.payment_method" required :class="sel">
                                    <option value="">Select method…</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Transaction / Reference ID</label>
                                <input v-model="form.transaction_id" type="text" placeholder="Optional reference number" :class="inp"/>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Notes</label>
                                <textarea v-model="form.notes" rows="3" placeholder="Additional payment details…"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition resize-none"></textarea>
                            </div>
                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-white/[0.06]">
                                <button type="button" @click="showModal = false"
                                    class="inline-flex items-center justify-center px-5 h-9 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="processing"
                                    class="inline-flex items-center justify-center gap-2 px-5 h-9 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 disabled:opacity-50 transition-all shadow-lg shadow-amber-500/20">
                                    <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                    </svg>
                                    {{ processing ? 'Recording…' : 'Record Payment' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
