<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    segments: Array,
    stats: Object,
});

const del = (id) => { if (!confirm('Delete segment?')) return; router.delete(route('admin.email.segments.destroy', id), { preserveScroll: true }); };

/* Create modal */
const showCreate = ref(false);
const previewCount = ref(null);
const previewLoading = ref(false);

const createForm = useForm({
    name: '', description: '', auto_update: true,
    conditions: [{ field: 'client_type', operator: 'is', value: 'prospect', logic: 'AND' }],
});

const fieldOptions = ['client_type','service_type','country','language','source','subscribed_marketing'];
const operatorOptions = ['is','is_not','contains','not_contains','is_empty','is_not_empty'];

const addCondition = () => createForm.conditions.push({ field: 'client_type', operator: 'is', value: '', logic: 'AND' });
const removeCondition = (i) => createForm.conditions.splice(i, 1);

const previewSegment = async () => {
    previewLoading.value = true; previewCount.value = null;
    try {
        const { data } = await axios.post(route('admin.email.segments.preview'), { conditions: createForm.conditions });
        previewCount.value = data.count;
    } catch { previewCount.value = '?' }
    previewLoading.value = false;
};

const submitCreate = () => createForm.post(route('admin.email.segments.store'), {
    onSuccess: () => { showCreate.value = false; createForm.reset(); previewCount.value = null; }
});
</script>

<template>
    <Head title="Audience Segments" />
    <AdminLayout>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Audience Segments</h1>
                <p class="text-sm text-gray-500 mt-1">Dynamic contact segments for targeted campaigns</p>
            </div>
            <button @click="showCreate = true" class="inline-flex items-center gap-2 bg-amber-400 text-[#0b1e33] hover:bg-amber-300 rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Segment
            </button>
        </div>

        <!-- KPI -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div v-for="(card, i) in [
                { label: 'Total Segments', value: stats.total, color: 'blue' },
                { label: 'Auto-Update', value: stats.auto_update, color: 'green' },
                { label: 'Total Contacts', value: stats.total_contacts.toLocaleString(), color: 'amber' },
            ]" :key="i" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
               style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div :class="`absolute inset-0 bg-gradient-to-br from-${card.color}-500/5 to-transparent pointer-events-none`"/>
                <div :class="`absolute bottom-0 left-0 right-0 h-[2px] bg-${card.color}-500/50 rounded-b-2xl`"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-2">{{ card.label }}</p>
                <p class="text-2xl font-bold text-white tabular-nums">{{ card.value }}</p>
            </div>
        </div>

        <!-- Segments grid -->
        <div v-if="segments.length === 0" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-16 text-center text-gray-600 text-sm">
            No segments yet. Create one to target specific contacts.
        </div>
        <div v-else class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <div v-for="s in segments" :key="s.id" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3 hover:border-white/[0.12] transition">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[14px] font-bold text-white">{{ s.name }}</p>
                    <span v-if="s.auto_update" class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-green-500/15 text-green-400 shrink-0">Auto</span>
                </div>
                <p v-if="s.description" class="text-[12px] text-gray-500">{{ s.description }}</p>
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="text-2xl font-bold text-white tabular-nums">{{ s.contact_count.toLocaleString() }}</span>
                    <span class="text-gray-500">contacts</span>
                </div>
                <div class="flex items-center gap-2 text-[10px] text-gray-600 flex-wrap">
                    <span v-for="(c, i) in s.conditions.slice(0,2)" :key="i" class="bg-white/[0.04] rounded px-2 py-0.5">{{ c.field }} {{ c.operator }} {{ c.value }}</span>
                    <span v-if="s.conditions.length > 2" class="text-gray-600">+{{ s.conditions.length - 2 }} more</span>
                </div>
                <div class="flex items-center gap-2 mt-auto pt-2 border-t border-white/[0.06]">
                    <span class="text-[10px] text-gray-600 flex-1">{{ s.created_at }}</span>
                    <button @click="del(s.id)" class="text-[11px] text-gray-600 hover:text-red-400 transition font-medium">Delete</button>
                </div>
            </div>
        </div>

        <!-- Create Segment Modal -->
        <div v-if="showCreate" class="fixed inset-0 z-50 flex items-start justify-center pt-12 pb-6 px-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div class="w-full max-w-xl rounded-2xl border border-white/[0.08] bg-[#0c1c30] shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.06]">
                    <h3 class="text-[15px] font-bold text-white">New Segment</h3>
                    <button @click="showCreate = false" class="text-gray-600 hover:text-white transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Segment Name *</label>
                        <input v-model="createForm.name" required type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Description</label>
                        <input v-model="createForm.description" type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                    </div>

                    <!-- Conditions builder -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[12px] font-bold text-white">Conditions</label>
                            <button type="button" @click="addCondition" class="text-[11px] text-amber-400 hover:text-amber-300 font-semibold transition">+ Add</button>
                        </div>
                        <div v-for="(c, i) in createForm.conditions" :key="i" class="flex flex-wrap items-center gap-2 mb-2">
                            <select v-if="i > 0" v-model="c.logic" class="bg-[#0a1628] border border-white/[0.08] text-white text-[11px] rounded-lg px-2 h-8 focus:outline-none w-14">
                                <option>AND</option><option>OR</option>
                            </select>
                            <span v-else class="w-14 text-center text-[11px] text-gray-600">WHERE</span>
                            <select v-model="c.field" class="bg-[#0a1628] border border-white/[0.08] text-white text-[11px] rounded-lg px-2 h-8 focus:outline-none flex-1">
                                <option v-for="f in fieldOptions" :key="f" :value="f">{{ f }}</option>
                            </select>
                            <select v-model="c.operator" class="bg-[#0a1628] border border-white/[0.08] text-white text-[11px] rounded-lg px-2 h-8 focus:outline-none w-28">
                                <option v-for="o in operatorOptions" :key="o" :value="o">{{ o }}</option>
                            </select>
                            <input v-if="!['is_empty','is_not_empty'].includes(c.operator)" v-model="c.value" type="text" class="bg-white/[0.04] border border-white/[0.08] text-white text-[11px] rounded-lg px-2 h-8 focus:outline-none flex-1"/>
                            <button v-if="createForm.conditions.length > 1" type="button" @click="removeCondition(i)" class="text-gray-600 hover:text-red-400 transition text-[11px]">×</button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="createForm.auto_update" type="checkbox" class="rounded border-white/20 bg-white/[0.04] text-amber-400 focus:ring-amber-400/40"/>
                        <span class="text-[13px] text-gray-300">Auto-update contact count</span>
                    </label>

                    <!-- Preview count -->
                    <div class="flex items-center gap-3">
                        <button type="button" @click="previewSegment" :disabled="previewLoading" class="text-[12px] text-blue-400 hover:text-blue-300 font-semibold transition disabled:opacity-50">
                            {{ previewLoading ? 'Counting…' : 'Preview count' }}
                        </button>
                        <span v-if="previewCount !== null" class="text-[12px] text-white font-bold">{{ previewCount }} contacts match</span>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreate = false" class="px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-gray-300 hover:text-white text-[13px] font-semibold transition">Cancel</button>
                        <button type="submit" :disabled="createForm.processing" class="px-5 h-9 rounded-lg bg-amber-400 text-[#0b1e33] hover:bg-amber-300 text-[13px] font-semibold transition disabled:opacity-60">Create Segment</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
