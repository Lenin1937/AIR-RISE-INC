<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { __ } = useTranslations();

const props = defineProps({
    users:           { type: Object, default: () => ({}) },
    stats:           { type: Object, default: () => ({}) },
    filters:         { type: Object, default: () => ({}) },
    available_roles: { type: Array,  default: () => [] },
});

const search     = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role   || '');

const formatCurrency = v => {
    if (v === null || v === undefined || isNaN(v)) return '$0.00';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(v));
};
const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Never';
const initials   = n => (n || 'U').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();

const avatarGrad = name => {
    const p = [
        'from-violet-500 to-indigo-600',
        'from-amber-400 to-orange-500',
        'from-emerald-400 to-teal-600',
        'from-rose-400 to-pink-600',
        'from-sky-400 to-cyan-600',
        'from-fuchsia-500 to-purple-700',
    ];
    return p[[...(name||'U')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};

const roleStyle = role => {
    const map = {
        'super-admin':   { bg: 'bg-rose-400/10',    text: 'text-rose-400',    dot: 'bg-rose-400'    },
        'administrator': { bg: 'bg-violet-400/10',  text: 'text-violet-400',  dot: 'bg-violet-400'  },
        'admin':         { bg: 'bg-purple-400/10',  text: 'text-purple-400',  dot: 'bg-purple-400'  },
        'moderator':     { bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400'    },
        'staff':         { bg: 'bg-cyan-400/10',    text: 'text-cyan-400',    dot: 'bg-cyan-400'    },
        'client':        { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400' },
    };
    return map[(role||'client').toLowerCase()] || map['client'];
};

const deleteUser = id => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', { user: id }));
    }
};

const applyFilters = () => {
    router.get(route('admin.users.index'), {
        search: search.value || undefined,
        role:   roleFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

const kpis = [
    { label: 'Total Users',    key: 'total_users',      accent: '#60a5fa', glow: 'rgba(96,165,250,0.15)',   bg: 'from-blue-500/[0.10]',    iconBg: 'bg-blue-500/15',    iconColor: 'text-blue-400',    icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
    { label: 'Online Now',     key: 'online_users',     accent: '#34d399', glow: 'rgba(52,211,153,0.15)',   bg: 'from-emerald-500/[0.10]', iconBg: 'bg-emerald-500/15', iconColor: 'text-emerald-400', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Verified',       key: 'verified_users',   accent: '#c084fc', glow: 'rgba(192,132,252,0.15)',  bg: 'from-purple-500/[0.10]',  iconBg: 'bg-purple-500/15',  iconColor: 'text-purple-400',  icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { label: 'New Today',      key: 'new_users_today',  accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',   bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6' },
    { label: 'Total Revenue',  key: 'total_revenue',    accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',   bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', currency: true },
];
</script>

<template>
    <Head title="User Management" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">User Management</h1>
                <p class="text-[13px] text-gray-500 mt-1">Manage all registered users and their activities</p>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.roles.index')"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Manage Roles
                </Link>
                <Link :href="route('admin.users.create')"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-semibold text-[#0b1e33] hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create User
                </Link>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div v-for="kpi in kpis" :key="kpi.key"
                class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3 hover:border-white/[0.12] transition-all duration-200"
                :style="{boxShadow: '0 0 28px 0 ' + kpi.glow}">
                <div :class="['absolute inset-0 bg-gradient-to-br opacity-60 pointer-events-none to-transparent', kpi.bg]"></div>
                <div class="relative flex items-center justify-between gap-3">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', kpi.iconBg]">
                        <svg :class="['w-5 h-5', kpi.iconColor]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="kpi.icon"/>
                        </svg>
                    </div>
                </div>
                <div class="relative">
                    <p class="text-[22px] font-extrabold text-white leading-none">
                        {{ kpi.currency ? formatCurrency(stats?.[kpi.key] || 0) : (stats?.[kpi.key] || 0).toLocaleString() }}
                    </p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600 mt-1.5">{{ kpi.label }}</p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" :style="{background: 'linear-gradient(90deg,' + kpi.accent + ',transparent)'}"></div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">

            <!-- Table header + filters -->
            <div class="px-6 py-4 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center gap-3">
                <h2 class="text-[14px] font-bold text-white mr-auto">All Users
                    <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ users?.total || users?.data?.length || 0 }}</span>
                </h2>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search users…"
                            class="pl-9 pr-4 h-9 w-52 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                    </div>
                    <select v-model="roleFilter" @change="applyFilters"
                        class="h-9 px-3 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0c1c30]">
                        <option value="">All Roles</option>
                        <option v-for="r in available_roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-white/[0.05]">
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">User</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Role</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Status</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Orders</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Total Spent</th>
                            <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Last Login</th>
                            <th class="text-right px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!users?.data?.length">
                            <td colspan="7" class="px-6 py-14 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="text-[12px] text-gray-600">No users found</p>
                            </td>
                        </tr>
                        <tr v-for="user in users?.data || []" :key="user.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.025] transition-colors">
                            <!-- User -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-9 h-9 rounded-full flex-shrink-0 overflow-hidden ring-1 ring-white/10 bg-gradient-to-br', avatarGrad(user.name)]">
                                        <img v-if="user.profile_picture_url" :src="user.profile_picture_url" :alt="user.name" class="w-full h-full object-cover"/>
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <span class="text-white text-[11px] font-bold">{{ initials(user.name) }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-semibold text-gray-100 leading-none truncate">{{ user.name || 'Unknown' }}</p>
                                        <p class="text-[11px] text-gray-600 truncate mt-0.5">{{ user.email }}</p>
                                        <span v-if="user.email_verified_at" class="inline-flex items-center gap-1 text-[10px] text-emerald-500 mt-0.5">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Verified
                                        </span>
                                        <span v-else class="inline-block text-[10px] text-gray-600 mt-0.5">Unverified</span>
                                    </div>
                                </div>
                            </td>
                            <!-- Role -->
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 capitalize', roleStyle(user.role).bg, roleStyle(user.role).text]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', roleStyle(user.role).dot]"></span>
                                    {{ user.role || 'client' }}
                                </span>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1',
                                    user.status === 'online' ? 'bg-emerald-400/10 text-emerald-400' : 'bg-gray-700/50 text-gray-500']">
                                    <span :class="['w-1.5 h-1.5 rounded-full', user.status === 'online' ? 'bg-emerald-400 animate-pulse' : 'bg-gray-600']"></span>
                                    {{ user.status === 'online' ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <!-- Orders -->
                            <td class="px-6 py-4 text-gray-400 font-medium">{{ user.orders_count || 0 }}</td>
                            <!-- Spent -->
                            <td class="px-6 py-4 font-semibold" :class="(user.total_spent || 0) > 0 ? 'text-amber-400' : 'text-gray-600'">
                                {{ formatCurrency(user.total_spent || 0) }}
                            </td>
                            <!-- Last login -->
                            <td class="px-6 py-4 text-[12px] text-gray-600">{{ formatDate(user.last_login) }}</td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <Link :href="route('admin.users.show', user.id)"
                                        class="text-[12px] font-semibold text-amber-400 hover:text-amber-300 transition-colors">View</Link>
                                    <Link :href="route('admin.users.edit', user.id)"
                                        class="text-[12px] font-semibold text-blue-400 hover:text-blue-300 transition-colors">Edit</Link>
                                    <button @click="deleteUser(user.id)"
                                        class="text-[12px] font-semibold text-gray-600 hover:text-red-400 transition-colors">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users?.links && users.links.length > 3" class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-between">
                <p class="text-[12px] text-gray-600">
                    Showing {{ users.from }}–{{ users.to }} of {{ users.total }} users
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in users.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url"
                            :class="['inline-flex items-center justify-center w-8 h-8 rounded-lg text-[12px] font-medium transition-all',
                                link.active
                                    ? 'bg-amber-400 text-[#0b1e33] font-bold'
                                    : 'text-gray-500 hover:text-white hover:bg-white/[0.06]']"
                            v-html="link.label"/>
                        <span v-else
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-[12px] text-gray-700 cursor-not-allowed"
                            v-html="link.label"/>
                    </template>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
