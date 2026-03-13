<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    automations: Array,
    stats: Object,
});

const statusColor = (s) => ({
    active: 'bg-green-500/15 text-green-400',
    paused: 'bg-amber-500/15 text-amber-400',
    draft:  'bg-gray-500/15 text-gray-400',
})[s] ?? 'bg-gray-500/15 text-gray-400';

const triggerLabel = (t) => ({
    new_contact:           'New Contact',
    order_created:         'Order Created',
    order_status_changed:  'Order Status Changed',
    document_uploaded:     'Document Uploaded',
    manual:                'Manual',
    tag_added:             'Tag Added',
    date_based:            'Date Based',
})[t] ?? t;

const toggle = (id) => router.post(route('admin.email.automations.toggle', id), {}, { preserveScroll: true });
const del    = (id) => { if (!confirm('Delete this automation?')) return; router.delete(route('admin.email.automations.destroy', id), { preserveScroll: true }); };

/* Create modal */
const showCreate = ref(false);
const createForm = useForm({
    name: '', description: '', trigger: 'new_contact',
    steps: [{ delay_days: 0, subject: '', body_html: '' }],
});
const addStep = () => createForm.steps.push({ delay_days: createForm.steps.length * 2, subject: '', body_html: '' });
const removeStep = (i) => createForm.steps.splice(i, 1);
const submitCreate = () => createForm.post(route('admin.email.automations.store'), { onSuccess: () => { showCreate.value = false; createForm.reset(); } });
</script>

<template>
    <Head title="Email Automations" />
    <AdminLayout>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Automations</h1>
                <p class="text-sm text-gray-500 mt-1">Trigger-based email sequences</p>
            </div>
            <button @click="showCreate = true" class="inline-flex items-center gap-2 bg-amber-400 text-[#0b1e33] hover:bg-amber-300 rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Automation
            </button>
        </div>

        <!-- KPI -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="(card, i) in [
                { label: 'Total', value: stats.total, color: 'blue' },
                { label: 'Active', value: stats.active, color: 'green' },
                { label: 'Paused', value: stats.paused, color: 'amber' },
                { label: 'Enrolled', value: stats.enrolled.toLocaleString(), color: 'purple' },
            ]" :key="i" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
               style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div :class="`absolute inset-0 bg-gradient-to-br from-${card.color}-500/5 to-transparent pointer-events-none`"/>
                <div :class="`absolute bottom-0 left-0 right-0 h-[2px] bg-${card.color}-500/50 rounded-b-2xl`"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-2">{{ card.label }}</p>
                <p class="text-2xl font-bold text-white tabular-nums">{{ card.value }}</p>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600">Automation</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden sm:table-cell">Trigger</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden md:table-cell">Steps</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden lg:table-cell">Enrolled</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600">Status</th>
                        <th class="px-5 py-3 text-right text-[10px] uppercase tracking-widest text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="automations.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-gray-600 text-sm">No automations yet. Create one to get started.</td>
                    </tr>
                    <tr v-for="a in automations" :key="a.id" class="border-t border-white/[0.04] hover:bg-white/[0.03] transition">
                        <td class="px-5 py-3.5">
                            <p class="text-[13px] font-semibold text-white">{{ a.name }}</p>
                            <p v-if="a.description" class="text-[11px] text-gray-500 mt-0.5 truncate max-w-[220px]">{{ a.description }}</p>
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-blue-500/15 text-blue-400">{{ triggerLabel(a.trigger) }}</span>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell text-[12px] text-gray-400">{{ a.step_count }} step{{ a.step_count !== 1 ? 's' : '' }}</td>
                        <td class="px-5 py-3.5 hidden lg:table-cell text-[12px] text-gray-400">{{ a.enrolled_count.toLocaleString() }}</td>
                        <td class="px-5 py-3.5">
                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold', statusColor(a.status)]">{{ a.status }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="toggle(a.id)" class="text-[11px] text-amber-400 hover:text-amber-300 transition font-semibold">
                                    {{ a.status === 'active' ? 'Pause' : 'Activate' }}
                                </button>
                                <button @click="del(a.id)" class="text-[11px] text-gray-600 hover:text-red-400 transition font-medium">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create Automation Modal -->
        <div v-if="showCreate" class="fixed inset-0 z-50 flex items-start justify-center pt-12 pb-6 px-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div class="w-full max-w-2xl rounded-2xl border border-white/[0.08] bg-[#0c1c30] shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.06]">
                    <h3 class="text-[15px] font-bold text-white">New Automation</h3>
                    <button @click="showCreate = false" class="text-gray-600 hover:text-white transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form @submit.prevent="submitCreate" class="p-6 space-y-5">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Name *</label>
                            <input v-model="createForm.name" required type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Trigger *</label>
                            <select v-model="createForm.trigger" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40">
                                <option value="new_contact">New Contact</option>
                                <option value="order_created">Order Created</option>
                                <option value="order_status_changed">Order Status Changed</option>
                                <option value="document_uploaded">Document Uploaded</option>
                                <option value="manual">Manual</option>
                                <option value="tag_added">Tag Added</option>
                                <option value="date_based">Date Based</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Description</label>
                        <input v-model="createForm.description" type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                    </div>

                    <!-- Steps -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-[12px] font-bold text-white">Email Steps</label>
                            <button type="button" @click="addStep" class="text-[11px] text-amber-400 hover:text-amber-300 font-semibold transition">+ Add Step</button>
                        </div>
                        <div v-for="(step, i) in createForm.steps" :key="i" class="rounded-xl border border-white/[0.06] bg-[#0a1628] p-4 mb-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-[12px] font-bold text-white">Step {{ i + 1 }}</p>
                                <button v-if="createForm.steps.length > 1" type="button" @click="removeStep(i)" class="text-[11px] text-gray-600 hover:text-red-400 transition">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] text-gray-500 mb-1">Send after (days)</label>
                                    <input v-model.number="step.delay_days" type="number" min="0" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-8 focus:outline-none focus:border-amber-400/40"/>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[11px] text-gray-500 mb-1">Subject *</label>
                                    <input v-model="step.subject" required type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-8 focus:outline-none focus:border-amber-400/40"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-1">HTML Body *</label>
                                <textarea v-model="step.body_html" required rows="4" class="w-full bg-[#070f1c] border border-white/[0.08] text-gray-200 text-[11px] font-mono rounded-xl px-3 py-2 focus:outline-none focus:border-amber-400/30 resize-none"/>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreate = false" class="px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-gray-300 hover:text-white text-[13px] font-semibold transition">Cancel</button>
                        <button type="submit" :disabled="createForm.processing" class="px-5 h-9 rounded-lg bg-amber-400 text-[#0b1e33] hover:bg-amber-300 text-[13px] font-semibold transition disabled:opacity-60">Create Automation</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
