<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { __ } = useTranslations();

const props = defineProps({
    orders: Object,
    stats: Object,
});

const page = usePage();

const selectedOrder = ref(props.orders?.data?.[0] ?? null);

const registrationStatus = computed(() => page.props?.auth?.user?.registration_status ?? null);
const showApprovalBanner = computed(() => ['pending_approval', 'rejected'].includes(registrationStatus.value));
const approvalBannerTitle = computed(() =>
    registrationStatus.value === 'rejected' ? 'Profile Review Required' : 'Profile Pending Admin Approval'
);
const approvalBannerText = computed(() =>
    registrationStatus.value === 'rejected'
        ? 'Your profile requires changes before access is approved. Please review your profile status and contact support if needed.'
        : 'Your profile is under admin review. Full client access will be enabled after approval.'
);

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : '—';
const formatDateTime = (d) => d ? new Date(d).toLocaleString('en-US', { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }) : '';
const formatCurrency = (n) => new Intl.NumberFormat('en-US', { style:'currency', currency:'USD' }).format(n || 0);

const getStatusColor = (s) => {
    if (s === 'completed') return 'bg-green-400/10 border-green-400/20 text-green-400';
    if (['cancelled','rejected'].includes(s)) return 'bg-red-400/10 border-red-400/20 text-red-400';
    if (s === 'approved') return 'bg-amber-400/10 border-amber-400/20 text-amber-400';
    return 'bg-blue-400/10 border-blue-400/20 text-blue-400';
};

const formatStatus = (s) => s ? s.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase()) : '';
const formatServiceType = (s) => s ? s.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase()) : '';

const getTimelineStages = (order) => {
    const stages = [
        { key:'pending',      label:'Order Received' },
        { key:'in_progress',  label:'In Progress' },
        { key:'under_review', label:'Under Review' },
        { key:'approved',     label:'Approved' },
        { key:'filed',        label:'Filed with State' },
        { key:'completed',    label:'Completed' },
    ];
    const orderMap = { draft:0, pending:0, in_progress:1, under_review:2, approved:3, filed:4, completed:5 };
    const cur = orderMap[order?.status] ?? 0;
    return stages.map((s, i) => ({
        ...s,
        state: i < cur ? 'completed' : i === cur ? 'in_progress' : 'pending',
    }));
};

const progress = computed(() => {
    if (!selectedOrder.value) return 0;
    const map = { draft:5, pending:10, in_progress:40, under_review:60, approved:80, filed:90, completed:100, cancelled:0, refunded:0 };
    return map[selectedOrder.value.status] ?? 10;
});
</script>

