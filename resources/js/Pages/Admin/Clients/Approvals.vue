<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pending:  Object,
    approved: Object,
    rejected: Object,
});

const activeTab = ref('pending');

const rejectModal = ref(false);
const rejectTarget = ref(null);
const rejectForm = useForm({ reason: '' });

function openReject(client) {
    rejectTarget.value = client;
    rejectForm.reset();
    rejectModal.value = true;
}

function approveClient(client) {
    if (!confirm(`Approve account for ${client.first_name} ${client.last_name}?`)) return;
    router.post(route('admin.client-approvals.approve', client.id), {}, {
        preserveScroll: true,
    });
}

function submitReject() {
    rejectForm.post(route('admin.client-approvals.reject', rejectTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { rejectModal.value = false; },
    });
}

function statusColor(status) {
    return {
        'pending_approval': 'text-amber-400 bg-amber-400/10',
        'approved':         'text-emerald-400 bg-emerald-400/10',
        'rejected':         'text-red-400 bg-red-400/10',
        'incomplete':       'text-gray-400 bg-gray-400/10',
        'order_pending':    'text-blue-400 bg-blue-400/10',
    }[status] ?? 'text-gray-400 bg-gray-400/10';
}
</script>

<template>
    <div class="min-h-screen bg-[#0b1e33] p-6">
        <Head title="Client Approvals | Admin" />

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[24px] font-extrabold text-white">Client Approvals</h1>
                <p class="text-[14px] text-gray-400 mt-0.5">Review and approve new client registrations</p>
            </div>
            <Link :href="route('admin.dashboard')" class="text-[13px] text-gray-500 hover:text-gray-300 transition-colors">
                ← Back to Dashboard
            </Link>
        </div>

        <!-- Stats row -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-[#0d2540] rounded-xl border border-white/[0.07] p-5">
                <p class="text-[12px] text-gray-500 uppercase tracking-wider mb-1">Pending</p>
                <p class="text-[32px] font-extrabold text-amber-400">{{ pending.total }}</p>
            </div>
            <div class="bg-[#0d2540] rounded-xl border border-white/[0.07] p-5">
                <p class="text-[12px] text-gray-500 uppercase tracking-wider mb-1">Approved</p>
                <p class="text-[32px] font-extrabold text-emerald-400">{{ approved.total }}</p>
            </div>
            <div class="bg-[#0d2540] rounded-xl border border-white/[0.07] p-5">
                <p class="text-[12px] text-gray-500 uppercase tracking-wider mb-1">Rejected</p>
                <p class="text-[32px] font-extrabold text-red-400">{{ rejected.total }}</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 bg-[#0d2540] rounded-xl border border-white/[0.07] p-1 w-fit mb-6">
            <button v-for="tab in [{id:'pending',label:'Pending',count:pending.total},{id:'approved',label:'Approved',count:approved.total},{id:'rejected',label:'Rejected',count:rejected.total}]"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="['px-4 py-2 rounded-lg text-[13px] font-semibold transition-all',
                    activeTab === tab.id ? 'bg-[#d4a02f] text-[#0b1e33]' : 'text-gray-400 hover:text-white']">
                {{ tab.label }}
                <span :class="['ml-1.5 text-[11px] font-bold', activeTab === tab.id ? 'text-[#0b1e33]/60' : 'text-gray-600']">({{ tab.count }})</span>
            </button>
        </div>

        <!-- ── PENDING TABLE ────────────────────────────────────────── -->
        <div v-if="activeTab === 'pending'" class="bg-[#0d2540] rounded-2xl border border-white/[0.07] overflow-hidden">
            <div v-if="pending.data.length === 0" class="p-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                No pending applications
            </div>
            <table v-else class="w-full">
                <thead class="border-b border-white/[0.07]">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Client</th>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Email</th>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Service</th>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Submitted</th>
                        <th class="text-right px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <tr v-for="client in pending.data" :key="client.id" class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#d4a02f]/10 border border-[#d4a02f]/20 flex items-center justify-center text-[13px] font-bold text-[#d4a02f]">
                                    {{ (client.first_name?.[0] ?? '') + (client.last_name?.[0] ?? '') }}
                                </div>
                                <div>
                                    <p class="text-[14px] font-semibold text-white">{{ client.first_name }} {{ client.last_name }}</p>
                                    <p class="text-[12px] text-gray-500">@{{ client.username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[14px] text-gray-300">{{ client.email }}</td>
                        <td class="px-5 py-4 text-[14px] text-gray-300">
                            <span v-if="client.orders && client.orders.length">{{ client.orders[0].service_type }}</span>
                            <span v-else class="text-gray-600">—</span>
                        </td>
                        <td class="px-5 py-4 text-[13px] text-gray-400">
                            {{ new Date(client.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('admin.client-approvals.show', client.id)"
                                    class="px-3 py-1.5 rounded-lg border border-white/10 text-[12px] text-gray-300 hover:bg-white/5 transition-colors">
                                    View
                                </Link>
                                <button @click="approveClient(client)"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-600/20 border border-emerald-500/30 text-[12px] text-emerald-400 hover:bg-emerald-600/30 transition-colors font-semibold">
                                    ✓ Approve
                                </button>
                                <button @click="openReject(client)"
                                    class="px-3 py-1.5 rounded-lg bg-red-600/10 border border-red-500/20 text-[12px] text-red-400 hover:bg-red-600/20 transition-colors font-semibold">
                                    ✕ Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── APPROVED TABLE ───────────────────────────────────────── -->
        <div v-if="activeTab === 'approved'" class="bg-[#0d2540] rounded-2xl border border-white/[0.07] overflow-hidden">
            <table class="w-full">
                <thead class="border-b border-white/[0.07]">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Client</th>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Email</th>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Approved At</th>
                        <th class="text-right px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <tr v-for="client in approved.data" :key="client.id" class="hover:bg-white/[0.02]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-emerald-500/10 flex items-center justify-center text-[13px] font-bold text-emerald-400">
                                    {{ (client.first_name?.[0] ?? '') + (client.last_name?.[0] ?? '') }}
                                </div>
                                <p class="text-[14px] font-semibold text-white">{{ client.first_name }} {{ client.last_name }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[14px] text-gray-300">{{ client.email }}</td>
                        <td class="px-5 py-4 text-[13px] text-gray-400">{{ client.approved_at ? new Date(client.approved_at).toLocaleDateString() : '—' }}</td>
                        <td class="px-5 py-4 text-right">
                            <Link :href="route('admin.client-approvals.show', client.id)"
                                class="px-3 py-1.5 rounded-lg border border-white/10 text-[12px] text-gray-300 hover:bg-white/5 transition-colors">
                                View
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── REJECTED TABLE ───────────────────────────────────────── -->
        <div v-if="activeTab === 'rejected'" class="bg-[#0d2540] rounded-2xl border border-white/[0.07] overflow-hidden">
            <table class="w-full">
                <thead class="border-b border-white/[0.07]">
                    <tr>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Client</th>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Email</th>
                        <th class="text-left px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Reason</th>
                        <th class="text-right px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <tr v-for="client in rejected.data" :key="client.id" class="hover:bg-white/[0.02]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-red-500/10 flex items-center justify-center text-[13px] font-bold text-red-400">
                                    {{ (client.first_name?.[0] ?? '') + (client.last_name?.[0] ?? '') }}
                                </div>
                                <p class="text-[14px] font-semibold text-white">{{ client.first_name }} {{ client.last_name }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[14px] text-gray-300">{{ client.email }}</td>
                        <td class="px-5 py-4 text-[13px] text-gray-400 max-w-xs truncate">{{ client.rejection_reason ?? '—' }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <Link :href="route('admin.client-approvals.show', client.id)"
                                    class="px-3 py-1.5 rounded-lg border border-white/10 text-[12px] text-gray-300 hover:bg-white/5 transition-colors">
                                    View
                                </Link>
                                <button @click="approveClient(client)"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-600/20 border border-emerald-500/30 text-[12px] text-emerald-400 hover:bg-emerald-600/30 transition-colors font-semibold">
                                    Re-Approve
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Reject Modal ───────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-[#0d2540] rounded-2xl border border-white/[0.10] p-6 w-full max-w-md shadow-xl">
                    <h3 class="text-[18px] font-bold text-white mb-1">Reject Application</h3>
                    <p class="text-[13px] text-gray-400 mb-4">
                        Rejecting <span class="text-white font-semibold">{{ rejectTarget?.first_name }} {{ rejectTarget?.last_name }}</span>.
                        Please provide a reason.
                    </p>
                    <textarea v-model="rejectForm.reason" rows="3"
                        placeholder="Reason for rejection…"
                        class="w-full rounded-xl border border-white/10 bg-white/5 p-3 text-[14px] text-white placeholder-gray-500 outline-none resize-none focus:border-red-500/40 focus:ring-1 focus:ring-red-500/20 transition-all" />
                    <p v-if="rejectForm.errors.reason" class="mt-1 text-[12px] text-red-400">{{ rejectForm.errors.reason }}</p>
                    <div class="flex gap-3 mt-4">
                        <button @click="rejectModal = false"
                            class="flex-1 h-10 rounded-xl border border-white/10 text-[14px] text-gray-400 hover:bg-white/5 transition-colors">
                            Cancel
                        </button>
                        <button @click="submitReject" :disabled="rejectForm.processing"
                            class="flex-1 h-10 rounded-xl bg-red-600 hover:bg-red-500 text-white text-[14px] font-bold transition-colors disabled:opacity-50">
                            {{ rejectForm.processing ? 'Rejecting…' : 'Confirm Reject' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>
