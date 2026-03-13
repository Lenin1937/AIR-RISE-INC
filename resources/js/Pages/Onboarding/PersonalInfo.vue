<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({ user: Object });

const form = useForm({
    address_line_1: props.user?.address_line_1 ?? '',
    address_line_2: props.user?.address_line_2 ?? '',
    city:           props.user?.city ?? '',
    state:          props.user?.state ?? '',
    zip_code:       props.user?.zip_code ?? '',
    country:        props.user?.country ?? 'US',
    company_name:   props.user?.company_name ?? '',
    date_of_birth:  '',
});

const inp = 'w-full h-12 rounded-xl border border-white/[0.10] bg-white/[0.05] px-4 text-[14px] text-white placeholder-gray-500 outline-none transition-all focus:border-[#d4a02f]/50 focus:bg-white/[0.08] focus:ring-2 focus:ring-[#d4a02f]/15';
const lbl = 'block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2';

const states = ['Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming'];

function submit() {
    form.post(route('onboarding.save-personal-info'), {
        preserveScroll: false,
        preserveState: false,
        onSuccess: () => {
            window.location.href = route('orders.create');
        },
    });
}
</script>

<template>
    <div class="min-h-screen bg-[#0b1e33]">
        <Head title="Personal Information | CORPIUS" />

        <!-- Top bar -->
        <div class="sticky top-0 z-10 bg-[#071525]/90 backdrop-blur border-b border-white/[0.06] px-6 py-3 flex items-center justify-between">
            <Link href="/">
                <img src="/logo.png" alt="CORPIUS" class="h-8 w-auto" />
            </Link>
            <button type="button" @click="router.post(route('logout'))" class="text-[13px] text-gray-500 hover:text-gray-300 transition-colors">Sign out</button>
        </div>

        <div class="max-w-2xl mx-auto px-6 py-12">

            <!-- Progress steps -->
            <div class="flex items-center gap-0 mb-10 justify-center">
                <template v-for="(s, idx) in [{n:1,l:'Personal Info'},{n:2,l:'Order'},{n:3,l:'Review'}]" :key="s.n">
                    <div class="flex flex-col items-center">
                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-[14px] font-bold border-2 transition-all',
                            s.n === 1 ? 'border-[#d4a02f] bg-[#d4a02f] text-[#0b1e33]' : 'border-white/20 text-gray-600']">
                            {{ s.n }}
                        </div>
                        <span :class="['text-[11px] font-semibold mt-1.5 uppercase tracking-wider', s.n === 1 ? 'text-[#d4a02f]' : 'text-gray-600']">{{ s.l }}</span>
                    </div>
                    <div v-if="idx < 2" class="w-20 h-[2px] bg-white/10 mx-1 mb-5 rounded" />
                </template>
            </div>

            <!-- Card -->
            <div class="bg-[#0d2540] rounded-2xl border border-white/[0.07] p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-[#d4a02f]/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#d4a02f]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[18px] font-bold text-white">Personal Information</h2>
                        <p class="text-[13px] text-gray-400">Provide your details to continue your application</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Home Address section -->
                    <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-5 space-y-4">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-lg bg-[#d4a02f]/10 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-[#d4a02f]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                            </div>
                            <span class="text-[13px] font-semibold text-white">Home Address</span>
                            <span class="text-[12px] text-gray-500">· Your current residential address</span>
                        </div>

                        <div>
                            <label :class="lbl">STREET ADDRESS <span class="text-red-400">*</span></label>
                            <input type="text" v-model="form.address_line_1" required placeholder="123 Main Street" :class="inp" />
                            <InputError class="mt-1" :message="form.errors.address_line_1" />
                        </div>

                        <div>
                            <label :class="lbl">APT / SUITE <span class="text-gray-600 normal-case text-[11px]">optional</span></label>
                            <input type="text" v-model="form.address_line_2" placeholder="Apt 4B" :class="inp" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label :class="lbl">CITY <span class="text-red-400">*</span></label>
                                <input type="text" v-model="form.city" required placeholder="New York" :class="inp" />
                                <InputError class="mt-1" :message="form.errors.city" />
                            </div>
                            <div>
                                <label :class="lbl">STATE <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select v-model="form.state" required
                                        class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0d2540] px-4 text-[14px] text-white outline-none transition-all focus:border-[#d4a02f]/50 focus:ring-2 focus:ring-[#d4a02f]/15 appearance-none cursor-pointer">
                                        <option value="" class="bg-[#0b1e33]">Select state</option>
                                        <option v-for="s in states" :key="s" :value="s" class="bg-[#0b1e33]">{{ s }}</option>
                                    </select>
                                    <svg class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                <InputError class="mt-1" :message="form.errors.state" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label :class="lbl">ZIP CODE <span class="text-red-400">*</span></label>
                                <input type="text" v-model="form.zip_code" required placeholder="10001" :class="inp" />
                                <InputError class="mt-1" :message="form.errors.zip_code" />
                            </div>
                            <div>
                                <label :class="lbl">DATE OF BIRTH <span class="text-gray-500 normal-case text-[11px]">optional</span></label>
                                <input type="date" v-model="form.date_of_birth" :class="inp + ' text-gray-300'" />
                                <InputError class="mt-1" :message="form.errors.date_of_birth" />
                            </div>
                        </div>
                    </div>

                    <!-- Company (optional) -->
                    <div>
                        <label :class="lbl">COMPANY NAME <span class="text-gray-500 normal-case text-[11px]">optional</span></label>
                        <input type="text" v-model="form.company_name" placeholder="Acme Corp LLC" :class="inp" />
                        <InputError class="mt-1" :message="form.errors.company_name" />
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full h-12 rounded-xl bg-[#d4a02f] text-[15px] font-bold text-[#0b1e33] hover:bg-[#e6b84a] transition-colors disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-[#d4a02f]/20 mt-2">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ form.processing ? 'Saving…' : 'Continue → Select Service' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
