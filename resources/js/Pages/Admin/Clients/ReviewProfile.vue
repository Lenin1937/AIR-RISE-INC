<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ client: Object });

const rejectModal = ref(false);
const rejectReason = ref('');

function approve() {
    if (!confirm('Approve this account?')) return;
    router.post(route('admin.client-approvals.approve', props.client.id), {}, { preserveScroll: true });
}

function submitReject() {
    router.post(route('admin.client-approvals.reject', props.client.id), { reason: rejectReason.value }, {
        preserveScroll: true,
        onSuccess: () => { rejectModal.value = false; },
    });
}

function statusLabel(s) {
    return { pending_approval: 'Pending', approved: 'Approved', rejected: 'Rejected' }[s] ?? s;
}
function statusColor(s) {
    return { pending_approval: 'text-amber-400 bg-amber-400/10', approved: 'text-emerald-400 bg-emerald-400/10', rejected: 'text-red-400 bg-red-400/10' }[s] ?? 'text-gray-400 bg-gray-400/10';
}
</script>

<template>
    <div class="min-h-screen bg-[#0b1e33] p-6">
        <Head :title="`Review – ${client.full_name} | Admin`" />

        <div class="max-w-3xl mx-auto">
            <!-- Back -->
            <Link :href="route('admin.client-approvals.index')" class="text-[13px] text-gray-500 hover:text-gray-300 transition-colors inline-flex items-center gap-1 mb-6">
                ← Back to Approvals
            </Link>

            <!-- Header card -->
            <div class="bg-[#0d2540] rounded-2xl border border-white/[0.07] p-6 mb-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-[#d4a02f]/10 border border-[#d4a02f]/20 flex items-center justify-center text-[22px] font-bold text-[#d4a02f]">
                            {{ (client.first_name?.[0] ?? '') + (client.last_name?.[0] ?? '') }}
                        </div>
                        <div>
                            <h1 class="text-[20px] font-extrabold text-white">{{ client.full_name }}</h1>
                            <p class="text-[14px] text-gray-400">{{ client.email }}</p>
                            <p class="text-[13px] text-gray-600">@{{ client.username }}</p>
                        </div>
                    </div>
                    <span :class="['text-[12px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wider', statusColor(client.registration_status)]">
                        {{ statusLabel(client.registration_status) }}
                    </span>
                </div>

                <!-- Action buttons -->
                <div v-if="client.registration_status === 'pending_approval'" class="flex gap-3 mt-5">
                    <button @click="approve"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[14px] font-bold transition-colors">
                        ✓ Approve Account
                    </button>
                    <button @click="rejectModal = true"
                        class="px-5 py-2.5 rounded-xl bg-red-600/10 border border-red-500/30 hover:bg-red-600/20 text-red-400 text-[14px] font-bold transition-colors">
                        ✕ Reject
                    </button>
                </div>
                <div v-else-if="client.registration_status === 'rejected'" class="mt-4">
                    <button @click="approve"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[14px] font-bold transition-colors">
                        Re-Approve Account
                    </button>
                </div>
            </div>

            <!-- Details grid -->
            <div class="grid grid-cols-2 gap-4 mb-5">
                <!-- Personal Info -->
                <div class="bg-[#0d2540] rounded-2xl border border-white/[0.07] p-5">
                    <h3 class="text-[12px] font-bold text-[#d4a02f] uppercase tracking-wider mb-4">Personal Info</h3>
                    <dl class="space-y-2.5">
                        <div v-for="row in [
                            {label: 'Phone',    val: client.phone},
                            {label: 'Telegram', val: client.telegram_username ? '@' + client.telegram_username : null},
                            {label: 'Company',  val: client.company_name},
                        ]" :key="row.label" class="flex justify-between text-[13px]">
                            <dt class="text-gray-500">{{ row.label }}</dt>
                            <dd class="text-white font-medium">{{ row.val ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between text-[13px]">
                            <dt class="text-gray-500">Registered</dt>
                            <dd class="text-white">{{ new Date(client.created_at).toLocaleDateString() }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Address -->
                <div class="bg-[#0d2540] rounded-2xl border border-white/[0.07] p-5">
                    <h3 class="text-[12px] font-bold text-[#d4a02f] uppercase tracking-wider mb-4">Address</h3>
                    <dl class="space-y-2.5">
                        <div v-for="row in [
                            {label: 'Street',  val: client.address_line_1},
                            {label: 'Suite',   val: client.address_line_2},
                            {label: 'City',    val: client.city},
                            {label: 'State',   val: client.state},
                            {label: 'ZIP',     val: client.zip_code},
                            {label: 'Country', val: client.country},
                        ]" :key="row.label" class="flex justify-between text-[13px]">
                            <dt class="text-gray-500">{{ row.label }}</dt>
                            <dd class="text-white">{{ row.val ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Orders -->
            <div v-if="client.orders && client.orders.length" class="bg-[#0d2540] rounded-2xl border border-white/[0.07] p-5">
                <h3 class="text-[12px] font-bold text-[#d4a02f] uppercase tracking-wider mb-4">Orders</h3>
                <div v-for="order in client.orders" :key="order.id"
                    class="flex items-center justify-between py-3 border-b border-white/[0.05] last:border-0">
                    <div>
                        <p class="text-[14px] font-semibold text-white">{{ order.service_type }}</p>
                        <p class="text-[12px] text-gray-500">{{ order.entity_name }} · {{ order.state }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[15px] font-bold text-[#d4a02f]">${{ order.total_amount }}</p>
                        <p class="text-[12px] text-gray-500">{{ order.payment_status }}</p>
                    </div>
                </div>
            </div>

            <!-- Rejection reason display -->
            <div v-if="client.registration_status === 'rejected' && client.rejection_reason"
                class="mt-4 rounded-xl border border-red-500/20 bg-red-500/5 p-4">
                <p class="text-[12px] font-bold uppercase tracking-wider text-red-400 mb-1">Rejection Reason</p>
                <p class="text-[14px] text-gray-300">{{ client.rejection_reason }}</p>
            </div>
        </div>

        <!-- Reject Modal -->
        <Teleport to="body">
            <div v-if="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-[#0d2540] rounded-2xl border border-white/[0.10] p-6 w-full max-w-md">
                    <h3 class="text-[18px] font-bold text-white mb-3">Reject Application</h3>
                    <textarea v-model="rejectReason" rows="3" placeholder="Reason for rejection…"
                        class="w-full rounded-xl border border-white/10 bg-white/5 p-3 text-[14px] text-white placeholder-gray-500 outline-none resize-none focus:border-red-500/40 transition-all" />
                    <div class="flex gap-3 mt-4">
                        <button @click="rejectModal = false"
                            class="flex-1 h-10 rounded-xl border border-white/10 text-gray-400 hover:bg-white/5 transition-colors text-[14px]">
                            Cancel
                        </button>
                        <button @click="submitReject"
                            class="flex-1 h-10 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-[14px] transition-colors">
                            Confirm Reject
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
