<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ user: Object });

const formatCurrency = v => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v || 0);
const formatDate     = d => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Never';
const initials       = n => (n || 'U').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
const avatarGrad     = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600','from-fuchsia-500 to-purple-700'];
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
</script>

<template>
    <Head :title="`${user?.name || 'User'} — Details`" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">User Details</h1>
                <p class="text-[13px] text-gray-500 mt-1">Viewing profile for <span class="text-gray-300 font-medium">{{ user?.name }}</span></p>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.users.edit', { user: user.id })"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg bg-amber-400 text-[13px] font-semibold text-[#0b1e33] hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit User
                </Link>
                <Link :href="route('admin.users.index')"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Users
                </Link>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <!-- Total Orders -->
            <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex items-center gap-4 hover:border-white/[0.12] transition-all" style="box-shadow:0 0 28px 0 rgba(96,165,250,0.12)">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/[0.10] to-transparent pointer-events-none"></div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="relative">
                    <p class="text-[26px] font-extrabold text-white leading-none">{{ user?.orders_count || 0 }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600 mt-1">Total Orders</p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" style="background:linear-gradient(90deg,#60a5fa,transparent)"></div>
            </div>
            <!-- Total Spent -->
            <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex items-center gap-4 hover:border-white/[0.12] transition-all" style="box-shadow:0 0 28px 0 rgba(244,184,64,0.15)">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/[0.12] to-transparent pointer-events-none"></div>
                <div class="w-12 h-12 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="relative">
                    <p class="text-[26px] font-extrabold text-white leading-none">{{ formatCurrency(user?.total_spent || 0) }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600 mt-1">Total Spent</p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" style="background:linear-gradient(90deg,#f4b840,transparent)"></div>
            </div>
            <!-- Verification -->
            <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex items-center gap-4 hover:border-white/[0.12] transition-all"
                :style="user?.email_verified_at ? 'box-shadow:0 0 28px 0 rgba(52,211,153,0.12)' : 'box-shadow:0 0 28px 0 rgba(248,113,113,0.10)'">
                <div :class="['absolute inset-0 bg-gradient-to-br to-transparent pointer-events-none', user?.email_verified_at ? 'from-emerald-500/[0.10]' : 'from-red-500/[0.08]']"></div>
                <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shrink-0', user?.email_verified_at ? 'bg-emerald-500/15' : 'bg-red-500/10']">
                    <svg v-if="user?.email_verified_at" class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg v-else class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="relative">
                    <p :class="['text-[26px] font-extrabold leading-none', user?.email_verified_at ? 'text-emerald-400' : 'text-red-400']">
                        {{ user?.email_verified_at ? 'Verified' : 'Unverified' }}
                    </p>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600 mt-1">Email Status</p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" :style="user?.email_verified_at ? 'background:linear-gradient(90deg,#34d399,transparent)' : 'background:linear-gradient(90deg,#f87171,transparent)'"></div>
            </div>
        </div>

        <!-- Main grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: Profile Card -->
            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden" style="box-shadow:0 0 40px 0 rgba(244,184,64,0.08)">
                    <!-- Banner -->
                    <div class="h-24 bg-gradient-to-r from-amber-500/20 via-amber-400/10 to-transparent relative">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(244,184,64,0.15),transparent_70%)]"></div>
                    </div>
                    <!-- Avatar -->
                    <div class="px-6 pb-6 -mt-12">
                        <div :class="['w-20 h-20 rounded-2xl overflow-hidden ring-4 ring-[#0c1c30] mb-4 bg-gradient-to-br', avatarGrad(user?.name)]">
                            <img v-if="user?.profile_picture_url" :src="user.profile_picture_url" :alt="user.name" class="w-full h-full object-cover"/>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <span class="text-white text-2xl font-extrabold">{{ initials(user?.name) }}</span>
                            </div>
                        </div>
                        <h2 class="text-[18px] font-bold text-white leading-tight">{{ user?.name || 'Unknown User' }}</h2>
                        <p class="text-[13px] text-gray-500 mt-0.5 break-all">{{ user?.email }}</p>

                        <!-- Role badge -->
                        <div class="mt-3 flex items-center gap-2">
                            <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 capitalize', roleStyle(user?.role).bg, roleStyle(user?.role).text]">
                                <span :class="['w-1.5 h-1.5 rounded-full', roleStyle(user?.role).dot]"></span>
                                {{ user?.role_display || user?.role || 'Client' }}
                            </span>
                            <!-- Online/Offline -->
                            <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', user?.status === 'online' ? 'bg-emerald-400/10 text-emerald-400' : 'bg-gray-700/50 text-gray-500']">
                                <span :class="['w-1.5 h-1.5 rounded-full', user?.status === 'online' ? 'bg-emerald-400 animate-pulse' : 'bg-gray-600']"></span>
                                {{ user?.status === 'online' ? 'Online' : 'Offline' }}
                            </span>
                        </div>

                        <!-- Divider -->
                        <div class="mt-5 pt-5 border-t border-white/[0.06] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-600 uppercase tracking-widest font-semibold">Member Since</span>
                                <span class="text-[12px] text-gray-300 font-medium">{{ user?.created_at ? new Date(user.created_at).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'}) : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-600 uppercase tracking-widest font-semibold">Last Login</span>
                                <span class="text-[12px] text-gray-300 font-medium">{{ user?.last_login ? new Date(user.last_login).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'}) : 'Never' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-600 uppercase tracking-widest font-semibold">KYC Status</span>
                                <span :class="['text-[12px] font-semibold', user?.kyc_verified ? 'text-emerald-400' : 'text-red-400']">
                                    {{ user?.kyc_verified ? 'Verified' : 'Not Verified' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-600 uppercase tracking-widest font-semibold">Email Verified</span>
                                <span :class="['text-[12px] font-semibold', user?.email_verified_at ? 'text-emerald-400' : 'text-red-400']">
                                    {{ user?.email_verified_at ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="mt-5 flex flex-col gap-2">
                            <Link :href="route('admin.users.edit', { user: user.id })"
                                class="w-full inline-flex items-center justify-center gap-2 h-9 rounded-lg bg-amber-400 text-[13px] font-semibold text-[#0b1e33] hover:bg-amber-300 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Profile
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Detail panels -->
            <div class="lg:col-span-2 space-y-5">

                <!-- User Information -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-bold text-white">User Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                        <div v-for="field in [
                            { label: 'Full Name',           value: user?.name },
                            { label: 'Email Address',        value: user?.email },
                            { label: 'First Name',           value: user?.first_name },
                            { label: 'Last Name',            value: user?.last_name },
                            { label: 'Phone Number',         value: user?.phone },
                            { label: 'Company Name',         value: user?.company_name },
                            { label: 'Registration Date',    value: formatDate(user?.created_at) },
                            { label: 'Last Login',           value: formatDate(user?.last_login) },
                            { label: 'Email Verified At',    value: formatDate(user?.email_verified_at) },
                            { label: 'KYC Status',           value: user?.kyc_verified ? 'Verified' : 'Not Verified', kyc: true },
                        ]" :key="field.label">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">{{ field.label }}</p>
                                <p :class="['text-[13px] font-medium leading-snug',
                                    field.kyc
                                        ? (user?.kyc_verified ? 'text-emerald-400' : 'text-red-400')
                                        : (field.value && field.value !== 'Never' ? 'text-gray-200' : 'text-gray-600')]">
                                    {{ field.value || 'Not provided' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-bold text-white">Address Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                        <div v-for="field in [
                            { label: 'Address Line 1', value: user?.address_line_1 },
                            { label: 'Address Line 2', value: user?.address_line_2 },
                            { label: 'City',           value: user?.city },
                            { label: 'State',          value: user?.state },
                            { label: 'ZIP Code',       value: user?.zip_code },
                            { label: 'Country',        value: user?.country },
                            { label: 'Citizenship',    value: user?.citizenship },
                        ]" :key="field.label">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">{{ field.label }}</p>
                                <p :class="['text-[13px] font-medium', field.value ? 'text-gray-200' : 'text-gray-600']">{{ field.value || 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-bold text-white">Preferences & Settings</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Preferred Language</p>
                            <p class="text-[13px] font-medium text-gray-200">{{ user?.preferred_language || 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Timezone</p>
                            <p class="text-[13px] font-medium text-gray-200">{{ user?.timezone || 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Email Notifications</p>
                            <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', user?.email_notifications ? 'bg-emerald-400/10 text-emerald-400' : 'bg-red-400/10 text-red-400']">
                                <span :class="['w-1.5 h-1.5 rounded-full', user?.email_notifications ? 'bg-emerald-400' : 'bg-red-400']"></span>
                                {{ user?.email_notifications ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">SMS Notifications</p>
                            <span :class="['inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1', user?.sms_notifications ? 'bg-emerald-400/10 text-emerald-400' : 'bg-red-400/10 text-red-400']">
                                <span :class="['w-1.5 h-1.5 rounded-full', user?.sms_notifications ? 'bg-emerald-400' : 'bg-red-400']"></span>
                                {{ user?.sms_notifications ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>
