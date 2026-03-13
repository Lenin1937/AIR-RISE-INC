<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    document: { type: Object, required: true },
});

const statusForm = ref({
    status:      props.document.status,
    admin_notes: props.document.admin_notes || '',
});

const updating = ref(false);

const downloadDocument = () => window.open(`/admin/documents/${props.document.id}/download`, '_blank');

const updateStatus = () => {
    updating.value = true;
    router.patch(`/admin/documents/${props.document.id}/status`, statusForm.value, {
        preserveScroll: true,
        onFinish: () => { updating.value = false; },
    });
};

const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';

const statusCfg = {
    pending:          { bg: 'bg-amber-400/10',   text: 'text-amber-400',   dot: 'bg-amber-400',   label: 'Pending Review' },
    approved:         { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', label: 'Approved' },
    rejected:         { bg: 'bg-red-400/10',     text: 'text-red-400',     dot: 'bg-red-400',     label: 'Rejected' },
    in_progress:      { bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400',    label: 'In Progress' },
    revision_required:{ bg: 'bg-orange-400/10',  text: 'text-orange-400',  dot: 'bg-orange-400',  label: 'Revision Required' },
    ready:            { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', label: 'Ready' },
};
const getStatus = s => statusCfg[(s||'').toLowerCase()] || { bg: 'bg-gray-400/10', text: 'text-gray-400', dot: 'bg-gray-400', label: s || 'Unknown' };

const getExt = n => (n||'').split('.').pop().toLowerCase();
const fileIcon = name => {
    const ext = getExt(name);
    if (ext === 'pdf')                return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-red-400', bg: 'bg-red-500/15' };
    if (['doc','docx'].includes(ext)) return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-blue-400', bg: 'bg-blue-500/15' };
    if (['jpg','jpeg','png'].includes(ext)) return { path: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'text-emerald-400', bg: 'bg-emerald-500/15' };
    return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-gray-400', bg: 'bg-white/[0.06]' };
};

const initials   = n => (n||'U').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
const avatarGrad = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600'];
    return p[[...(name||'U')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};

const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
</script>

<template>
    <Head :title="`Document — ${document.name}`" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-[12px] text-gray-600 mb-3">
                    <Link href="/admin/documents" class="hover:text-gray-400 transition">Documents</Link>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-gray-400">{{ document.name }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ document.name }}</h1>
                <p class="text-[13px] text-gray-500 mt-1">Document Details and Review</p>
            </div>
            <button @click="downloadDocument"
                class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all self-start sm:self-auto flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download
            </button>
        </div>

        <!-- Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: Document Info + Review -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Document Information -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0', fileIcon(document.file_name).bg]">
                            <svg :class="['w-4 h-4', fileIcon(document.file_name).color]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="fileIcon(document.file_name).path"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Document Information</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">File Name</p>
                            <p class="text-[13px] text-gray-200 font-medium">{{ document.file_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Document Type</p>
                            <p class="text-[13px] text-amber-400 font-medium">{{ document.category_display || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">File Size</p>
                            <p class="text-[13px] text-gray-200 font-medium">{{ document.file_size_human }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">MIME Type</p>
                            <p class="text-[13px] text-blue-400 font-medium font-mono text-[12px]">{{ document.mime_type || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Upload Date</p>
                            <p class="text-[13px] text-gray-200 font-medium">{{ formatDate(document.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Download Count</p>
                            <p class="text-[13px] text-gray-200 font-medium">{{ document.download_count || 0 }}</p>
                        </div>
                        <div v-if="document.last_downloaded_at" class="sm:col-span-2">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Last Downloaded</p>
                            <p class="text-[13px] text-gray-200 font-medium">{{ formatDate(document.last_downloaded_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Document Review -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Document Review</h2>
                    </div>
                    <form @submit.prevent="updateStatus" class="p-6 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Status</label>
                            <select v-model="statusForm.status" :class="sel">
                                <option value="pending">Pending Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="in_progress">In Progress</option>
                                <option value="revision_required">Revision Required</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Admin Notes</label>
                            <textarea v-model="statusForm.admin_notes" rows="4" placeholder="Add review notes or comments…"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition resize-none"></textarea>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" :disabled="updating"
                                class="inline-flex items-center justify-center gap-2 px-5 h-9 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 disabled:opacity-50 transition-all shadow-lg shadow-amber-500/20">
                                <svg v-if="updating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                </svg>
                                {{ updating ? 'Updating…' : 'Update Status' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">

                <!-- Client Information -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Client Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-5 pb-5 border-b border-white/[0.05]">
                            <div class="w-11 h-11 rounded-2xl flex-shrink-0 overflow-hidden">
                                <img v-if="document.client?.avatar" :src="document.client.avatar" :alt="document.client.name" class="w-full h-full object-cover" />
                                <div v-else :class="['w-full h-full flex items-center justify-center text-white text-[13px] font-bold bg-gradient-to-br', avatarGrad(document.client?.name)]">
                                    {{ initials(document.client?.name) }}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[14px] font-bold text-white truncate">{{ document.client?.name || 'Unknown' }}</p>
                                <p class="text-[12px] text-gray-600 truncate">{{ document.client?.email || 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Name</p>
                                <p class="text-[13px] text-amber-400 font-semibold">{{ document.client?.name || 'Unknown' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Email</p>
                                <p class="text-[13px] text-gray-300">{{ document.client?.email || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Order (if any) -->
                <div v-if="document.order" class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Related Order</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Order Number</p>
                            <p class="text-[13px] font-mono text-amber-400 font-semibold">{{ document.order.order_number }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Service Type</p>
                            <p class="text-[13px] text-gray-300">{{ document.order.service_type }}</p>
                        </div>
                    </div>
                </div>

                <!-- Current Status -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-[14px] font-bold text-white">Current Status</h2>
                    </div>
                    <div class="p-6">
                        <span :class="['inline-flex items-center gap-2 text-[12px] font-bold rounded-full px-3 py-1.5', getStatus(document.status).bg, getStatus(document.status).text]">
                            <span :class="['w-2 h-2 rounded-full', getStatus(document.status).dot]"></span>
                            {{ getStatus(document.status).label }}
                        </span>
                        <p v-if="document.status_display && document.status_display !== getStatus(document.status).label" class="text-[12px] text-gray-600 mt-2">{{ document.status_display }}</p>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>
