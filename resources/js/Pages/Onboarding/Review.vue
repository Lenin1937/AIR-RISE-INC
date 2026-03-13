<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user:  Object,
    order: Object,
});

const isPending  = computed(() => props.user.registration_status === 'pending_approval');
const isRejected = computed(() => props.user.registration_status === 'rejected');
</script>

<template>
    <div class="min-h-screen bg-[#0b1e33]">
        <Head title="Application Review | CORPIUS" />

        <!-- Top bar -->
        <div class="sticky top-0 z-10 bg-[#071525]/90 backdrop-blur border-b border-white/[0.06] px-6 py-3 flex items-center justify-between">
            <Link href="/"><img src="/logo.png" alt="CORPIUS" class="h-8 w-auto" /></Link>
            <form method="POST" :action="route('logout')">
                <input type="hidden" name="_token" :value="$page.props.csrf_token ?? ''" />
                <button type="submit" class="text-[13px] text-gray-500 hover:text-gray-300 transition-colors">Sign out</button>
            </form>
        </div>

        <div class="max-w-xl mx-auto px-6 py-16 text-center">

            <!-- Progress steps -->
            <div class="flex items-center gap-0 mb-12 justify-center">
                <template v-for="(s, idx) in [{n:1,l:'Personal Info'},{n:2,l:'Order'},{n:3,l:'Review'}]" :key="s.n">
                    <div class="flex flex-col items-center">
                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-[14px] font-bold border-2 transition-all',
                            s.n < 3 ? 'border-[#d4a02f] bg-[#d4a02f] text-[#0b1e33]' : isRejected ? 'border-red-500 bg-red-500 text-white' : 'border-[#d4a02f] bg-[#d4a02f] text-[#0b1e33]']">
                            <svg v-if="s.n < 3" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <svg v-else-if="isRejected" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span :class="['text-[11px] font-semibold mt-1.5 uppercase tracking-wider',
                            s.n === 3 && isRejected ? 'text-red-400' : 'text-[#d4a02f]']">{{ s.l }}</span>
                    </div>
                    <div v-if="idx < 2" class="w-20 h-[2px] bg-[#d4a02f] mx-1 mb-5 rounded" />
                </template>
            </div>

            <!-- ── REJECTED ──────────────────────────── -->
            <template v-if="isRejected">
                <div class="w-20 h-20 rounded-full bg-red-500/10 border-2 border-red-500/30 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <h1 class="text-[28px] font-extrabold text-white mb-3">Application Not Approved</h1>
                <p class="text-gray-400 text-[15px] leading-relaxed mb-6">
                    Unfortunately, your application was not approved at this time.
                </p>
                <div v-if="user.rejection_reason" class="rounded-xl border border-red-500/20 bg-red-500/5 p-4 text-left mb-6">
                    <p class="text-[12px] font-bold uppercase tracking-wider text-red-400 mb-1">Reason</p>
                    <p class="text-[14px] text-gray-300">{{ user.rejection_reason }}</p>
                </div>
                <p class="text-[14px] text-gray-500">
                    Please contact us at
                    <a href="mailto:support@corpius.net" class="text-[#d4a02f] hover:text-[#e6b84a]">support@corpius.net</a>
                    for assistance.
                </p>
            </template>

            <!-- ── PENDING ───────────────────────────── -->
            <template v-else>
                <!-- Animated clock / hourglass icon -->
                <div class="relative mx-auto mb-6 w-24 h-24">
                    <div class="w-24 h-24 rounded-full border-4 border-[#d4a02f]/20 flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full border-4 border-t-[#d4a02f] border-r-transparent border-b-transparent border-l-transparent animate-spin" style="animation-duration: 2s;"></div>
                        <svg class="w-10 h-10 text-[#d4a02f]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <h1 class="text-[28px] font-extrabold text-white mb-3">Under Review</h1>
                <p class="text-gray-400 text-[15px] leading-relaxed mb-8 max-w-md mx-auto">
                    Your application has been submitted and is awaiting admin review. You'll receive an email notification once approved. This typically takes <span class="text-white font-semibold">1–2 business days</span>.
                </p>

                <!-- Summary card -->
                <div class="bg-[#0d2540] rounded-2xl border border-white/[0.07] p-6 text-left space-y-3 mb-8">
                    <h3 class="text-[13px] font-bold text-[#d4a02f] uppercase tracking-wider mb-3">Application Summary</h3>

                    <div class="flex justify-between text-[14px]">
                        <span class="text-gray-500">Name</span>
                        <span class="text-white font-medium">{{ user.first_name }} {{ user.last_name }}</span>
                    </div>
                    <div class="flex justify-between text-[14px]">
                        <span class="text-gray-500">Email</span>
                        <span class="text-white">{{ user.email }}</span>
                    </div>
                    <template v-if="order">
                        <div class="flex justify-between text-[14px]">
                            <span class="text-gray-500">Service</span>
                            <span class="text-white font-medium">{{ order.service_type }}</span>
                        </div>
                        <div class="flex justify-between text-[14px]">
                            <span class="text-gray-500">Company</span>
                            <span class="text-white">{{ order.entity_name }}</span>
                        </div>
                        <div class="flex justify-between text-[14px]">
                            <span class="text-gray-500">State</span>
                            <span class="text-white">{{ order.state }}</span>
                        </div>
                        <div class="flex justify-between text-[14px] border-t border-white/[0.07] pt-3 mt-2">
                            <span class="text-gray-400 font-medium">Order Total</span>
                            <span class="text-[#d4a02f] font-bold text-[16px]">${{ order.total_amount }}</span>
                        </div>
                    </template>
                </div>

                <!-- Status badge -->
                <div class="inline-flex items-center gap-2 rounded-full bg-amber-500/10 border border-amber-500/25 px-4 py-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="text-[13px] font-semibold text-amber-400">Pending Admin Approval</span>
                </div>

                <p class="mt-6 text-[13px] text-gray-600">
                    Need help? Email us at
                    <a href="mailto:support@corpius.net" class="text-[#d4a02f] hover:text-[#e6b84a]">support@corpius.net</a>
                </p>
            </template>

        </div>
    </div>
</template>
