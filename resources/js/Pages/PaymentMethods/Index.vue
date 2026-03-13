<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { __ } = useTranslations();

defineProps({
    paymentMethods: Array,
    recent_payments: Array,
    stats: Object,
});

const showModal = ref(false);
const form = ref({
    cardholder_name:'', card_number:'', expiry_month:'01', expiry_year: String(new Date().getFullYear()),
    cvc:'', billing_address:{ line1:'', line2:'', city:'', state:'', postal_code:'' },
    is_default: false
});

const months = ['01','02','03','04','05','06','07','08','09','10','11','12'];
const years = Array.from({length:10}, (_,i) => String(new Date().getFullYear()+i));

const formatCurrency = (n) => new Intl.NumberFormat('en-US', { style:'currency', currency:'USD' }).format(n || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : '—';

const getPaymentStatusColor = (s) => {
    if (s === 'succeeded' || s === 'completed') return 'bg-green-400/10 border-green-400/20 text-green-400';
    if (s === 'failed' || s === 'cancelled') return 'bg-red-400/10 border-red-400/20 text-red-400';
    if (s === 'pending' || s === 'processing') return 'bg-amber-400/10 border-amber-400/20 text-amber-400';
    return 'bg-gray-400/10 border-gray-400/20 text-gray-400';
};

const handleDelete = (pm) => {
    if (confirm('Remove this payment method?'))
        router.delete(route('payment-methods.destroy', pm.id));
};
const handleSetDefault = (pm) => router.patch(route('payment-methods.set-default', pm.id));

const submitNewPaymentMethod = () => {
    router.post(route('payment-methods.store'), form.value, {
        onSuccess: () => { showModal.value = false; form.value = { cardholder_name:'', card_number:'', expiry_month:'01', expiry_year: String(new Date().getFullYear()), cvc:'', billing_address:{line1:'',line2:'',city:'',state:'',postal_code:''}, is_default:false }; },
    });
};

const brandIcon = (brand) => {
    if (!brand) return '💳';
    brand = brand.toLowerCase();
    if (brand.includes('visa')) return '💙';
    if (brand.includes('master')) return '🟠';
    if (brand.includes('amex')) return '💚';
    return '💳';
};
</script>

<template>
    <Head :title="__('client.payment_methods')" />
    <AuthenticatedLayout>
        <div class="space-y-7">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-[22px] font-bold text-white tracking-tight">{{ __('Payment Methods') }}</h1>
                    <p class="mt-0.5 text-[13px] text-gray-400">{{ __('Manage your payment methods') }}</p>
                </div>
                <button @click="showModal = true" class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20">
                    <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Payment Method') }}
                </button>
            </div>  

            <!-- KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(59,130,246,.10)">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/[0.07] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-blue-500/60 to-transparent"/>
                    <div class="w-9 h-9 rounded-xl bg-blue-500/20 flex items-center justify-center mb-3">
                        <svg style="width:18px;height:18px" class="text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ paymentMethods?.length ?? 0 }}</div>
                    <div class="mt-1 text-[12px] text-gray-400 font-medium">{{ __('Saved Cards') }}</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(34,197,94,.10)">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/[0.07] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-green-500/60 to-transparent"/>
                    <div class="w-9 h-9 rounded-xl bg-green-500/20 flex items-center justify-center mb-3">
                        <svg style="width:18px;height:18px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ stats?.total_payments ?? 0 }}</div>
                    <div class="mt-1 text-[12px] text-gray-400 font-medium">{{ __('Successful Payments') }}</div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5" style="box-shadow:0 0 28px 0 rgba(251,191,36,.10)">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-400/[0.07] to-transparent pointer-events-none"/>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-amber-400/60 to-transparent"/>
                    <div class="w-9 h-9 rounded-xl bg-amber-400/20 flex items-center justify-center mb-3">
                        <svg style="width:18px;height:18px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ formatCurrency(stats?.total_spent) }}</div>
                    <div class="mt-1 text-[12px] text-gray-400 font-medium">{{ __('Total Spent') }}</div>
                </div>
            </div>

            <!-- 2-col grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Payment Methods -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06]">
                        <h2 class="text-[14px] font-bold text-white">{{ __('Saved Payment Methods') }}</h2>
                    </div>
                    <div class="p-5">
                        <div v-if="paymentMethods?.length" class="space-y-3">
                            <div v-for="pm in paymentMethods" :key="pm.id" class="flex items-center gap-4 p-4 rounded-xl border border-white/[0.06] bg-white/[0.02] hover:border-white/[0.1] transition">
                                <div class="w-11 h-11 rounded-xl bg-white/[0.04] flex items-center justify-center text-xl flex-shrink-0">
                                    {{ brandIcon(pm.brand) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-[13px] font-semibold text-white">•••• {{ pm.last4 ?? '****' }}</p>
                                        <span v-if="pm.is_default" class="text-[10px] font-bold rounded-full px-2 py-0.5 bg-amber-400 text-[#07101e]">Default</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500">{{ pm.brand?.toUpperCase() }} · Expires {{ pm.exp_month }}/{{ pm.exp_year }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button v-if="!pm.is_default" @click="handleSetDefault(pm)" class="text-[11px] text-gray-400 hover:text-amber-400 transition font-semibold">Set default</button>
                                    <button @click="handleDelete(pm)" class="w-7 h-7 rounded-lg bg-red-400/10 flex items-center justify-center text-red-400 hover:bg-red-400/20 transition">
                                        <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center">
                            <div class="text-3xl mb-3">💳</div>
                            <p class="text-[13px] font-semibold text-white mb-1">{{ __('No payment methods') }}</p>
                            <p class="text-[12px] text-gray-500">{{ __('You have no payment methods at the moment.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06]">
                        <h2 class="text-[14px] font-bold text-white">{{ __('Recent Payments') }}</h2>
                    </div>
                    <div class="p-5">
                        <div v-if="recent_payments?.length" class="space-y-3">
                            <div v-for="pay in recent_payments" :key="pay.id" class="flex items-center justify-between p-3.5 rounded-xl border border-white/[0.05] bg-white/[0.02] hover:border-white/[0.08] transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-green-400/10 flex items-center justify-center">
                                        <svg style="width:13px;height:13px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-gray-200">{{ pay.description || __('Payment') }}</p>
                                        <p class="text-[11px] text-gray-500">{{ formatDate(pay.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[14px] font-bold text-white">{{ formatCurrency(pay.amount) }}</p>
                                    <span :class="['inline-flex text-[10px] font-bold rounded-full px-2 py-0.5 border', getPaymentStatusColor(pay.status)]">{{ pay.status }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center">
                            <p class="text-[13px] font-semibold text-white mb-1">{{ __('No recent payments') }}</p>
                            <p class="text-[12px] text-gray-500">{{ __('You have no recent payments at the moment.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Payment Method Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4" @click.self="showModal=false">
            <div class="w-full max-w-lg rounded-2xl bg-[#0c1c30] border border-white/[0.08] overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.06]">
                    <h3 class="text-[16px] font-bold text-white">{{ __('Add Payment Method') }}</h3>
                    <button @click="showModal=false" class="w-8 h-8 rounded-lg bg-white/[0.04] flex items-center justify-center text-gray-400 hover:text-white transition">
                        <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5 max-h-[78vh] overflow-y-auto">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Cardholder Name</label>
                            <input v-model="form.cardholder_name" type="text" placeholder="John Doe"
                                class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Card Number</label>
                            <input v-model="form.card_number" type="text" placeholder="1234 5678 9012 3456" maxlength="19"
                                class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Month</label>
                                <select v-model="form.expiry_month" class="w-full h-10 px-3 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-200 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition">
                                    <option v-for="m in months" :key="m" :value="m">{{ m }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Year</label>
                                <select v-model="form.expiry_year" class="w-full h-10 px-3 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-200 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition">
                                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">CVC</label>
                                <input v-model="form.cvc" type="text" placeholder="123" maxlength="4"
                                    class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Billing Address</label>
                            <div class="space-y-2">
                                <input v-model="form.billing_address.line1" type="text" placeholder="Address line 1"
                                    class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                <input v-model="form.billing_address.line2" type="text" placeholder="Apt, suite, etc. (optional)"
                                    class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                <div class="grid grid-cols-3 gap-2">
                                    <input v-model="form.billing_address.city" type="text" placeholder="City"
                                        class="h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    <input v-model="form.billing_address.state" type="text" placeholder="State"
                                        class="h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    <input v-model="form.billing_address.postal_code" type="text" placeholder="ZIP"
                                        class="h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                </div>
                            </div>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="form.is_default" type="checkbox" class="w-4 h-4 rounded border-white/20 accent-amber-400"/>
                            <span class="text-[13px] text-gray-300">Set as default payment method</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-white/[0.06]">
                    <button @click="showModal=false" class="h-9 px-5 rounded-xl border border-white/[0.08] bg-white/[0.03] text-[13px] font-semibold text-gray-300 hover:text-white transition">Cancel</button>
                    <button @click="submitNewPaymentMethod" class="h-9 px-6 rounded-xl bg-amber-400 text-[13px] font-semibold text-[#07101e] hover:bg-amber-300 transition shadow-lg shadow-amber-400/20">Add Card</button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
