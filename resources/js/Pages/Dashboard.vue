<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const { __ } = useTranslations();

defineProps({
    stats: Object,
    recent_orders: Array,
    recent_documents: Array,
    recent_messages: Array,
});

const fmt = (d) => new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });

const statusInfo = (s) => {
    if (s === 'completed') return { dot:'bg-green-400', text:'text-green-400', bg:'bg-green-400/10 border-green-400/20' };
    if (['cancelled','rejected'].includes(s)) return { dot:'bg-red-400', text:'text-red-400', bg:'bg-red-400/10 border-red-400/20' };
    if (s === 'approved') return { dot:'bg-amber-400', text:'text-amber-400', bg:'bg-amber-400/10 border-amber-400/20' };
    return { dot:'bg-blue-400', text:'text-blue-400', bg:'bg-blue-400/10 border-blue-400/20' };
};
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="space-y-7">

            <!-- Welcome -->
            <div>
                <h1 class="text-[22px] font-bold text-white tracking-tight">{{ __('client.welcome_back') }}</h1>
                <p class="mt-0.5 text-[13px] text-gray-400">{{ __('client.dashboard_subtitle') }}</p>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(251,191,36,.10)">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-400/[0.07] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-amber-400/60 to-transparent"/>
                    <div class="w-9 h-9 rounded-xl bg-amber-400/20 flex items-center justify-center mb-3">
                        <svg style="width:18px;height:18px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-white">{{ stats?.total_orders ?? 0 }}</div>
                    <div class="mt-1 text-[12px] text-gray-400 font-medium">{{ __('client.total_orders') }}</div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(59,130,246,.10)">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/[0.07] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-blue-500/60 to-transparent"/>
                    <div class="w-9 h-9 rounded-xl bg-blue-500/20 flex items-center justify-center mb-3">
                        <svg style="width:18px;height:18px" class="text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-white">{{ stats?.active_orders ?? 0 }}</div>
                    <div class="mt-1 text-[12px] text-gray-400 font-medium">{{ __('client.in_progress') }}</div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(34,197,94,.10)">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/[0.07] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-green-500/60 to-transparent"/>
                    <div class="w-9 h-9 rounded-xl bg-green-500/20 flex items-center justify-center mb-3">
                        <svg style="width:18px;height:18px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-white">{{ stats?.completed_orders ?? 0 }}</div>
                    <div class="mt-1 text-[12px] text-gray-400 font-medium">{{ __('client.completed') }}</div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(168,85,247,.10)">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/[0.07] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-purple-500/60 to-transparent"/>
                    <div class="w-9 h-9 rounded-xl bg-purple-500/20 flex items-center justify-center mb-3">
                        <svg style="width:18px;height:18px" class="text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-white">{{ stats?.unread_messages ?? 0 }}</div>
                    <div class="mt-1 text-[12px] text-gray-400 font-medium">{{ __('client.unread_messages') }}</div>
                </div>
            </div>

            <!-- Bottom grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Recent Orders -->
                <div class="lg:col-span-2 rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.06]">
                        <h2 class="text-[14px] font-bold text-white">{{ __('client.recent_orders') }}</h2>
                        <Link :href="route('orders.index')" class="text-[12px] text-amber-400 hover:text-amber-300 font-semibold transition">
                            {{ __('client.view_all') }} →
                        </Link>
                    </div>

                    <div class="p-5">
                        <div v-if="recent_orders && recent_orders.length" class="space-y-3">
                            <div
                                v-for="order in recent_orders"
                                :key="order.id"
                                class="flex items-center justify-between p-3 sm:p-3.5 rounded-xl border border-white/[0.05] bg-white/[0.02] hover:border-amber-400/20 transition gap-3"
                            >
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-9 h-9 rounded-xl bg-amber-400/20 flex items-center justify-center flex-shrink-0">
                                        <svg style="width:16px;height:16px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-semibold text-gray-200 truncate">{{ order.service_type || order.business_name }}</div>
                                        <div class="text-[11px] text-gray-500 truncate">{{ order.order_number }} · {{ fmt(order.created_at) }}</div>
                                    </div>
                                </div>
                                <span :class="['inline-flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-[11px] font-semibold rounded-full px-2 sm:px-2.5 py-1 border flex-shrink-0', statusInfo(order.status).bg, statusInfo(order.status).text]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', statusInfo(order.status).dot]"/>
                                    {{ order.status.replace('_',' ').toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="w-12 h-12 rounded-xl bg-white/[0.03] flex items-center justify-center mb-3">
                                <svg style="width:20px;height:20px" class="text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-[13px] font-semibold text-white mb-1">{{ __('client.no_recent_orders') }}</p>
                            <p class="text-[12px] text-gray-500 mb-4">{{ __('client.start_journey') }}</p>
                            <Link :href="route('orders.create')" class="inline-flex items-center gap-2 h-8 px-4 rounded-xl bg-amber-400 text-[12px] font-semibold text-[#07101e] hover:bg-amber-300 transition">
                                {{ __('client.create_first_order') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- CTA Card -->
                <div class="rounded-2xl bg-gradient-to-br from-amber-400 to-amber-500 p-6 flex flex-col justify-between" style="box-shadow:0 8px 32px 0 rgba(251,191,36,.25)">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-[#07101e]/15 flex items-center justify-center mb-4">
                            <svg style="width:22px;height:22px" class="text-[#07101e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-[17px] font-bold text-[#07101e] mb-2">{{ __('client.need_form_entity') }}</h3>
                        <p class="text-[13px] text-[#07101e]/75 leading-relaxed">{{ __('client.start_new_process') }}</p>
                    </div>
                    <Link :href="route('orders.create')" class="mt-6 inline-flex items-center justify-center gap-2 h-10 px-5 rounded-xl bg-[#07101e] text-[13px] font-bold text-white hover:bg-[#0f2643] transition shadow-lg">
                        {{ __('client.create_new_order') }}
                        <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
