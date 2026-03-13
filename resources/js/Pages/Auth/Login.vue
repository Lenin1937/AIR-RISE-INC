<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-[#0b1e33] flex flex-col lg:flex-row">
        <Head title="Sign In | CORPIUS" />

        <!-- ── LEFT PANEL ── -->
        <div class="w-full lg:w-[700px] lg:flex-shrink-0 flex flex-col items-center justify-center px-10 py-14 min-h-screen">

            <div class="w-full max-w-[360px]">

                <!-- Logo -->
                <div class="flex flex-col items-center mb-10">
                    <Link href="/">
                        <img src="/logo.png" alt="CORPIUS" class="h-16 w-auto mb-5">
                    </Link>
                    <h1 class="text-[28px] font-extrabold text-white">Welcome back</h1>
                    <p class="mt-1.5 text-[14px] text-gray-400 text-center">
                        Sign in to your
                        <span class="text-[#d4a02f] font-medium">CORPIUS</span>
                        account
                    </p>
                </div>

                <!-- Status -->
                <div v-if="status" class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-sm text-emerald-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">EMAIL</label>
                        <input
                            type="email" v-model="form.email"
                            required autofocus autocomplete="username"
                            placeholder="you@example.com"
                            class="w-full h-12 rounded-xl border border-white/[0.10] bg-white/[0.05] px-4 text-[14px] text-white placeholder-gray-500 outline-none transition-all focus:border-[#d4a02f]/50 focus:bg-white/[0.08] focus:ring-2 focus:ring-[#d4a02f]/15"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400">PASSWORD</label>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                class="text-[13px] text-[#d4a02f] hover:text-[#e6b84a] transition-colors font-medium">
                                Forgot Password?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'" v-model="form.password"
                                required autocomplete="current-password" placeholder="••••••••"
                                class="w-full h-12 rounded-xl border border-white/[0.10] bg-white/[0.05] px-4 pr-12 text-[14px] text-white placeholder-gray-500 outline-none transition-all focus:border-[#d4a02f]/50 focus:bg-white/[0.08] focus:ring-2 focus:ring-[#d4a02f]/15"
                            />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <!-- Remember me -->
                    <label class="flex items-center gap-2.5 cursor-pointer select-none w-fit">
                        <input type="checkbox" v-model="form.remember"
                            class="h-4 w-4 rounded border-gray-600 bg-white/5 text-[#d4a02f] focus:ring-[#d4a02f]/30" />
                        <span class="text-[14px] text-gray-300">Remember me</span>
                    </label>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full h-12 rounded-xl bg-[#d4a02f] text-[15px] font-bold text-[#0b1e33] hover:bg-[#e6b84a] active:bg-[#b8902a] transition-colors disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-[#d4a02f]/20">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Signing in…' : 'Sign In' }}
                    </button>

                </form>

                <!-- Register link -->
                <p class="mt-6 text-center text-[14px] text-gray-400">
                    Don't have an account?
                    <Link :href="route('register')" class="text-[#d4a02f] font-bold hover:text-[#e6b84a] transition-colors ml-1">Create an account</Link>
                </p>

            </div>
        </div>

        <!-- ── RIGHT PANEL ── -->
        <div class="hidden lg:flex flex-1 relative bg-[#071525] flex-col overflow-hidden">

            <!-- Dot grid -->
            <div class="absolute inset-0 opacity-[0.06]"
                style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 28px 28px;"></div>

            <!-- Glow blobs -->
            <div class="absolute top-1/3 left-1/3 w-[500px] h-[500px] rounded-full bg-[#d4a02f]/8 blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 rounded-full bg-blue-500/8 blur-3xl pointer-events-none"></div>

            <!-- Left fade -->
            <div class="absolute left-0 top-0 bottom-0 w-16 bg-gradient-to-r from-[#071525] to-transparent z-10 pointer-events-none"></div>

            <!-- Text section -->
            <div class="relative z-20 flex flex-col items-center text-center pt-16 pb-8 px-16">
                <span class="mb-5 inline-flex items-center gap-2 rounded-full bg-[#d4a02f]/10 border border-[#d4a02f]/25 px-4 py-1.5 text-[11px] font-bold uppercase tracking-widest text-[#d4a02f]">
                    Fast · Accurate · Fully Compliant
                </span>
                <h2 class="text-[38px] font-extrabold text-white leading-tight mb-4">
                    Start your U.S. <span class="text-[#d4a02f]">business</span><br>the right way
                </h2>
                <p class="text-gray-400 text-[15px] leading-relaxed max-w-lg">
                    Form any company (C-Corp, S-Corp, LLC, or Nonprofit) in any U.S. state. We handle state filings, EIN, operating agreements, and compliance tracking.
                </p>
            </div>

            <!-- Large screenshot -->
            <div class="relative z-20 flex-1 flex items-start justify-center px-6 pb-0 overflow-hidden">
                <div class="w-full max-w-5xl rounded-t-2xl overflow-hidden shadow-[0_-8px_60px_rgba(0,0,0,0.6)] border border-white/10 border-b-0">
                    <img src="/images/auth-bg.png?v=3" alt="CORPIUS Dashboard" class="w-full block" />
                </div>
            </div>

        </div>

    </div>
</template>
