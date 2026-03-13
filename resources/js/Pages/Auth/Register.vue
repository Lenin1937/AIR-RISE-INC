<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

// ── Step state ──────────────────────────────────────────────────────────────
const step = ref(1); // 1 = Profile, 2 = Verify, 3 = Access

// Step 1 fields
const step1 = ref({ first_name: '', last_name: '', username: '', email: '' });
const step1Errors = ref({});

// Step 2 fields
const otp = ref('');
const otpError = ref('');
const otpSent = ref(false);
const otpSending = ref(false);
const otpVerifying = ref(false);
const emailVerified = ref(false);
const resendCooldown = ref(0);
let cooldownTimer = null;

// Step 3 – final form
const showPassword = ref(false);
const showConfirm  = ref(false);
const form = useForm({
    first_name:          '',
    last_name:           '',
    username:            '',
    email:               '',
    phone:               '',
    telegram_username:   '',
    password:            '',
    password_confirmation: '',
    terms_accepted:      false,
});

// ── Step labels ─────────────────────────────────────────────────────────────
const steps = [
    { number: 1, label: 'Profile' },
    { number: 2, label: 'Verify'  },
    { number: 3, label: 'Access'  },
];

async function refreshCsrfToken() {
    const response = await axios.get(route('csrf.token'));
    const newToken = response?.data?.token;

    if (newToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
        const meta = document.head.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', newToken);
    }
}

// ── Step 1 – Continue ────────────────────────────────────────────────────────
const step1Submitting = ref(false);

async function goToVerify() {
    step1Errors.value = {};
    if (!step1.value.first_name) { step1Errors.value.first_name = 'First name is required.'; }
    if (!step1.value.last_name)  { step1Errors.value.last_name  = 'Last name is required.';  }
    if (!step1.value.username)   { step1Errors.value.username   = 'Username is required.';   }
    if (!step1.value.email)      { step1Errors.value.email      = 'Email is required.';      }
    if (Object.keys(step1Errors.value).length) return;

    await sendOtp();
}

async function sendOtp() {
    otpSending.value = true;
    step1Errors.value = {};
    otpError.value = '';

    try {
        await refreshCsrfToken();

        await axios.post(route('register.send-otp'), {
            email:      step1.value.email,
            first_name: step1.value.first_name,
        });
        otpSent.value = true;
        emailVerified.value = false;
        otp.value = '';
        step.value = 2;
        startResendCooldown();
    } catch (err) {
        if (err.response?.status === 419) {
            step1Errors.value.email = 'Session expired. Please try again.';
            return;
        }

        const errors = err.response?.data?.errors ?? {};
        if (errors.email)      step1Errors.value.email    = errors.email[0];
        if (errors.first_name) step1Errors.value.first_name = errors.first_name[0];
        if (!errors.email && !errors.first_name) {
            step1Errors.value.email = err.response?.data?.message ?? 'Could not send code.';
        }
    } finally {
        otpSending.value = false;
    }
}

function startResendCooldown() {
    resendCooldown.value = 60;
    clearInterval(cooldownTimer);
    cooldownTimer = setInterval(() => {
        resendCooldown.value--;
        if (resendCooldown.value <= 0) clearInterval(cooldownTimer);
    }, 1000);
}

// ── Step 2 – Verify OTP ──────────────────────────────────────────────────────
async function verifyOtp() {
    otpError.value = '';
    if (!otp.value || otp.value.length !== 6) {
        otpError.value = 'Please enter the 6-digit code.';
        return;
    }
    otpVerifying.value = true;
    try {
        await refreshCsrfToken();

        await axios.post(route('register.verify-otp'), {
            email: step1.value.email,
            otp:   otp.value,
        });
        emailVerified.value = true;
        // Copy step1 into final form and advance
        form.first_name = step1.value.first_name;
        form.last_name  = step1.value.last_name;
        form.username   = step1.value.username;
        form.email      = step1.value.email;
        step.value = 3;
    } catch (err) {
        if (err.response?.status === 419) {
            otpError.value = 'Session expired. Please resend OTP and try again.';
            return;
        }

        const errors = err.response?.data?.errors ?? {};
        otpError.value = errors.otp?.[0] ?? err.response?.data?.message ?? 'Invalid code.';
    } finally {
        otpVerifying.value = false;
    }
}

