<script setup>
import ChatWidget from '@/Components/ChatWidget.vue';
import ClientOnly from '@/Components/ClientOnly.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { __ } = useTranslations();
const sidebarOpen      = ref(false);
const sidebarCollapsed = ref(false);
const page             = usePage();
const currentLocale    = computed(() => page.props.locale || 'en');
const user             = computed(() => page.props.auth?.user);

const toggleSidebar = () => { sidebarCollapsed.value = !sidebarCollapsed.value; };
const logout        = () => router.post(route('logout'));

const navGroups = [
    {
        label: 'Overview',
        items: [
            { name: 'Dashboard',       href: () => route('dashboard'),             active: () => route().current('dashboard'),           icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        ],
    },
    {
        label: 'Services',
        items: [
            { name: 'My Orders',       href: () => route('orders.index'),          active: () => route().current('orders.*'),            icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { name: 'My Documents',    href: () => route('documents.index'),       active: () => route().current('documents.*'),         icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
        ],
    },
    {
        label: 'Communication',
        items: [
            { name: 'Messages',        href: () => route('messages.index'),        active: () => route().current('messages.*'),          icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', badge: () => page.props.auth?.user?.unread_messages_count || 0 },
            { name: 'Notifications',   href: () => route('notifications.index'),   active: () => route().current('notifications.*'),     icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
        ],
    },
    {
        label: 'Billing',
        items: [
            { name: 'Payment Methods', href: () => route('payment-methods.index'), active: () => route().current('payment-methods.*'),   icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
        ],
    },
    {
        label: 'Support',
        items: [
            { name: 'Knowledge Base',  href: () => route('knowledge-base.index'),  active: () => route().current('knowledge-base.*'),    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
        ],
    },
];

const currentPageName = computed(() => {
    for (const group of navGroups) {
        for (const item of group.items) {
            if (item.active()) return item.name;
        }
    }
    return 'Dashboard';
});
</script>

<template>
    <div class="min-h-screen flex bg-[#07101e]">

        <!-- Mobile overlay -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"/>

        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-40 flex flex-col bg-[#0a1628] border-r border-white/[0.06] transition-all duration-300 ease-in-out lg:translate-x-0',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarCollapsed ? 'w-[70px]' : 'w-60'
        ]">

            <!-- Logo -->
            <div class="flex items-center h-16 shrink-0 border-b border-white/[0.06]" :class="sidebarCollapsed ? 'px-[22px]' : 'px-5'">
                <Link :href="route('dashboard')" class="flex items-center gap-3 min-w-0 flex-1">
                    <img src="/logo.png" alt="CORPIUS" class="w-8 h-8 flex-shrink-0 rounded-lg object-contain">
                    <div v-if="!sidebarCollapsed" class="min-w-0 leading-none">
                        <p class="text-[13px] font-bold text-white tracking-tight">CORPIUS</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Client Portal</p>
                    </div>
                </Link>
                <button @click="toggleSidebar" class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-md text-gray-600 hover:text-white hover:bg-white/[0.08] transition">
                    <svg class="w-3 h-3 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Create Order -->
            <div :class="['py-3 border-b border-white/[0.06]', sidebarCollapsed ? 'px-2' : 'px-3']">
                <Link :href="route('orders.create')"
                    :title="sidebarCollapsed ? 'Create New Order' : undefined"
                    :class="[
                        'flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-[#0b1e33] font-bold transition-all hover:from-amber-400 hover:to-amber-300 shadow-lg shadow-amber-500/20',
                        sidebarCollapsed ? 'w-10 h-10 mx-auto' : 'h-9 px-4 text-[12px]'
                    ]">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span v-if="!sidebarCollapsed">Create New Order</span>
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3" :class="sidebarCollapsed ? 'px-2' : 'px-3'">
                <div v-for="(group, gi) in navGroups" :key="group.label">
                    <p v-if="!sidebarCollapsed" :class="['mb-1 px-3 text-[10px] font-bold uppercase tracking-[0.1em] text-gray-600', gi === 0 ? 'mt-1' : 'mt-4']">{{ group.label }}</p>
                    <div v-else :class="['mb-1 mx-1 border-t border-white/[0.05]', gi === 0 ? 'mt-1' : 'mt-3']"></div>
                    <Link
                        v-for="item in group.items" :key="item.name"
                        :href="item.href()"
                        :title="sidebarCollapsed ? item.name : undefined"
                        :class="[
                            'flex items-center rounded-lg font-medium transition-all duration-150 group relative mb-0.5',
                            sidebarCollapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-3 px-3 h-10',
                            item.active()
                                ? 'bg-gradient-to-r from-amber-500/20 to-amber-400/5 text-amber-400'
                                : 'text-gray-500 hover:text-gray-100 hover:bg-white/[0.06]'
                        ]"
                    >
                        <span v-if="item.active() && !sidebarCollapsed" class="absolute left-0 inset-y-2.5 w-[3px] rounded-r-full bg-amber-400"></span>
                        <svg :class="['flex-shrink-0', item.active() ? 'text-amber-400' : 'text-gray-600 group-hover:text-gray-300', sidebarCollapsed ? 'w-[18px] h-[18px]' : 'w-[17px] h-[17px]']"
                            fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
                        </svg>
                        <span v-if="!sidebarCollapsed" class="text-[13px] truncate flex-1">{{ item.name }}</span>
                        <span v-if="!sidebarCollapsed && item.badge && item.badge() > 0"
                            class="ml-auto text-[10px] font-bold bg-red-500 text-white rounded-full px-1.5 py-0.5 leading-none">
                            {{ item.badge() }}
                        </span>
                    </Link>
                </div>
            </nav>

            <!-- Footer -->
            <div class="shrink-0 border-t border-white/[0.06] py-3" :class="sidebarCollapsed ? 'px-2' : 'px-3'">
                <div :class="['mb-1', sidebarCollapsed ? 'flex justify-center' : '']">
                    <LanguageSwitcher :locale="currentLocale" :compact="sidebarCollapsed" buttonClass="text-gray-500 hover:text-white" :class="sidebarCollapsed ? '' : 'w-full'"/>
                </div>
                <Link :href="route('profile.edit')" :title="sidebarCollapsed ? user?.name : undefined"
                    :class="['flex items-center rounded-lg text-[13px] font-medium text-gray-500 hover:text-white hover:bg-white/[0.06] transition group mb-0.5',
                             sidebarCollapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-2.5 px-3 h-11']">
                    <div class="rounded-full overflow-hidden ring-1 ring-amber-400/30 flex-shrink-0 bg-gradient-to-br from-amber-400 to-amber-600 w-7 h-7">
                        <img v-if="user?.profile_picture_url" :src="user.profile_picture_url" class="w-full h-full object-cover"/>
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-[#0b1e33] font-bold text-xs">{{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
                        </div>
                    </div>
                    <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
                        <p class="text-[12px] font-semibold text-gray-200 truncate leading-none">{{ user?.name || 'Client' }}</p>
                        <p class="text-[10px] text-gray-600 truncate mt-0.5">{{ user?.email }}</p>
                    </div>
                </Link>
                <button @click="logout" :title="sidebarCollapsed ? 'Sign Out' : undefined"
                    :class="['w-full flex items-center rounded-lg text-[13px] font-medium text-gray-600 hover:text-red-400 hover:bg-red-500/10 transition group',
                             sidebarCollapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-2.5 px-3 h-10']">
                    <svg :class="['flex-shrink-0 group-hover:text-red-400', sidebarCollapsed ? 'w-[18px] h-[18px]' : 'w-[17px] h-[17px]']"
                        fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span v-if="!sidebarCollapsed">Sign Out</span>
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div :class="['flex-1 flex flex-col min-h-screen transition-all duration-300', sidebarCollapsed ? 'lg:ml-[70px]' : 'lg:ml-60']">

            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex items-center h-16 shrink-0 px-4 sm:px-6 bg-[#07101e]/95 backdrop-blur-lg border-b border-white/[0.06]">
                <button class="lg:hidden mr-4 p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/[0.08] transition" @click="sidebarOpen = true">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
                <div class="flex flex-col justify-center">
                    <p class="text-[13px] font-semibold text-white leading-none">{{ currentPageName }}</p>
                    <p class="text-[11px] text-gray-600 mt-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-gray-400 transition-colors">Dashboard</Link>
                        <span v-if="currentPageName !== 'Dashboard'"> / {{ currentPageName }}</span>
                    </p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <Link :href="route('notifications.index')" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-white hover:bg-white/[0.08] transition">
                        <svg class="w-[17px] h-[17px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </Link>
                    <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-amber-400/20 bg-gradient-to-br from-amber-400 to-amber-600 flex-shrink-0">
                        <img v-if="user?.profile_picture_url" :src="user.profile_picture_url" class="w-full h-full object-cover"/>
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-[#0b1e33] font-bold text-sm">{{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-[12px] font-semibold text-white leading-none">{{ user?.name }}</p>
                        <p class="text-[10px] text-gray-600 mt-0.5 leading-none">Client</p>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <!-- AI Chat Widget -->
        <ClientOnly><ChatWidget /></ClientOnly>
    </div>
</template>
