<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    payment: { type: Object, required: true },
});

const formatCurrency = v => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 }).format(v || 0);
const formatDate     = d => d ? new Date(d).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';

const cardBrandIcon = (brand) => {
    const b = (brand || '').toLowerCase();
    if (b === 'visa') return '💳 Visa';
    if (b === 'mastercard') return '💳 Mastercard';
    if (b === 'amex' || b === 'american_express') return '💳 Amex';
    if (b === 'discover') return '💳 Discover';
    return '💳 ' + (brand || 'Card');
};

const cardMasked = (last4) => last4 ? `•••• •••• •••• ${last4}` : '—';

const statusCfg = {
    succeeded:  { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', label: 'Succeeded' },
    completed:  { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', label: 'Completed' },
    pending:    { bg: 'bg-amber-400/10',   text: 'text-amber-400',   dot: 'bg-amber-400',   label: 'Pending' },
    failed:     { bg: 'bg-red-400/10',     text: 'text-red-400',     dot: 'bg-red-400',     label: 'Failed' },
    refunded:   { bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400',    label: 'Refunded' },
};
const getStatus = s => statusCfg[(s||'').toLowerCase()] || statusCfg.pending;

const refund = () => {
    if (confirm('Refund this payment?')) {
        router.patch(route('admin.payments.update', props.payment.id), { status: 'refunded' });
    }
};
</script>

<template>
    <Head :title="`Payment ${payment.transaction_id}`" />
    <AdminLayout>

        <!-- Breadcrumb + Back -->
        <div class="flex items-center gap-3 mb-6">
            <Link :href="route('admin.payments.index')"
                class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Payments
            </Link>
            <span class="text-gray-700">/</span>
            <span class="text-[13px] text-gray-400 font-mono">{{ payment.transaction_id }}</span>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Payment Details</h1>
                <p class="text-[13px] text-gray-500 mt-1">Transaction ID: <span class="font-mono text-gray-400">{{ payment.transaction_id }}</span></p>
            </div>
            <div class="flex items-center gap-3">
                <span :class="['inline-flex items-center gap-1.5 text-[12px] font-semibold rounded-full px-3 py-1.5', getStatus(payment.status).bg, getStatus(payment.status).text]">
                    <span :class="['w-1.5 h-1.5 rounded-full', getStatus(payment.status).dot]"></span>
                    {{ getStatus(payment.status).label }}
                </span>
                <button v-if="payment.status === 'completed' || payment.status === 'succeeded'"
                    @click="refund"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-red-500/30 bg-red-500/10 text-[13px] font-semibold text-red-400 hover:bg-red-500/20 transition-all">
                    Refund
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Payment Info Card -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Payment Information</h2>
                        <a v-if="payment.receipt_url" :href="payment.receipt_url" target="_blank"
                            class="ml-auto text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">
                            View Receipt →
                        </a>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Amount</dt>
                                <dd class="text-[22px] font-extrabold text-white">{{ formatCurrency(payment.amount) }} <span class="text-[13px] text-gray-500 font-normal">{{ payment.currency }}</span></dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Payment Method</dt>
                                <dd class="text-[14px] font-semibold text-gray-200 capitalize">{{ payment.payment_method_display || payment.payment_method }}</dd>
                            </div>
                            <div v-if="payment.processing_fee">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Processing Fee</dt>
                                <dd class="text-[13px] text-gray-300">{{ formatCurrency(payment.processing_fee) }}</dd>
                            </div>
                            <div v-if="payment.net_amount">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Net Amount</dt>
                                <dd class="text-[13px] font-semibold text-emerald-400">{{ formatCurrency(payment.net_amount) }}</dd>
                            </div>
                            <div v-if="payment.refunded_amount && payment.refunded_amount > 0">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Refunded</dt>
                                <dd class="text-[13px] font-semibold text-blue-400">{{ formatCurrency(payment.refunded_amount) }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Transaction ID</dt>
                                <dd class="text-[13px] font-mono text-gray-300">{{ payment.transaction_id }}</dd>
                            </div>
                            <div v-if="payment.invoice_number">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Invoice Number</dt>
                                <dd class="text-[13px] font-mono text-gray-300">{{ payment.invoice_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Date</dt>
                                <dd class="text-[13px] text-gray-300">{{ formatDate(payment.created_at) }}</dd>
                            </div>
                            <div v-if="payment.processed_at">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Processed At</dt>
                                <dd class="text-[13px] text-gray-300">{{ formatDate(payment.processed_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Last Updated</dt>
                                <dd class="text-[13px] text-gray-300">{{ formatDate(payment.updated_at) }}</dd>
                            </div>
                        </dl>
                        <!-- Failure info -->
                        <div v-if="payment.failure_message" class="mt-5 p-4 rounded-xl bg-red-500/10 border border-red-500/20">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-red-400 mb-1">Failure Reason</p>
                            <p class="text-[13px] text-red-300">{{ payment.failure_message }}</p>
                            <p v-if="payment.failure_code" class="text-[11px] font-mono text-red-400/60 mt-1">Code: {{ payment.failure_code }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card Information -->
                <div v-if="payment.card_last_four || payment.card_brand" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Card Details</h2>
                    </div>
                    <!-- Visual card -->
                    <div class="p-6">
                        <div class="relative w-full max-w-sm mx-auto rounded-2xl p-6 mb-6 overflow-hidden"
                            style="background: linear-gradient(135deg, #1a2f4e 0%, #0d1b2e 60%, #162540 100%); border: 1px solid rgba(255,255,255,0.08);">
                            <div class="absolute top-0 right-0 w-40 h-40 rounded-full opacity-10" style="background: radial-gradient(circle, #f4b840, transparent); transform: translate(30%, -30%);"></div>
                            <div class="flex justify-between items-start mb-8">
                                <div class="text-[11px] font-bold uppercase tracking-widest text-gray-500">{{ payment.currency || 'USD' }}</div>
                                <div class="text-[15px] font-bold text-amber-400 tracking-wider">{{ (payment.card_brand || 'CARD').toUpperCase() }}</div>
                            </div>
                            <div class="text-[18px] font-mono font-bold text-white tracking-[0.2em] mb-6">{{ cardMasked(payment.card_last_four) }}</div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="text-[9px] uppercase tracking-widest text-gray-600 mb-0.5">Card Holder</div>
                                    <div class="text-[13px] font-bold text-white uppercase">{{ payment.card_holder_name || payment.client?.name || '—' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[9px] uppercase tracking-widest text-gray-600 mb-0.5">Expires</div>
                                    <div class="text-[13px] font-bold text-white">
                                        {{ payment.card_exp_month ? String(payment.card_exp_month).padStart(2,'0') : '—' }} / {{ payment.card_exp_year ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Brand</dt>
                                <dd class="text-[13px] text-gray-200 capitalize">{{ payment.card_brand || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Last 4 Digits</dt>
                                <dd class="text-[13px] font-mono text-gray-200">{{ payment.card_last_four || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Expiry</dt>
                                <dd class="text-[13px] font-mono text-gray-200">
                                    {{ payment.card_exp_month ? String(payment.card_exp_month).padStart(2,'0') : '—' }} / {{ payment.card_exp_year ?? '—' }}
                                </dd>
                            </div>
                            <div v-if="payment.card_holder_name">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Cardholder Name</dt>
                                <dd class="text-[13px] text-gray-200">{{ payment.card_holder_name }}</dd>
                            </div>
                            <div v-if="payment.stripe_payment_method_id">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Payment Method ID</dt>
                                <dd class="text-[11px] font-mono text-gray-400 break-all">{{ payment.stripe_payment_method_id }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Billing Address -->
                <div v-if="payment.billing_address && Object.keys(payment.billing_address).length" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-teal-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Billing Address</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-if="payment.billing_address.name">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Name</dt>
                                <dd class="text-[13px] text-gray-200">{{ payment.billing_address.name }}</dd>
                            </div>
                            <div v-if="payment.billing_address.email">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Email</dt>
                                <dd class="text-[13px] text-gray-200">{{ payment.billing_address.email }}</dd>
                            </div>
                            <div v-if="payment.billing_address.phone">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Phone</dt>
                                <dd class="text-[13px] text-gray-200">{{ payment.billing_address.phone }}</dd>
                            </div>
                            <template v-if="payment.billing_address.address">
                                <div v-if="payment.billing_address.address.line1">
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Address Line 1</dt>
                                    <dd class="text-[13px] text-gray-200">{{ payment.billing_address.address.line1 }}</dd>
                                </div>
                                <div v-if="payment.billing_address.address.line2">
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Address Line 2</dt>
                                    <dd class="text-[13px] text-gray-200">{{ payment.billing_address.address.line2 }}</dd>
                                </div>
                                <div v-if="payment.billing_address.address.city">
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">City</dt>
                                    <dd class="text-[13px] text-gray-200">{{ payment.billing_address.address.city }}</dd>
                                </div>
                                <div v-if="payment.billing_address.address.state">
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">State</dt>
                                    <dd class="text-[13px] text-gray-200">{{ payment.billing_address.address.state }}</dd>
                                </div>
                                <div v-if="payment.billing_address.address.postal_code">
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">ZIP / Postal Code</dt>
                                    <dd class="text-[13px] font-mono text-gray-200">{{ payment.billing_address.address.postal_code }}</dd>
                                </div>
                                <div v-if="payment.billing_address.address.country">
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Country</dt>
                                    <dd class="text-[13px] text-gray-200">{{ payment.billing_address.address.country }}</dd>
                                </div>
                            </template>
                        </dl>
                    </div>
                </div>

                <!-- Stripe IDs -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-violet-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Gateway / Stripe IDs</h2>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-3">
                            <div v-if="payment.stripe_payment_intent_id">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Payment Intent ID</dt>
                                <dd class="text-[12px] font-mono text-gray-400 break-all">{{ payment.stripe_payment_intent_id }}</dd>
                            </div>
                            <div v-if="payment.stripe_charge_id">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Charge ID</dt>
                                <dd class="text-[12px] font-mono text-gray-400 break-all">{{ payment.stripe_charge_id }}</dd>
                            </div>
                            <div v-if="payment.stripe_customer_id">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Customer ID</dt>
                                <dd class="text-[12px] font-mono text-gray-400 break-all">{{ payment.stripe_customer_id }}</dd>
                            </div>
                            <div v-if="payment.stripe_payment_method_id">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Payment Method ID</dt>
                                <dd class="text-[12px] font-mono text-gray-400 break-all">{{ payment.stripe_payment_method_id }}</dd>
                            </div>
                            <div v-if="payment.payment_id">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Internal Payment ID</dt>
                                <dd class="text-[12px] font-mono text-gray-400">{{ payment.payment_id }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Order Info Card -->
                <div v-if="payment.order" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Order Information</h2>
                        <Link :href="route('admin.orders.show', payment.order.id)"
                            class="ml-auto text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">
                            View Order →
                        </Link>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Order Number</dt>
                                <dd class="text-[13px] font-mono text-gray-200">{{ payment.order.order_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Service Type</dt>
                                <dd class="text-[13px] text-gray-200 capitalize">{{ payment.order.service_type }}</dd>
                            </div>
                            <div v-if="payment.order.entity_name">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Entity Name</dt>
                                <dd class="text-[13px] text-gray-200">{{ payment.order.entity_name }}</dd>
                            </div>
                            <div v-if="payment.order.state">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">State</dt>
                                <dd class="text-[13px] text-gray-200">{{ payment.order.state }}</dd>
                            </div>
                            <div v-if="payment.order.package_type">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Package</dt>
                                <dd class="text-[13px] text-gray-200 capitalize">{{ payment.order.package_type }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <!-- Client Info -->
                <div v-if="payment.client" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Client</h2>
                        <Link :href="route('admin.users.show', payment.client.id)"
                            class="ml-auto text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">
                            View →
                        </Link>
                    </div>
                    <div class="p-6 space-y-5">
                        <!-- Avatar -->
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-full overflow-hidden flex-shrink-0">
                                <img
                                    v-if="payment.client.profile_picture_url"
                                    :src="payment.client.profile_picture_url"
                                    :alt="payment.client.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white font-bold text-[16px]">
                                    {{ (payment.client.name||'U').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase() }}
                                </div>
                            </div>
                            <div>
                                <p class="text-[15px] font-bold text-white">{{ payment.client.name }}</p>
                                <p class="text-[12px] text-gray-500">{{ payment.client.email }}</p>
                            </div>
                        </div>
                        <!-- Personal Info -->
                        <dl class="space-y-3">
                            <div v-if="payment.client.first_name || payment.client.last_name">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Full Name</dt>
                                <dd class="text-[13px] text-gray-300">{{ [payment.client.first_name, payment.client.last_name].filter(Boolean).join(' ') }}</dd>
                            </div>
                            <div v-if="payment.client.phone">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Phone</dt>
                                <dd class="text-[13px] text-gray-300">{{ payment.client.phone }}</dd>
                            </div>
                            <div v-if="payment.client.company_name">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Company</dt>
                                <dd class="text-[13px] text-gray-300">{{ payment.client.company_name }}</dd>
                            </div>
                            <div v-if="payment.client.address_line_1">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Address</dt>
                                <dd class="text-[13px] text-gray-300">
                                    {{ payment.client.address_line_1 }}<br v-if="payment.client.address_line_2"/>
                                    <span v-if="payment.client.address_line_2">{{ payment.client.address_line_2 }}<br/></span>
                                    {{ [payment.client.city, payment.client.state, payment.client.zip_code].filter(Boolean).join(', ') }}
                                </dd>
                            </div>
                            <div v-if="payment.client.country">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Country</dt>
                                <dd class="text-[13px] text-gray-300">{{ payment.client.country }}</dd>
                            </div>
                            <div v-if="payment.client.citizenship">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Citizenship</dt>
                                <dd class="text-[13px] text-gray-300">{{ payment.client.citizenship }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Client ID</dt>
                                <dd class="text-[12px] font-mono text-gray-500">#{{ payment.client.id }}</dd>
                            </div>
                            <div v-if="payment.client.registered_at">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Registered</dt>
                                <dd class="text-[12px] text-gray-500">{{ formatDate(payment.client.registered_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                    <h3 class="text-[12px] font-bold uppercase tracking-widest text-gray-600 mb-4">Payment Status</h3>
                    <div :class="['flex items-center gap-3 px-4 py-3 rounded-xl', getStatus(payment.status).bg]">
                        <span :class="['w-2.5 h-2.5 rounded-full flex-shrink-0', getStatus(payment.status).dot]"></span>
                        <span :class="['text-[14px] font-bold capitalize', getStatus(payment.status).text]">
                            {{ getStatus(payment.status).label }}
                        </span>
                    </div>
                </div>

            </div>

        </div>

    </AdminLayout>
</template>
