<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user:   Object,
    roles:  Array,
    errors: Object,
});

const processing = ref(false);

const form = useForm({
    name:                  props.user.name,
    email:                 props.user.email,
    first_name:            props.user.first_name,
    last_name:             props.user.last_name,
    phone:                 props.user.phone,
    company_name:          props.user.company_name,
    role:                  props.user.role,
    password:              '',
    password_confirmation: '',
    email_verified:        !!props.user.email_verified_at,
});

const submitForm = () => {
    processing.value = true;
    form.patch(route('admin.users.update', props.user.id), {
        onFinish: () => { processing.value = false; },
    });
};

const avatarGrad = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600','from-fuchsia-500 to-purple-700'];
    return p[[...(name||'U')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};
const initials = n => (n||'U').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();

// shared input class
const inp = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-200 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
</script>

<template>
    <Head :title="`Edit ${user?.name || 'User'}`" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-2 text-[12px] text-gray-600 mb-2">
                    <Link :href="route('admin.users.index')" class="hover:text-gray-400 transition-colors">User Management</Link>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-gray-400">Edit {{ user?.name }}</span>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Edit {{ user?.name }}</h1>
                <p class="text-[13px] text-gray-500 mt-1">Update user information, role assignment, and account settings</p>
            </div>
            <Link :href="route('admin.users.show', user.id)"
                class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Profile
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: user summary card -->
            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden sticky top-24" style="box-shadow:0 0 40px 0 rgba(244,184,64,0.07)">
                    <div class="h-20 bg-gradient-to-r from-amber-500/20 via-amber-400/10 to-transparent relative">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(244,184,64,0.15),transparent_70%)]"></div>
                    </div>
                    <div class="px-5 pb-5 -mt-10">
                        <div :class="['w-16 h-16 rounded-2xl overflow-hidden ring-4 ring-[#0c1c30] mb-3 bg-gradient-to-br', avatarGrad(user?.name)]">
                            <img v-if="user?.profile_picture_url" :src="user.profile_picture_url" class="w-full h-full object-cover"/>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <span class="text-white text-xl font-extrabold">{{ initials(user?.name) }}</span>
                            </div>
                        </div>
                        <p class="text-[15px] font-bold text-white">{{ form.name || user?.name }}</p>
                        <p class="text-[12px] text-gray-500 mt-0.5 break-all">{{ form.email || user?.email }}</p>
                        <div class="mt-3 pt-3 border-t border-white/[0.06] space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase tracking-widest font-bold text-gray-600">Role</span>
                                <span class="text-[12px] text-gray-300 font-medium capitalize">{{ form.role || '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase tracking-widest font-bold text-gray-600">Email Status</span>
                                <span :class="['text-[12px] font-semibold', form.email_verified ? 'text-emerald-400' : 'text-red-400']">
                                    {{ form.email_verified ? 'Verified' : 'Unverified' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase tracking-widest font-bold text-gray-600">Member Since</span>
                                <span class="text-[12px] text-gray-400">{{ user?.created_at ? new Date(user.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: form -->
            <div class="lg:col-span-2 space-y-5">
                <form @submit.prevent="submitForm">

                    <!-- Basic Information -->
                    <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden mb-5">
                        <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-[14px] font-bold text-white">Basic Information</h3>
                        </div>
                        <div class="p-6 space-y-5">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">First Name</label>
                                    <input v-model="form.first_name" type="text" placeholder="e.g. John" :class="inp"/>
                                    <p v-if="errors?.first_name" class="mt-1.5 text-[11px] text-red-400">{{ errors.first_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">Last Name</label>
                                    <input v-model="form.last_name" type="text" placeholder="e.g. Smith" :class="inp"/>
                                    <p v-if="errors?.last_name" class="mt-1.5 text-[11px] text-red-400">{{ errors.last_name }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">Full Name / Display Name <span class="text-amber-400">*</span></label>
                                <input v-model="form.name" type="text" required placeholder="e.g. John Smith" :class="inp"/>
                                <p v-if="errors?.name" class="mt-1.5 text-[11px] text-red-400">{{ errors.name }}</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">Email Address <span class="text-amber-400">*</span></label>
                                    <input v-model="form.email" type="email" required placeholder="user@example.com" :class="inp"/>
                                    <p v-if="errors?.email" class="mt-1.5 text-[11px] text-red-400">{{ errors.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">Phone Number</label>
                                    <input v-model="form.phone" type="text" placeholder="+1 (555) 123-4567" :class="inp"/>
                                    <p v-if="errors?.phone" class="mt-1.5 text-[11px] text-red-400">{{ errors.phone }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">Company Name</label>
                                <input v-model="form.company_name" type="text" placeholder="e.g. Tech Innovations LLC" :class="inp"/>
                                <p v-if="errors?.company_name" class="mt-1.5 text-[11px] text-red-400">{{ errors.company_name }}</p>
                            </div>

                        </div>
                    </div>

                    <!-- Role & Access -->
                    <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden mb-5">
                        <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h3 class="text-[14px] font-bold text-white">Role & Access</h3>
                        </div>
                        <div class="p-6">
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">User Role <span class="text-amber-400">*</span></label>
                            <select v-model="form.role" required :class="sel">
                                <option value="">Select a role…</option>
                                <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                            </select>
                            <p v-if="errors?.role" class="mt-1.5 text-[11px] text-red-400">{{ errors.role }}</p>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden mb-5">
                        <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-bold text-white">Password</h3>
                                <p class="text-[11px] text-gray-600">Leave blank to keep current password</p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">New Password</label>
                                <input v-model="form.password" type="password" placeholder="Enter new password" :class="inp"/>
                                <p v-if="errors?.password" class="mt-1.5 text-[11px] text-red-400">{{ errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-600 mb-2">Confirm Password</label>
                                <input v-model="form.password_confirmation" type="password" placeholder="Confirm new password" :class="inp"/>
                                <p v-if="errors?.password_confirmation" class="mt-1.5 text-[11px] text-red-400">{{ errors.password_confirmation }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden mb-6">
                        <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-[14px] font-bold text-white">Account Settings</h3>
                        </div>
                        <div class="p-6">
                            <!-- Email verified toggle -->
                            <label class="flex items-center gap-4 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" v-model="form.email_verified" class="sr-only peer"/>
                                    <div class="w-10 h-6 rounded-full border border-white/[0.10] bg-white/[0.05] peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-all"></div>
                                    <div class="absolute top-1 left-1 w-4 h-4 rounded-full bg-gray-500 peer-checked:bg-white peer-checked:translate-x-4 transition-all"></div>
                                </div>
                                <div>
                                    <p class="text-[13px] font-semibold text-gray-200 group-hover:text-white transition-colors">Email Verified</p>
                                    <p class="text-[11px] text-gray-600">Mark this user's email as verified</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('admin.users.index')"
                            class="inline-flex items-center justify-center px-5 h-10 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="processing"
                            class="inline-flex items-center justify-center gap-2 px-6 h-10 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 disabled:opacity-50 transition-all shadow-lg shadow-amber-500/20">
                            <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ processing ? 'Saving…' : 'Update User' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </AdminLayout>
</template>