<template>
    <Head :title="__('client.my_orders')" />
    <AuthenticatedLayout>
        <div class="space-y-7">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-[22px] font-bold text-white tracking-tight">{{ __('client.my_orders') }}</h1>
                    <p class="mt-0.5 text-[13px] text-gray-400">{{ __('client.track_orders_progress') }}</p>
                </div>
                <Link :href="route('orders.create')" class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20">
                    <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('New Order') }}
                </Link>
            </div>

            <div v-if="showApprovalBanner" class="rounded-2xl border p-4"
                 :class="registrationStatus === 'rejected' ? 'border-red-400/25 bg-red-400/[0.05]' : 'border-amber-400/25 bg-amber-400/[0.05]'">
                <p class="text-[13px] font-bold"
                   :class="registrationStatus === 'rejected' ? 'text-red-400' : 'text-amber-400'">
                    {{ approvalBannerTitle }}
                </p>
                <p class="mt-1 text-[12px] text-gray-300">{{ approvalBannerText }}</p>
                <Link :href="route('onboarding.review')" class="mt-2 inline-flex text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition">
                    Open review status →
                </Link>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(96,165,250,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-blue-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.total_orders ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">Total Orders</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(244,184,64,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-400/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-amber-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-amber-400/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.pending ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">Pending</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(167,139,250,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-400/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-violet-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-violet-400/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.in_progress ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">In Progress</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(52,211,153,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-emerald-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-emerald-400/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.completed ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">Completed</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 col-span-2 lg:col-span-1" style="box-shadow:0 0 28px 0 rgba(244,184,64,.15)">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/[0.10] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-amber-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ formatCurrency(stats?.total_revenue) }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">Total Revenue</div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!orders?.data?.length" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-amber-400/10 flex items-center justify-center mx-auto mb-5">
                    <svg style="width:28px;height:28px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-[17px] font-bold text-white mb-2">{{ __('client.ready_to_start') }}</h3>
                <p class="text-[13px] text-gray-400 mb-8 max-w-md mx-auto">{{ __('client.no_orders_placed') }}</p>
                <!-- 3-step guide -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 max-w-xl mx-auto">
                    <div v-for="(step, i) in [{ icon:'🎯', title: __('client.choose_service'), desc: __('client.select_service_desc') }, { icon:'📋', title: __('client.provide_details'), desc: __('client.fill_info_desc') }, { icon:'🚀', title: __('client.we_handle_it'), desc: __('client.we_take_care_desc') }]" :key="i" class="rounded-xl border border-white/[0.05] bg-white/[0.02] p-4 text-center">
                        <div class="text-2xl mb-2">{{ step.icon }}</div>
                        <div class="text-[12px] font-bold text-white mb-1">{{ step.title }}</div>
                        <div class="text-[11px] text-gray-500">{{ step.desc }}</div>
                    </div>
                </div>
                <Link :href="route('orders.create')" class="inline-flex items-center gap-2 h-10 px-6 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20">
                    {{ __('client.create_first_order') }}
                </Link>
            </div>

            <!-- Split layout with orders -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Orders list -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-white/[0.06]">
                        <h2 class="text-[13px] font-bold text-white">{{ __('client.all_orders') }}</h2>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-white/[0.04]">
                        <button
                            v-for="order in orders.data"
                            :key="order.id"
                            @click="selectedOrder = order"
                            :class="['w-full text-left px-5 py-4 transition hover:bg-white/[0.03]', selectedOrder?.id === order.id ? 'bg-amber-400/[0.07] border-l-2 border-amber-400' : 'border-l-2 border-transparent']"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[13px] font-semibold text-white truncate">{{ formatServiceType(order.service_type) }}</span>
                                <span :class="['text-[10px] font-bold rounded-full px-2 py-0.5 border', getStatusColor(order.status)]">
                                    {{ formatStatus(order.status) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-gray-500">{{ order.order_number }}</span>
                                <span class="text-gray-700">·</span>
                                <span class="text-[11px] text-gray-500">{{ formatDate(order.created_at) }}</span>
                            </div>
                        </button>
                    </div>
                    <!-- Pagination -->
                    <div v-if="orders.prev_page_url || orders.next_page_url" class="flex items-center justify-between px-5 py-3 border-t border-white/[0.06]">
                        <Link v-if="orders.prev_page_url" :href="orders.prev_page_url" class="text-[11px] text-amber-400 hover:text-amber-300 font-semibold transition">← Prev</Link>
                        <span v-else/>
                        <Link v-if="orders.next_page_url" :href="orders.next_page_url" class="text-[11px] text-amber-400 hover:text-amber-300 font-semibold transition">Next →</Link>
                    </div>
                </div>

                <!-- Order detail -->
                <div class="lg:col-span-2 rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden flex flex-col">
                    <div v-if="selectedOrder">
                        <!-- Detail header -->
                        <div class="px-6 py-5 border-b border-white/[0.06]">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-[17px] font-bold text-white mb-0.5">{{ selectedOrder.business_name || formatServiceType(selectedOrder.service_type) }}</h2>
                                    <p class="text-[12px] text-gray-500">{{ selectedOrder.order_number }} · {{ formatDate(selectedOrder.created_at) }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-[20px] font-bold text-amber-400">{{ formatCurrency(selectedOrder.total_amount) }}</div>
                                    <span :class="['inline-flex text-[11px] font-bold rounded-full px-2.5 py-0.5 border mt-1', getStatusColor(selectedOrder.status)]">
                                        {{ formatStatus(selectedOrder.status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Progress bar -->
                            <div class="mt-4">
                                <div class="flex justify-between text-[11px] text-gray-500 mb-1.5">
                                    <span>Order Progress</span>
                                    <span>{{ progress }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-white/[0.06]">
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500 transition-all duration-700" :style="{width: progress+'%'}"/>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="p-6">
                            <h3 class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-5">Order Timeline</h3>
                            <div class="relative">
                                <div class="absolute left-[14px] top-0 bottom-0 w-px bg-white/[0.06]"/>
                                <div class="space-y-4">
                                    <div v-for="(stage, i) in getTimelineStages(selectedOrder)" :key="i" class="flex items-start gap-4 relative">
                                        <div :class="['relative z-10 w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 border text-[10px] font-bold transition',
                                            stage.state === 'completed' ? 'bg-green-400/20 border-green-400/50 text-green-400' :
                                            stage.state === 'in_progress' ? 'bg-amber-400/20 border-amber-400/60 text-amber-400 shadow-lg shadow-amber-400/20' :
                                            'bg-white/[0.03] border-white/[0.08] text-gray-600']">
                                            <svg v-if="stage.state === 'completed'" style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <div v-else-if="stage.state === 'in_progress'" class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"/>
                                            <div v-else class="w-1.5 h-1.5 rounded-full bg-gray-700"/>
                                        </div>
                                        <div class="pt-0.5 pb-3">
                                            <p :class="['text-[13px] font-semibold',
                                                stage.state === 'completed' ? 'text-green-400' :
                                                stage.state === 'in_progress' ? 'text-amber-400' :
                                                'text-gray-500']">
                                                {{ stage.label }}
                                            </p>
                                            <p v-if="stage.state === 'in_progress'" class="text-[11px] text-gray-500 mt-0.5">Currently processing…</p>
                                            <p v-else-if="stage.state === 'completed'" class="text-[11px] text-green-400/70 mt-0.5">Completed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex items-center justify-center h-64 text-gray-500 text-[13px]">Select an order to view details</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
