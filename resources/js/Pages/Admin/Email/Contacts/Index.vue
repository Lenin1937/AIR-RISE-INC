<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    contacts: Object,
    stats: Object,
    filters: Object,
});

const search     = ref(props.filters?.search ?? '');
const clientType = ref(props.filters?.client_type ?? '');
const serviceType= ref(props.filters?.service_type ?? '');
const subscribed = ref(props.filters?.subscribed ?? '');

let searchTimer = null;
watch([search, clientType, serviceType, subscribed], () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.email.contacts.index'), {
            search: search.value || undefined,
            client_type: clientType.value || undefined,
            service_type: serviceType.value || undefined,
            subscribed: subscribed.value || undefined,
        }, { preserveScroll: true, preserveState: true });
    }, 400);
});

/* Add contact modal */
const showAdd = ref(false);
const addForm = useForm({ email: '', first_name: '', last_name: '', client_type: '', subscribed_marketing: true });
const submitAdd = () => addForm.post(route('admin.email.contacts.store'), {
    onSuccess: () => { showAdd.value = false; addForm.reset(); }
});

/* CSV import */
const importInput = ref(null);
const importForm = useForm({ file: null });
const handleImport = (e) => {
    importForm.file = e.target.files[0];
    importForm.post(route('admin.email.contacts.import'), {
        onSuccess: () => { importInput.value.value = ''; }
    });
};

const deleteContact = (id) => {
    if (!confirm('Delete this contact?')) return;
    router.delete(route('admin.email.contacts.destroy', id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Email Contacts" />
    <AdminLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Email Contacts</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your marketing contact list</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <input ref="importInput" type="file" accept=".csv,.txt" class="hidden" @change="handleImport" />
                <button @click="importInput.click()" class="inline-flex items-center gap-2 border border-white/[0.10] bg-white/[0.04] text-gray-300 hover:text-white hover:bg-white/[0.08] rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import CSV
                </button>
                <button @click="showAdd = true" class="inline-flex items-center gap-2 bg-amber-400 text-[#0b1e33] hover:bg-amber-300 rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Contact
                </button>
            </div>
        </div>

        <!-- KPI -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="(card, i) in [
                { label: 'Total', value: stats.total, color: 'blue' },
                { label: 'Subscribed', value: stats.subscribed, color: 'green' },
                { label: 'Unsubscribed', value: stats.unsubscribed, color: 'red' },
                { label: 'Prospects', value: stats.prospects, color: 'amber' },
            ]" :key="i" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 relative overflow-hidden"
               style="box-shadow: 0 4px 24px 0 rgba(0,0,0,.35)">
                <div :class="`absolute inset-0 bg-gradient-to-br from-${card.color}-500/5 to-transparent pointer-events-none`"/>
                <div :class="`absolute bottom-0 left-0 right-0 h-[2px] bg-${card.color}-500/50 rounded-b-2xl`"/>
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-600 mb-2">{{ card.label }}</p>
                <p class="text-2xl font-bold text-white tabular-nums">{{ card.value.toLocaleString() }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mb-5">
            <input v-model="search" type="text" placeholder="Search email or name…"
                class="bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 w-full sm:w-72 focus:outline-none focus:border-amber-400/40 placeholder-gray-600 transition"/>
            <select v-model="clientType" class="bg-white/[0.04] border border-white/[0.08] text-gray-300 text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40 transition">
                <option value="">All Types</option>
                <option value="prospect">Prospect</option>
                <option value="active">Active Client</option>
                <option value="past">Past Client</option>
                <option value="internal">Internal</option>
            </select>
            <select v-model="subscribed" class="bg-white/[0.04] border border-white/[0.08] text-gray-300 text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40 transition">
                <option value="">All Subscriptions</option>
                <option value="1">Subscribed</option>
                <option value="0">Unsubscribed</option>
            </select>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600">Name / Email</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden sm:table-cell">Type</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600 hidden md:table-cell">Source</th>
                        <th class="px-5 py-3 text-left text-[10px] uppercase tracking-widest text-gray-600">Status</th>
                        <th class="px-5 py-3 text-right text-[10px] uppercase tracking-widest text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="contacts.data.length === 0">
                        <td colspan="5" class="px-5 py-10 text-center text-gray-600 text-sm">No contacts found.</td>
                    </tr>
                    <tr v-for="c in contacts.data" :key="c.id" class="border-t border-white/[0.04] hover:bg-white/[0.03] transition">
                        <td class="px-5 py-3.5">
                            <p class="text-[13px] font-semibold text-white">{{ c.first_name || c.last_name ? `${c.first_name ?? ''} ${c.last_name ?? ''}`.trim() : '—' }}</p>
                            <p class="text-[11px] text-gray-500">{{ c.email }}</p>
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            <span class="text-[12px] text-gray-400 capitalize">{{ c.client_type ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell text-[12px] text-gray-500">{{ c.source ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold', c.subscribed_marketing ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400']">
                                {{ c.subscribed_marketing ? 'Subscribed' : 'Unsubscribed' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <button @click="deleteContact(c.id)" class="text-gray-600 hover:text-red-400 transition text-xs font-medium">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination -->
            <div v-if="contacts.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-white/[0.06]">
                <p class="text-[12px] text-gray-600">{{ contacts.from }}–{{ contacts.to }} of {{ contacts.total }}</p>
                <div class="flex gap-1">
                    <Link v-if="contacts.prev_page_url" :href="contacts.prev_page_url"
                        class="px-3 h-7 rounded-lg border border-white/[0.07] text-[12px] text-gray-400 hover:text-white hover:bg-white/[0.06] transition flex items-center">Prev</Link>
                    <Link v-if="contacts.next_page_url" :href="contacts.next_page_url"
                        class="px-3 h-7 rounded-lg border border-white/[0.07] text-[12px] text-gray-400 hover:text-white hover:bg-white/[0.06] transition flex items-center">Next</Link>
                </div>
            </div>
        </div>

        <!-- Add Contact Modal -->
        <div v-if="showAdd" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div class="w-full max-w-md rounded-2xl border border-white/[0.08] bg-[#0c1c30] shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.06]">
                    <h3 class="text-[15px] font-bold text-white">Add Contact</h3>
                    <button @click="showAdd = false" class="text-gray-600 hover:text-white transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form @submit.prevent="submitAdd" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Email *</label>
                        <input v-model="addForm.email" type="email" required class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        <p v-if="addForm.errors.email" class="text-red-400 text-xs mt-1">{{ addForm.errors.email }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">First Name</label>
                            <input v-model="addForm.first_name" type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Last Name</label>
                            <input v-model="addForm.last_name" type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Client Type</label>
                        <select v-model="addForm.client_type" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40">
                            <option value="">Select…</option>
                            <option value="prospect">Prospect</option>
                            <option value="active">Active Client</option>
                            <option value="past">Past Client</option>
                            <option value="internal">Internal</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="addForm.subscribed_marketing" type="checkbox" class="rounded border-white/20 bg-white/[0.04] text-amber-400 focus:ring-amber-400/40"/>
                        <span class="text-[13px] text-gray-300">Subscribe to marketing emails</span>
                    </label>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showAdd = false" class="px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-gray-300 hover:text-white text-[13px] font-semibold transition">Cancel</button>
                        <button type="submit" :disabled="addForm.processing" class="px-5 h-9 rounded-lg bg-amber-400 text-[#0b1e33] hover:bg-amber-300 text-[13px] font-semibold transition disabled:opacity-60">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
