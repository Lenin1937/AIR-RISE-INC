<script setup>
import ChatWidget from '@/Components/ChatWidget.vue';
import ClientOnly from '@/Components/ClientOnly.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const sidebarOpen    = ref(false);
const sidebarCollapsed = ref(false);

const page = usePage();
const currentLocale  = computed(() => page.props.locale || 'en');
const user           = computed(() => page.props.auth?.user);

const logout         = () => router.post(route('logout'));
const toggleSidebar  = () => { sidebarCollapsed.value = !sidebarCollapsed.value; };

const navItems = [
    { group: 'Overview',    name: 'Dashboard',         href: route('admin.dashboard'),    active: route().current('admin.dashboard'),   icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { group: 'Management',  name: 'Users',             href: route('admin.users.index'),    active: route().current('admin.users.*'),     icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
    { group: 'Management',  name: 'Orders',            href: route('admin.orders.index'),   active: route().current('admin.orders.*'),    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { group: 'Management',  name: 'Payments',          href: route('admin.payments.index'), active: route().current('admin.payments.*'), icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    { group: 'Management',  name: 'Documents',         href: route('admin.documents.index'), active: route().current('admin.documents.*'), icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
    { group: 'Content',     name: 'Articles',          href: route('admin.articles.index'), active: route().current('admin.articles.*'), icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z' },
    { group: 'Content',     name: 'Blog',              href: route('admin.blog.index'),    active: route().current('admin.blog.*'),     icon: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' },
    { group: 'Content',     name: 'AI Chat Logs',      href: route('admin.chats.index'),    active: route().current('admin.chats.*'),     icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' },
    { group: 'System',      name: 'Roles',             href: route('admin.roles.index'),  active: route().current('admin.roles.*'),     icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { group: 'System',      name: 'SEO & Analytics',   href: route('admin.analytics.index'), active: route().current('admin.analytics.*'), icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { group: 'System',      name: 'Webmail',           href: 'https://mail.revold.us/mail', active: false, external: true, icon: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4' },
    { group: 'Email Marketing', name: 'EM Overview',   href: route('admin.email.index'),           active: route().current('admin.email.index'),           icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { group: 'Email Marketing', name: 'Contacts',      href: route('admin.email.contacts.index'),  active: route().current('admin.email.contacts.*'),      icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { group: 'Email Marketing', name: 'Templates',     href: route('admin.email.templates.index'), active: route().current('admin.email.templates.*'),     icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z' },
    { group: 'Email Marketing', name: 'Campaigns',     href: route('admin.email.campaigns.index'), active: route().current('admin.email.campaigns.*'),     icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z' },
    { group: 'Email Marketing', name: 'Automations',   href: route('admin.email.automations.index'), active: route().current('admin.email.automations.*'), icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { group: 'Email Marketing', name: 'Segments',      href: route('admin.email.segments.index'),  active: route().current('admin.email.segments.*'),      icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
];

const navGroups = computed(() => {
    const groups = {};
    navItems.forEach(item => {
        if (!groups[item.group]) groups[item.group] = [];
        groups[item.group].push(item);
    });
    return Object.entries(groups).map(([label, items]) => ({ label, items }));
});

const currentPageName = computed(() => navItems.find(i => i.active)?.name ?? 'Admin');
</script>

<template>
    <div class="min-h-screen flex bg-[#07101e]">

        <!-- ── Mobile overlay ── -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"/>

        <!-- ── Sidebar ── -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-40 flex flex-col bg-[#0a1628] border-r border-white/[0.06] transition-all duration-300 ease-in-out',
            'lg:translate-x-0',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarCollapsed ? 'w-[70px]' : 'w-60'
        ]">

            <!-- Logo -->
            <div class="flex items-center h-16 shrink-0 border-b border-white/[0.06]" :class="sidebarCollapsed ? 'px-[22px]' : 'px-5'">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-3 min-w-0 flex-1">
                    <img src="/logo.png" alt="CORPIUS" class="w-8 h-8 flex-shrink-0 rounded-lg object-contain">
                    <div v-if="!sidebarCollapsed" class="min-w-0 leading-none">
                        <p class="text-[13px] font-bold text-white tracking-tight">CORPIUS</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Admin Panel</p>
                    </div>
                </Link>
                <button @click="toggleSidebar" class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-md text-gray-600 hover:text-white hover:bg-white/[0.08] transition">
                    <svg class="w-3 h-3" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3" :class="sidebarCollapsed ? 'px-2' : 'px-3'">
                <template v-for="group in navGroups" :key="group.label">
                    <p v-if="!sidebarCollapsed" class="mt-4 mb-1 px-3 text-[10px] font-bold uppercase tracking-[0.1em] text-gray-600 first:mt-1">{{ group.label }}</p>
                    <div v-else class="mt-3 mb-1 mx-1 border-t border-white/[0.05] first:hidden"></div>
                    <template v-for="item in group.items" :key="item.name">
                    <!-- External link (e.g. Webmail) — opens in new tab -->
                    <a
                        v-if="item.external"
                        :href="item.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        :title="sidebarCollapsed ? item.name : undefined"
                        :class="[
                            'flex items-center rounded-lg font-medium transition-all duration-150 group relative mb-0.5',
                            sidebarCollapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-3 px-3 h-10',
                            'text-gray-500 hover:text-gray-100 hover:bg-white/[0.06]'
                        ]"
                    >
                        <svg :class="['flex-shrink-0 transition-colors text-gray-600 group-hover:text-gray-300', sidebarCollapsed ? 'w-[18px] h-[18px]' : 'w-[17px] h-[17px]']"
                            fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
                        </svg>
                        <span v-if="!sidebarCollapsed" class="text-[13px] truncate">{{ item.name }}</span>
                        <!-- small external arrow indicator -->
                        <svg v-if="!sidebarCollapsed" class="w-3 h-3 ml-auto flex-shrink-0 text-gray-700 group-hover:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    <!-- Internal Inertia link -->
                    <Link
                        v-else
                        :href="item.href"
                        :title="sidebarCollapsed ? item.name : undefined"
                        :class="[
                            'flex items-center rounded-lg font-medium transition-all duration-150 group relative mb-0.5',
                            sidebarCollapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-3 px-3 h-10',
                            item.active
                                ? 'bg-gradient-to-r from-amber-500/20 to-amber-400/5 text-amber-400'
                                : 'text-gray-500 hover:text-gray-100 hover:bg-white/[0.06]'
                        ]"
                    >
                        <span v-if="item.active && !sidebarCollapsed" class="absolute left-0 inset-y-2.5 w-[3px] rounded-r-full bg-amber-400"></span>
                        <svg :class="['flex-shrink-0 transition-colors', item.active ? 'text-amber-400' : 'text-gray-600 group-hover:text-gray-300', sidebarCollapsed ? 'w-[18px] h-[18px]' : 'w-[17px] h-[17px]']"
                            fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
                        </svg>
                        <span v-if="!sidebarCollapsed" class="text-[13px] truncate">{{ item.name }}</span>
                    </Link>
                    </template>
                </template>
            </nav>

            <!-- Footer -->
            <div class="shrink-0 border-t border-white/[0.06] py-3" :class="sidebarCollapsed ? 'px-2' : 'px-3'">
                <!-- Language -->
                <div :class="['mb-1', sidebarCollapsed ? 'flex justify-center' : '']">
                    <LanguageSwitcher :locale="currentLocale" :compact="sidebarCollapsed" buttonClass="text-gray-500 hover:text-white" :class="sidebarCollapsed ? '' : 'w-full'"/>
                </div>
                <!-- Profile -->
                <Link :href="route('admin.profile.edit')" :title="sidebarCollapsed ? user?.name : undefined"
                    :class="['flex items-center rounded-lg text-[13px] font-medium text-gray-500 hover:text-white hover:bg-white/[0.06] transition group mb-0.5',
                             sidebarCollapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-2.5 px-3 h-11']">
                    <div :class="['rounded-full overflow-hidden ring-1 ring-amber-400/30 flex-shrink-0 bg-gradient-to-br from-amber-400 to-amber-600', sidebarCollapsed ? 'w-7 h-7' : 'w-7 h-7']">
                        <img v-if="user?.profile_picture_url" :src="user.profile_picture_url" class="w-full h-full object-cover"/>
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-[#0b1e33] font-bold text-xs">{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
                        </div>
                    </div>
                    <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
                        <p class="text-[12px] font-semibold text-gray-200 truncate leading-none">{{ user?.name || 'Admin' }}</p>
                        <p class="text-[10px] text-gray-600 truncate mt-0.5">{{ user?.email }}</p>
                    </div>
                </Link>
                <!-- Logout -->
                <button @click="logout" :title="sidebarCollapsed ? 'Sign Out' : undefined"
                    :class="['w-full flex items-center rounded-lg text-[13px] font-medium text-gray-600 hover:text-red-400 hover:bg-red-500/10 transition group',
                             sidebarCollapsed ? 'justify-center w-10 h-10 mx-auto' : 'gap-2.5 px-3 h-10']">
                    <svg :class="['flex-shrink-0 transition-colors group-hover:text-red-400', sidebarCollapsed ? 'w-[18px] h-[18px]' : 'w-[17px] h-[17px]']"
                        fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span v-if="!sidebarCollapsed">Sign Out</span>
                </button>
            </div>
        </aside>

        <!-- ── Main content ── -->
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
                        <Link :href="route('admin.dashboard')" class="hover:text-gray-400 transition-colors">Dashboard</Link>
                        <span v-if="currentPageName !== 'Dashboard'"> / {{ currentPageName }}</span>
                    </p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-amber-400/20 bg-gradient-to-br from-amber-400 to-amber-600 flex-shrink-0">
                        <img v-if="user?.profile_picture_url" :src="user.profile_picture_url" class="w-full h-full object-cover"/>
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-[#0b1e33] font-bold text-sm">{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-[12px] font-semibold text-white leading-none">{{ user?.name }}</p>
                        <p class="text-[10px] text-gray-600 mt-0.5 leading-none">Admin</p>
                    </div>
                </div>
            </header>

            <!-- Slot -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <ClientOnly><ChatWidget /></ClientOnly>
    </div>
</template>