// ── Step 3 – Create Account ──────────────────────────────────────────────────
async function submit() {
    await refreshCsrfToken();

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}

// ── Styling helpers ──────────────────────────────────────────────────────────
const inp  = 'w-full h-12 rounded-xl border border-white/[0.10] bg-white/[0.05] px-4 text-[14px] text-white placeholder-gray-500 outline-none transition-all focus:border-[#d4a02f]/50 focus:bg-white/[0.08] focus:ring-2 focus:ring-[#d4a02f]/15';
const lbl  = 'block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2';
</script>

<template>
    <div class="min-h-screen bg-[#0b1e33] flex flex-col lg:flex-row">
        <Head title="Create Account | CORPIUS" />

        <!-- ── LEFT PANEL ───────────────────────────────────────────────── -->
        <div class="w-full lg:w-[700px] lg:flex-shrink-0 flex flex-col items-center justify-center px-10 py-14 min-h-screen">
            <div class="w-full max-w-[420px]">

                <!-- Logo -->
                <div class="flex flex-col items-center mb-8">
                    <Link href="/">
                        <img src="/logo.png" alt="CORPIUS" class="h-16 w-auto mb-5">
                    </Link>
                    <h1 class="text-[28px] font-extrabold text-white">Create Account</h1>
                    <p class="mt-1.5 text-[14px] text-gray-400 text-center">
                        Join <span class="text-[#d4a02f] font-medium">CORPIUS</span> and start your U.S. business today
                    </p>
                </div>

                <!-- ── Step Indicators ──────────────────────────────────── -->
                <div class="flex items-center justify-center gap-0 mb-8">
                    <template v-for="(s, idx) in steps" :key="s.number">
                        <!-- Step bubble -->
                        <div class="flex flex-col items-center">
                            <div :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-[14px] font-bold border-2 transition-all',
                                step > s.number
                                    ? 'bg-[#d4a02f] border-[#d4a02f] text-[#0b1e33]'
                                    : step === s.number
                                        ? 'bg-transparent border-[#d4a02f] text-[#d4a02f]'
                                        : 'bg-transparent border-white/20 text-gray-500'
                            ]">
                                <!-- checkmark for completed -->
                                <svg v-if="step > s.number" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span v-else>{{ s.number }}</span>
                            </div>
                            <span :class="[
                                'text-[11px] font-semibold mt-1.5 uppercase tracking-wider',
                                step === s.number ? 'text-[#d4a02f]' : step > s.number ? 'text-[#d4a02f]/70' : 'text-gray-600'
                            ]">{{ s.label }}</span>
                        </div>
                        <!-- connector line -->
                        <div v-if="idx < steps.length - 1"
                            :class="['w-16 h-[2px] mb-5 mx-1 rounded transition-all', step > s.number ? 'bg-[#d4a02f]' : 'bg-white/10']" />
                    </template>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  STEP 1 — Profile                                      -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div v-if="step === 1" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label :class="lbl">FIRST NAME <span class="text-red-400">*</span></label>
                            <input type="text" v-model="step1.first_name" placeholder="John" :class="inp" autocomplete="given-name" />
                            <p v-if="step1Errors.first_name" class="mt-1 text-[12px] text-red-400">{{ step1Errors.first_name }}</p>
                        </div>
                        <div>
                            <label :class="lbl">LAST NAME <span class="text-red-400">*</span></label>
                            <input type="text" v-model="step1.last_name" placeholder="Smith" :class="inp" autocomplete="family-name" />
                            <p v-if="step1Errors.last_name" class="mt-1 text-[12px] text-red-400">{{ step1Errors.last_name }}</p>
                        </div>
                    </div>

                    <div>
                        <label :class="lbl">LOGIN (USERNAME) <span class="text-red-400">*</span></label>
                        <input type="text" v-model="step1.username" placeholder="Choose a username" :class="inp" autocomplete="username" />
                        <p v-if="step1Errors.username" class="mt-1 text-[12px] text-red-400">{{ step1Errors.username }}</p>
                    </div>

                    <div>
                        <label :class="lbl">EMAIL ADDRESS <span class="text-red-400">*</span></label>
                        <input type="email" v-model="step1.email" placeholder="email@example.com" :class="inp" autocomplete="email" />
                        <p v-if="step1Errors.email" class="mt-1 text-[12px] text-red-400">{{ step1Errors.email }}</p>
                    </div>

                    <button type="button" @click="goToVerify" :disabled="otpSending"
                        class="w-full h-12 rounded-xl bg-[#d4a02f] text-[15px] font-bold text-[#0b1e33] hover:bg-[#e6b84a] active:bg-[#b8902a] transition-colors disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-[#d4a02f]/20 mt-2">
                        <svg v-if="otpSending" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ otpSending ? 'Sending code…' : 'Continue →' }}
                    </button>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  STEP 2 — Verify Email                                 -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div v-if="step === 2" class="space-y-5">
                    <!-- Info banner -->
                    <div class="flex items-start gap-3 rounded-xl border border-[#d4a02f]/30 bg-[#d4a02f]/5 px-4 py-3">
                        <svg class="w-5 h-5 text-[#d4a02f] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        <div>
                            <p class="text-[13px] font-semibold text-white">Verify your Email</p>
                            <p class="text-[12px] text-gray-400 mt-0.5">We'll send a verification code to <span class="text-[#d4a02f] font-medium">{{ step1.email }}</span></p>
                        </div>
                    </div>

                    <div>
                        <label :class="lbl">EMAIL VERIFICATION CODE</label>
                        <div class="flex gap-3">
                            <input type="text" v-model="otp" maxlength="6" placeholder="6-digit code"
                                :class="inp + ' flex-1 tracking-widest text-center text-lg font-bold'"
                                @keydown.enter="verifyOtp" />
                            <button type="button" @click="sendOtp" :disabled="otpSending || resendCooldown > 0"
                                class="h-12 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[13px] font-bold transition-colors disabled:opacity-50 whitespace-nowrap flex-shrink-0">
                                {{ resendCooldown > 0 ? `Resend (${resendCooldown}s)` : otpSending ? '…' : 'Send OTP' }}
                            </button>
                        </div>
                        <p class="mt-1.5 text-[12px] text-gray-500">Click "Send OTP" to receive a 6-digit code</p>
                        <p v-if="otpError" class="mt-1 text-[12px] text-red-400">{{ otpError }}</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="step = 1"
                            class="flex-1 h-12 rounded-xl border border-white/10 text-[14px] text-gray-300 font-semibold hover:bg-white/5 transition-colors">
                            ← Back
                        </button>
                        <button type="button" @click="verifyOtp" :disabled="otpVerifying || !otp"
                            :class="['flex-[2] h-12 rounded-xl text-[14px] font-bold transition-colors disabled:opacity-50 flex items-center justify-center gap-2',
                                emailVerified ? 'bg-emerald-600 text-white' : 'bg-[#d4a02f] text-[#0b1e33] hover:bg-[#e6b84a]']">
                            <svg v-if="otpVerifying" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ otpVerifying ? 'Verifying…' : 'Verify OTP →' }}
                        </button>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  STEP 3 — Access (Password)                            -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div v-if="step === 3">
                    <!-- Email verified banner -->
                    <div class="flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 mb-5">
                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[13px] text-emerald-400">Email <span class="font-semibold">{{ step1.email }}</span> verified ✓</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label :class="lbl">PHONE NUMBER <span class="text-red-400">*</span></label>
                            <input type="tel" v-model="form.phone" required placeholder="+1 (555) 000-0000" :class="inp" autocomplete="tel" />
                            <InputError class="mt-1" :message="form.errors.phone" />
                        </div>

                        <div>
                            <label :class="lbl">TELEGRAM NICKNAME <span class="text-gray-600 normal-case text-[11px]">(optional)</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-[14px] font-bold select-none">@</span>
                                <input type="text" v-model="form.telegram_username" placeholder="username (optional)"
                                    :class="inp + ' pl-8'" />
                            </div>
                            <InputError class="mt-1" :message="form.errors.telegram_username" />
                        </div>

                        <div>
                            <label :class="lbl">PASSWORD <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" v-model="form.password" required
                                    placeholder="Min 8 characters" :class="inp + ' pr-12'" autocomplete="new-password" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                    <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>

                        <div>
                            <label :class="lbl">CONFIRM PASSWORD <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" v-model="form.password_confirmation"
                                    required placeholder="Repeat password" :class="inp + ' pr-12'" autocomplete="new-password" />
                                <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                    <svg v-if="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            <InputError class="mt-1" :message="form.errors.password_confirmation" />
                        </div>

                        <!-- Terms -->
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" v-model="form.terms_accepted"
                                class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-white/5 text-[#d4a02f] focus:ring-[#d4a02f]/30 flex-shrink-0" />
                            <span class="text-[13px] text-gray-400">
                                I agree to the
                                <a href="/terms-of-service" class="text-[#d4a02f] hover:text-[#e6b84a]">Terms of Service</a>
                                and
                                <a href="/privacy-policy" class="text-[#d4a02f] hover:text-[#e6b84a]">Privacy Policy</a>.
                            </span>
                        </label>
                        <InputError class="mt-1" :message="form.errors.terms_accepted" />

                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="step = 2"
                                class="flex-1 h-12 rounded-xl border border-white/10 text-[14px] text-gray-300 font-semibold hover:bg-white/5 transition-colors">
                                ← Back
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-[2] h-12 rounded-xl bg-[#d4a02f] text-[15px] font-bold text-[#0b1e33] hover:bg-[#e6b84a] active:bg-[#b8902a] transition-colors disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-[#d4a02f]/20">
                                <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                {{ form.processing ? 'Creating account…' : 'Create Account' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sign in link -->
                <p class="mt-6 text-center text-[14px] text-gray-400">
                    Already have an account?
                    <Link :href="route('login')" class="text-[#d4a02f] font-bold hover:text-[#e6b84a] transition-colors ml-1">Sign In</Link>
                </p>

            </div>
        </div>

        <!-- ── RIGHT PANEL ───────────────────────────────────────────────── -->
        <div class="hidden lg:flex flex-1 relative bg-[#071525] flex-col overflow-hidden">
            <div class="absolute inset-0 opacity-[0.06]"
                style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 28px 28px;"></div>
            <div class="absolute top-1/3 left-1/3 w-[500px] h-[500px] rounded-full bg-[#d4a02f]/8 blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 rounded-full bg-blue-500/8 blur-3xl pointer-events-none"></div>
            <div class="absolute left-0 top-0 bottom-0 w-16 bg-gradient-to-r from-[#071525] to-transparent z-10 pointer-events-none"></div>
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
            <div class="relative z-20 flex-1 flex items-start justify-center px-6 pb-0 overflow-hidden">
                <div class="w-full max-w-5xl rounded-t-2xl overflow-hidden shadow-[0_-8px_60px_rgba(0,0,0,0.6)] border border-white/10 border-b-0">
                    <img src="/images/auth-bg.png?v=3" alt="CORPIUS Dashboard" class="w-full block" />
                </div>
            </div>
        </div>

    </div>
</template>
