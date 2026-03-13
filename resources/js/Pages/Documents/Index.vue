<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? {});

const { __ } = useTranslations();

const props = defineProps({
    documents:     { type: Object, default: () => ({}) },
    categories:    { type: Array,  default: () => [] },
    statusOptions: { type: Array,  default: () => [] },
    filters:       { type: Object, default: () => ({}) },
    stats:         { type: Object, default: () => ({}) },
    orders:        { type: Array,  default: () => [] },
});

const searchQuery  = ref(props.filters?.search    ?? '');
const statusFilter = ref(props.filters?.status    ?? '');
const typeFilter   = ref(props.filters?.category  ?? '');
const orderFilter  = ref(props.filters?.order_id  ?? '');

const sortBy  = ref('created_at');
const sortDir = ref('desc');

const selectedDocs = ref([]);

const docsArray = computed(() => props.documents?.data || []);

const sortedDocuments = computed(() => {
    const list = [...docsArray.value];
    list.sort((a, b) => {
        let av = a[sortBy.value] ?? ''; let bv = b[sortBy.value] ?? '';
        if (sortBy.value === 'created_at') { av = new Date(av); bv = new Date(bv); }
        if (sortBy.value === 'file_size')  { av = Number(av);   bv = Number(bv); }
        if (typeof av === 'string') av = av.toLowerCase();
        if (typeof bv === 'string') bv = bv.toLowerCase();
        return sortDir.value === 'asc' ? (av < bv ? -1 : av > bv ? 1 : 0) : (av > bv ? -1 : av < bv ? 1 : 0);
    });
    return list;
});

const setSort = col => {
    if (sortBy.value === col) { sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; }
    else { sortBy.value = col; sortDir.value = 'desc'; }
};

const applyServerFilters = () => {
    router.get(route('documents.index'), {
        search:   searchQuery.value,
        status:   statusFilter.value,
        category: typeFilter.value,
        order_id: orderFilter.value,
    }, { preserveState: true, replace: true });
};

let filterTimer = null;
const debounceFilter = () => { clearTimeout(filterTimer); filterTimer = setTimeout(applyServerFilters, 400); };

const allSelected = computed(() => docsArray.value.length > 0 && selectedDocs.value.length === docsArray.value.length);
const toggleAll   = () => { allSelected.value ? (selectedDocs.value = []) : (selectedDocs.value = docsArray.value.map(d => d.id)); };
const toggleDoc   = id => { const i = selectedDocs.value.indexOf(id); i === -1 ? selectedDocs.value.push(id) : selectedDocs.value.splice(i, 1); };

const formatFileSize = b => {
    if (!b) return '0 B';
    const u = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(b) / Math.log(1024));
    return (b / Math.pow(1024, i)).toFixed(1) + ' ' + u[i];
};
const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
const getExt     = n => (n || '').split('.').pop().toLowerCase();

