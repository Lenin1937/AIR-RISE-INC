<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const { __ } = useTranslations();

defineProps({
    notifications: Object,
    unreadCount: Number,
    filters: Object,
    notificationTypes: Array,
    stats: Object,
    preferences: Object,
});

const formatDateTime = (d) => d ? new Date(d).toLocaleString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '';

const getNotificationIcon = (type) => {
    if (type === 'order_update') return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
    if (type === 'payment') return 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z';
    if (type === 'document') return 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z';
    if (type === 'message') return 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z';
    return 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
};

const getNotificationColor = (type) => {
    if (type === 'order_update') return { bg:'bg-amber-400/10', border:'border-amber-400/20', icon:'text-amber-400' };
    if (type === 'payment') return { bg:'bg-green-400/10', border:'border-green-400/20', icon:'text-green-400' };
    if (type === 'document') return { bg:'bg-blue-400/10', border:'border-blue-400/20', icon:'text-blue-400' };
    if (type === 'message') return { bg:'bg-purple-400/10', border:'border-purple-400/20', icon:'text-purple-400' };
    return { bg:'bg-gray-400/10', border:'border-gray-400/20', icon:'text-gray-400' };
};

const markAsRead = (id) => router.patch(route('notifications.mark-read', id));
const markAllAsRead = () => router.patch(route('notifications.mark-all-read'));
</script>

<template>
    <Head :title="__('Notifications')" />
    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-[22px] font-bold text-white tracking-tight">{{ __('Notifications') }}</h1>
                    <span v-if="unreadCount > 0" class="inline-flex items-center justify-center h-5 px-2 rounded-full bg-amber-400 text-[10px] font-bold text-[#07101e]">
                        {{ unreadCount }}
                    </span>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-300 hover:border-amber-400/30 hover:text-white transition"
                >
                    <svg style="width:13px;height:13px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Mark all as read') }}
                </button>
            </div>

            <!-- ── KPI Cards ── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(96,165,250,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-blue-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.total ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">Total</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(244,184,64,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-400/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-amber-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-amber-400/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.unread ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">Unread</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(52,211,153,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-emerald-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-emerald-400/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m8.66-9H21M3 12H2.34M18.36 5.64l-.7.7M6.34 17.66l-.7.7M18.36 18.36l-.7-.7M6.34 6.34l-.7-.7"/><circle cx="12" cy="12" r="4" stroke-width="1.8"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.today ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">Today</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(192,132,252,.12)">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400/[0.08] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-purple-400/70 to-transparent"/>
                    <div class="w-10 h-10 rounded-xl bg-purple-400/15 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-[22px] font-extrabold text-white leading-none">{{ stats?.this_week ?? 0 }}</div>
                    <div class="mt-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-600">This Week</div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!notifications?.data?.length" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-white/[0.03] flex items-center justify-center mx-auto mb-4">
                    <svg style="width:24px;height:24px" class="text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-[14px] font-bold text-white mb-1">{{ __('No notifications') }}</p>
                <p class="text-[12px] text-gray-500">{{ __('You have no new notifications at the moment.') }}</p>
            </div>

            <!-- Notifications list -->
            <div v-else class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                <div class="divide-y divide-white/[0.04]">
                    <div
                        v-for="notif in notifications.data"
                        :key="notif.id"
                        :class="['flex items-start gap-4 px-5 py-4 transition border-l-2', notif.read_at ? 'border-transparent' : 'border-amber-400/50 bg-amber-400/[0.03]']"
                    >
                        <!-- Icon -->
                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 border', getNotificationColor(notif.type).bg, getNotificationColor(notif.type).border]">
                            <svg style="width:15px;height:15px" :class="getNotificationColor(notif.type).icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notif.type)"/>
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-[13px] font-semibold text-white">{{ notif.title ?? notif.data?.title }}</p>
                                    <span v-if="!notif.read_at" class="text-[10px] font-bold rounded-full px-1.5 py-0.5 bg-amber-400 text-[#07101e]">New</span>
                                </div>
                                <span class="text-[11px] text-gray-500 whitespace-nowrap flex-shrink-0">{{ formatDateTime(notif.created_at) }}</span>
                            </div>
                            <p class="text-[12px] text-gray-400 mt-0.5 leading-relaxed">{{ notif.message ?? notif.data?.message }}</p>
                        </div>

                        <!-- Mark read -->
                        <button
                            v-if="!notif.read_at"
                            @click="markAsRead(notif.id)"
                            class="flex-shrink-0 w-7 h-7 rounded-lg bg-white/[0.04] flex items-center justify-center text-gray-500 hover:bg-amber-400/20 hover:text-amber-400 transition"
                            title="Mark as read"
                        >
                            <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="notifications.prev_page_url || notifications.next_page_url" class="flex items-center justify-between px-5 py-4 border-t border-white/[0.06]">
                    <div class="text-[12px] text-gray-500">
                        <span v-if="notifications.from">Showing {{ notifications.from }}–{{ notifications.to }} of {{ notifications.total }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a v-if="notifications.prev_page_url" :href="notifications.prev_page_url"
                            class="inline-flex items-center gap-1 h-8 px-3 rounded-lg border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-300 hover:border-amber-400/30 hover:text-white transition">
                            ← Prev
                        </a>
                        <a v-if="notifications.next_page_url" :href="notifications.next_page_url"
                            class="inline-flex items-center gap-1 h-8 px-3 rounded-lg border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-300 hover:border-amber-400/30 hover:text-white transition">
                            Next →
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