const fileIcon = name => {
    const ext = getExt(name);
    if (ext === 'pdf')                                   return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-red-400',     bg: 'bg-red-500/15',     ext: 'PDF'  };
    if (['doc','docx'].includes(ext))                    return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-blue-400',    bg: 'bg-blue-500/15',    ext: 'DOC'  };
    if (['jpg','jpeg','png','gif','webp'].includes(ext)) return { path: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'text-emerald-400', bg: 'bg-emerald-500/15', ext: 'IMG'  };
    if (['xls','xlsx','csv'].includes(ext))              return { path: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',                                 color: 'text-green-400',   bg: 'bg-green-500/15',   ext: 'XLS'  };
    if (['zip','rar','7z'].includes(ext))                return { path: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',                                       color: 'text-orange-400',  bg: 'bg-orange-500/15',  ext: 'ZIP'  };
    return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-gray-400', bg: 'bg-white/[0.06]', ext: 'FILE' };
};

const statusCfg = {
    uploaded:        { bg: 'bg-blue-400/10',    border: 'border-blue-400/20',    label: 'Uploaded',        hex: '#60a5fa' },
    processing:      { bg: 'bg-sky-400/10',     border: 'border-sky-400/20',     label: 'Processing',      hex: '#38bdf8' },
    pending_review:  { bg: 'bg-amber-400/10',   border: 'border-amber-400/20',   label: 'Pending Review',  hex: '#fbbf24' },
    pending:         { bg: 'bg-amber-400/10',   border: 'border-amber-400/20',   label: 'Pending',         hex: '#fbbf24' },
    approved:        { bg: 'bg-emerald-400/10', border: 'border-emerald-400/20', label: 'Approved',        hex: '#34d399' },
    rejected:        { bg: 'bg-red-400/10',     border: 'border-red-400/20',     label: 'Rejected',        hex: '#f87171' },
    requires_action: { bg: 'bg-orange-400/10',  border: 'border-orange-400/20',  label: 'Action Required', hex: '#fb923c' },
    archived:        { bg: 'bg-gray-400/10',    border: 'border-gray-400/20',    label: 'Archived',        hex: '#9ca3af' },
    expired:         { bg: 'bg-gray-400/10',    border: 'border-gray-400/20',    label: 'Expired',         hex: '#9ca3af' },
};
const getStatus = s => statusCfg[s] || { bg: 'bg-gray-400/10', border: 'border-gray-400/20', label: s || 'Unknown', hex: '#9ca3af' };

const initials   = n => (n || 'O').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
const avatarGrad = name => {
    const p = ['from-violet-500 to-indigo-600', 'from-amber-400 to-orange-500', 'from-emerald-400 to-teal-600', 'from-rose-400 to-pink-600', 'from-sky-400 to-cyan-600'];
    return p[[...(name || 'O')].reduce((a, c) => a + c.charCodeAt(0), 0) % p.length];
};

const downloadDoc = doc => {
    if (!doc.url) return;
    const a = document.createElement('a'); a.href = doc.url; a.download = doc.name || 'document'; a.click();
};

const previewDoc  = ref(null);
const openPreview = doc => { previewDoc.value = doc; };
const closePreview = () => { previewDoc.value = null; };

const isImage = doc => doc?.mime_type?.startsWith('image/');
const isPdf   = doc => doc?.mime_type === 'application/pdf';

const kpis = [
    { label: 'Total Documents', value: () => (props.stats?.total       ?? 0).toLocaleString(), accent: '#60a5fa', glow: 'rgba(96,165,250,0.15)',  bg: 'from-blue-500/[0.10]',    iconBg: 'bg-blue-500/15',    iconColor: 'text-blue-400',    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { label: 'Approved',         value: () => (props.stats?.approved    ?? 0).toLocaleString(), accent: '#34d399', glow: 'rgba(52,211,153,0.15)',  bg: 'from-emerald-500/[0.10]', iconBg: 'bg-emerald-500/15', iconColor: 'text-emerald-400', icon: 'M5 13l4 4L19 7' },
    { label: 'Pending Review',   value: () => (props.stats?.pending     ?? 0).toLocaleString(), accent: '#f4b840', glow: 'rgba(244,184,64,0.18)',  bg: 'from-amber-500/[0.12]',   iconBg: 'bg-amber-500/15',   iconColor: 'text-amber-400',   icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Storage Used',     value: () => formatFileSize(props.stats?.storage_used ?? 0),   accent: '#c084fc', glow: 'rgba(192,132,252,0.15)', bg: 'from-purple-500/[0.10]',  iconBg: 'bg-purple-500/15',  iconColor: 'text-purple-400',  icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' },
];

const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
const inp = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
</script>

<template>
    <Head :title="__('client.my_documents')" />
    <AuthenticatedLayout>
        <div>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">{{ __('client.my_documents') }}</h1>
                    <p class="text-[13px] text-gray-500 mt-1">{{ __('client.access_documents') }}</p>
                </div>
                <Link :href="route('orders.create')"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 transition shadow-lg shadow-amber-500/20 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Order
                </Link>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div v-for="kpi in kpis" :key="kpi.label"
                    class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3 hover:border-white/[0.12] transition-all duration-200"
                    :style="{ boxShadow: '0 0 28px 0 ' + kpi.glow }">
                    <div :class="['absolute inset-0 bg-gradient-to-br opacity-60 pointer-events-none to-transparent', kpi.bg]"></div>
                    <div class="relative w-10 h-10 rounded-xl flex items-center justify-center" :class="kpi.iconBg">
                        <svg :class="['w-5 h-5', kpi.iconColor]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="kpi.icon"/>
                        </svg>
                    </div>
                    <div class="relative">
                        <p class="text-[22px] font-extrabold text-white leading-none">{{ kpi.value() }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-600 mt-1.5">{{ kpi.label }}</p>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl"
                        :style="{ background: 'linear-gradient(90deg,' + kpi.accent + ',transparent)' }"></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Search</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-600 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input v-model="searchQuery" @input="debounceFilter" type="text" placeholder="Search documents…"
                                class="w-full h-10 pl-9 pr-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Status</label>
                        <select v-model="statusFilter" @change="applyServerFilters" :class="sel">
                            <option value="">All Statuses</option>
                            <option value="uploaded">Uploaded</option>
                            <option value="processing">Processing</option>
                            <option value="pending_review">Pending Review</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="requires_action">Action Required</option>
                            <option value="archived">Archived</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Document Type</label>
                        <select v-model="typeFilter" @change="applyServerFilters" :class="sel">
                            <option value="">All Types</option>
                            <option value="formation">Formation</option>
                            <option value="certificates">Certificates</option>
                            <option value="compliance">Compliance</option>
                            <option value="additional">Additional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Order</label>
                        <select v-model="orderFilter" @change="applyServerFilters" :class="sel">
                            <option value="">All Orders</option>
                            <option v-for="o in orders" :key="o.value" :value="o.value">{{ o.label }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bulk action bar -->
            <Transition name="slide-down">
                <div v-if="selectedDocs.length" class="flex items-center justify-between px-5 py-3 rounded-2xl bg-amber-400/10 border border-amber-400/30 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"/>
                        <span class="text-[13px] font-bold text-amber-400">{{ selectedDocs.length }} selected</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="selectedDocs = []" class="text-[12px] text-gray-500 hover:text-white transition px-2">Deselect all</button>
                        <button class="inline-flex items-center gap-1.5 h-8 px-4 rounded-xl bg-amber-400/15 border border-amber-400/30 text-[12px] font-bold text-amber-400 hover:bg-amber-400/25 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Selected
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Data Table -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <h2 class="text-[14px] font-bold text-white">
                        All Documents
                        <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ props.documents?.total ?? sortedDocuments.length }}</span>
                    </h2>
                    <span class="text-[11px] text-gray-600">Click column headers to sort</span>
                </div>

                <!-- Empty State -->
                <div v-if="!sortedDocuments.length" class="px-6 py-16 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-[13px] font-semibold text-gray-600">No documents found</p>
                    <p class="text-[11px] text-gray-700 mt-1">Adjust your filters or create a new order.</p>
                </div>

                <!-- Table -->
                <template v-else>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-white/[0.06]">
                                    <th class="pl-6 pr-3 py-3 w-10">
                                        <input type="checkbox" :checked="allSelected" @change="toggleAll"
                                            class="w-4 h-4 rounded border-white/20 accent-amber-400 cursor-pointer"/>
                                    </th>
                                    <th @click="setSort('name')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none min-w-[200px]">
                                        <div class="flex items-center gap-1">File <span v-if="sortBy==='name'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span><span v-else class="text-gray-700">↕</span></div>
                                    </th>
                                    <th @click="setSort('order_name')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none min-w-[160px]">
                                        <div class="flex items-center gap-1">Order <span v-if="sortBy==='order_name'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span><span v-else class="text-gray-700">↕</span></div>
                                    </th>
                                    <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 select-none hidden lg:table-cell min-w-[120px]">Category</th>
                                    <th @click="setSort('status')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none min-w-[140px]">
                                        <div class="flex items-center gap-1">Status <span v-if="sortBy==='status'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span><span v-else class="text-gray-700">↕</span></div>
                                    </th>
                                    <th @click="setSort('file_size')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none hidden sm:table-cell min-w-[80px]">
                                        <div class="flex items-center gap-1">Size <span v-if="sortBy==='file_size'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span><span v-else class="text-gray-700">↕</span></div>
                                    </th>
                                    <th @click="setSort('created_at')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none hidden md:table-cell min-w-[100px]">
                                        <div class="flex items-center gap-1">Date <span v-if="sortBy==='created_at'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span><span v-else class="text-gray-700">↕</span></div>
                                    </th>
                                    <th class="pl-3 pr-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 text-right select-none">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.04]">
                                <tr v-for="doc in sortedDocuments" :key="doc.id"
                                    :class="['group transition-colors', selectedDocs.includes(doc.id) ? 'bg-amber-400/[0.03]' : 'hover:bg-white/[0.02]']">
                                    <td class="pl-6 pr-3 py-4 w-10">
                                        <input type="checkbox" :checked="selectedDocs.includes(doc.id)" @change="toggleDoc(doc.id)"
                                            class="w-4 h-4 rounded border-white/20 accent-amber-400 cursor-pointer"/>
                                    </td>
                                    <!-- File -->
                                    <td class="px-3 py-4">
                                        <div class="flex items-center gap-3">
                                            <div :class="['w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0', fileIcon(doc.original_filename || doc.name).bg]">
                                                <svg :class="['w-4 h-4', fileIcon(doc.original_filename || doc.name).color]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" :d="fileIcon(doc.original_filename || doc.name).path"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[13px] font-semibold text-gray-200 truncate max-w-[180px]">{{ doc.name }}</p>
                                                <span :class="['text-[9px] font-black px-1.5 py-0.5 rounded-md', fileIcon(doc.original_filename || doc.name).bg, fileIcon(doc.original_filename || doc.name).color]">
                                                    {{ fileIcon(doc.original_filename || doc.name).ext }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Order -->
                                    <td class="px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full flex-shrink-0 overflow-hidden">
                                                <img v-if="!doc.order_name && authUser.profile_picture_url" :src="authUser.profile_picture_url" :alt="authUser.name" class="w-full h-full object-cover"/>
                                                <div v-else :class="['w-full h-full flex items-center justify-center text-white text-[9px] font-bold bg-gradient-to-br', avatarGrad(doc.order_name || authUser.name)]">
                                                    {{ initials(doc.order_name || authUser.name) }}
                                                </div>
                                            </div>
                                            <span class="text-[12px] text-gray-300 truncate max-w-[120px]">{{ doc.order_name || authUser.name || '—' }}</span>
                                        </div>
                                    </td>
                                    <!-- Category -->
                                    <td class="px-3 py-4 hidden lg:table-cell">
                                        <span class="text-[12px] text-gray-500 capitalize">{{ (doc.category_display || doc.type || doc.category || '—').replace(/_/g, ' ') }}</span>
                                    </td>
                                    <!-- Status -->
                                    <td class="px-3 py-4">
                                        <span :class="['inline-flex items-center gap-1.5 h-7 pl-2.5 pr-2.5 rounded-lg text-[11px] font-bold border whitespace-nowrap', getStatus(doc.status).bg, getStatus(doc.status).border]"
                                            :style="{ color: getStatus(doc.status).hex }">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :style="{ background: getStatus(doc.status).hex }"></span>
                                            {{ getStatus(doc.status).label }}
                                        </span>
                                    </td>
                                    <!-- Size -->
                                    <td class="px-3 py-4 hidden sm:table-cell">
                                        <span class="text-[12px] text-gray-600">{{ formatFileSize(doc.file_size) }}</span>
                                    </td>
                                    <!-- Date -->
                                    <td class="px-3 py-4 hidden md:table-cell">
                                        <span class="text-[12px] text-gray-600">{{ formatDate(doc.created_at) }}</span>
                                    </td>
                                    <!-- Actions -->
                                    <td class="pl-3 pr-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openPreview(doc)"
                                                :class="['h-7 w-7 flex items-center justify-center rounded-lg border border-white/[0.08] bg-white/[0.03] transition',
                                                    'text-gray-500 hover:text-white hover:bg-white/[0.08]']">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            <button @click="downloadDoc(doc)" :disabled="!doc.url"
                                                :class="['h-7 px-3 rounded-lg text-[11px] font-bold transition',
                                                    doc.url ? 'bg-amber-400/15 border border-amber-400/25 text-amber-400 hover:bg-amber-400/25' : 'bg-white/[0.03] border border-white/[0.06] text-gray-600 cursor-not-allowed opacity-40']">
                                                Download
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-between gap-4">
                        <span class="text-[12px] text-gray-600">
                            Showing
                            <span class="text-gray-400 font-medium">{{ props.documents?.from ?? 1 }}</span>–<span class="text-gray-400 font-medium">{{ props.documents?.to ?? sortedDocuments.length }}</span>
                            of <span class="text-gray-400 font-medium">{{ props.documents?.total ?? 0 }}</span>
                        </span>
                        <div class="flex items-center gap-1.5">
                            <a v-if="props.documents?.prev_page_url" :href="props.documents.prev_page_url"
                                class="h-8 px-3 rounded-xl border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-400 hover:text-amber-400 hover:border-amber-400/30 transition flex items-center">
                                ← Prev
                            </a>
                            <template v-if="props.documents?.links">
                                <a v-for="link in props.documents.links.slice(1,-1)" :key="link.label"
                                    :href="link.url || '#'"
                                    :class="['h-8 w-8 rounded-xl border text-[12px] font-semibold transition flex items-center justify-center',
                                        link.active ? 'border-amber-400/50 bg-amber-400/20 text-amber-400' :
                                        link.url ? 'border-white/[0.08] bg-white/[0.03] text-gray-400 hover:text-white hover:border-white/[0.15]' :
                                        'border-white/[0.04] text-gray-700 cursor-default']"
                                    v-html="link.label"/>
                            </template>
                            <a v-if="props.documents?.next_page_url" :href="props.documents.next_page_url"
                                class="h-8 px-3 rounded-xl border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-400 hover:text-amber-400 hover:border-amber-400/30 transition flex items-center">
                                Next →
                            </a>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </AuthenticatedLayout>

    <!-- ── Preview Modal ── -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="previewDoc" class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.esc="closePreview">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closePreview"></div>
                <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl border border-white/[0.10] bg-[#0d1e35] shadow-2xl overflow-hidden"
                    style="box-shadow:0 0 80px 0 rgba(0,0,0,0.6)">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-white/[0.07] flex-shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div :class="['w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0', fileIcon(previewDoc.original_filename || previewDoc.name).bg]">
                                <svg :class="['w-4 h-4', fileIcon(previewDoc.original_filename || previewDoc.name).color]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="fileIcon(previewDoc.original_filename || previewDoc.name).path"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[14px] font-bold text-white truncate">{{ previewDoc.name }}</p>
                                <p class="text-[11px] text-gray-500">{{ formatFileSize(previewDoc.file_size) }} · {{ formatDate(previewDoc.created_at) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button v-if="previewDoc.url" @click="downloadDoc(previewDoc)"
                                class="inline-flex items-center gap-1.5 h-8 px-3 rounded-xl bg-amber-400/15 border border-amber-400/25 text-[12px] font-bold text-amber-400 hover:bg-amber-400/25 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                </svg>
                                Download
                            </button>
                            <button @click="closePreview" class="w-8 h-8 flex items-center justify-center rounded-xl border border-white/[0.08] text-gray-400 hover:text-white hover:bg-white/[0.08] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <!-- Modal Body -->
                    <div class="flex-1 overflow-auto bg-[#080f1c] min-h-0">
                        <!-- Image preview -->
                        <div v-if="isImage(previewDoc)" class="flex items-center justify-center p-6 min-h-[400px]">
                            <img :src="previewDoc.preview_url || previewDoc.url" :alt="previewDoc.name"
                                class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-2xl"/>
                        </div>
                        <!-- PDF preview -->
                        <iframe v-else-if="isPdf(previewDoc)" :src="(previewDoc.preview_url || previewDoc.url) + '#toolbar=1&navpanes=0'"
                            class="w-full" style="height:70vh" frameborder="0"></iframe>
                        <!-- Unsupported -->
                        <div v-else class="flex flex-col items-center justify-center py-20 gap-4">
                            <div :class="['w-20 h-20 rounded-2xl flex items-center justify-center', fileIcon(previewDoc.original_filename || previewDoc.name).bg]">
                                <svg :class="['w-10 h-10', fileIcon(previewDoc.original_filename || previewDoc.name).color]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="fileIcon(previewDoc.original_filename || previewDoc.name).path"/>
                                </svg>
                            </div>
                            <p class="text-[15px] font-bold text-white">Preview not available</p>
                            <p class="text-[13px] text-gray-500 mb-2">This file type cannot be previewed in the browser.</p>
                            <button v-if="previewDoc.url" @click="downloadDoc(previewDoc)"
                                class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 transition">
                                Download to View
                            </button>
                        </div>
                    </div>
                    <!-- Status strip -->
                    <div class="flex items-center gap-3 px-5 py-3 border-t border-white/[0.06] flex-shrink-0">
                        <span :class="['inline-flex items-center gap-1.5 h-6 pl-2 pr-2.5 rounded-md text-[11px] font-bold border', getStatus(previewDoc.status).bg, getStatus(previewDoc.status).border]"
                            :style="{ color: getStatus(previewDoc.status).hex }">
                            <span class="w-1.5 h-1.5 rounded-full" :style="{ background: getStatus(previewDoc.status).hex }"></span>
                            {{ getStatus(previewDoc.status).label }}
                        </span>
                        <span class="text-[11px] text-gray-600">{{ previewDoc.category_display || previewDoc.category_display || 'Document' }}</span>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
