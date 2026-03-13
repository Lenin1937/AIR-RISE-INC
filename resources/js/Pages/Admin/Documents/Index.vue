<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const openStatusDropdown = ref(null);
const dropdownPos = ref({ top: 0, left: 0 });

const openDropdown = (docId, event) => {
    if (openStatusDropdown.value === docId) {
        openStatusDropdown.value = null;
        return;
    }
    const rect = event.currentTarget.getBoundingClientRect();
    dropdownPos.value = {
        top: rect.bottom + window.scrollY + 4,
        left: rect.left + window.scrollX,
    };
    openStatusDropdown.value = docId;
};

const closeDropdown = () => { openStatusDropdown.value = null; };
onMounted(() => window.addEventListener('click', closeDropdown));
onBeforeUnmount(() => window.removeEventListener('click', closeDropdown));

const props = defineProps({
    documents:     { type: Object, default: () => ({}) },
    clients:       { type: Array,  default: () => [] },
    categories:    { type: Array,  default: () => [] },
    statusOptions: { type: Array,  default: () => [] },
    filters:       { type: Object, default: () => ({}) },
});

const searchQuery  = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const typeFilter   = ref(props.filters?.type ?? '');
const clientFilter = ref(props.filters?.client ?? '');
const showModal    = ref(false);
const uploading    = ref(false);
const sortBy       = ref('created_at');
const sortDir      = ref('desc');
const selectedDocs = ref([]);

const uploadForm = ref({ user_id: '', category: '', name: '', file: null, notes: '' });

const docsArray = computed(() => props.documents?.data || []);

// Local sort only (server already filtered)
const sortedDocuments = computed(() => {
    const list = [...docsArray.value];
    list.sort((a, b) => {
        let av = a[sortBy.value] ?? ''; let bv = b[sortBy.value] ?? '';
        if (sortBy.value === 'created_at') { av = new Date(av); bv = new Date(bv); }
        if (sortBy.value === 'file_size')  { av = Number(av); bv = Number(bv); }
        if (typeof av === 'string') av = av.toLowerCase();
        if (typeof bv === 'string') bv = bv.toLowerCase();
        return sortDir.value === 'asc' ? (av < bv ? -1 : av > bv ? 1 : 0) : (av > bv ? -1 : av < bv ? 1 : 0);
    });
    return list;
});

const setSort = col => {
    if (sortBy.value === col) { sortDir.value = sortDir.value==='asc' ? 'desc' : 'asc'; }
    else { sortBy.value = col; sortDir.value = 'desc'; }
};

const applyServerFilters = () => {
    router.get(route('admin.documents.index'), {
        search: searchQuery.value, status: statusFilter.value,
        type: typeFilter.value, client: clientFilter.value,
    }, { preserveState: true, replace: true });
};

let filterTimer = null;
const debounceFilter = () => { clearTimeout(filterTimer); filterTimer = setTimeout(applyServerFilters, 400); };

const uniqueClients     = computed(() => [...new Set(docsArray.value.map(d => d.client?.name).filter(Boolean))].sort());
const approvedDocuments = computed(() => docsArray.value.filter(d => d.status === 'approved').length);
const pendingDocuments  = computed(() => docsArray.value.filter(d => d.status === 'pending_review').length);
const totalStorageUsed  = computed(() => docsArray.value.reduce((s, d) => s + (d.file_size || 0), 0));

// Bulk selection helpers
const allSelected = computed(() => docsArray.value.length > 0 && selectedDocs.value.length === docsArray.value.length);
const toggleAll = () => { allSelected.value ? (selectedDocs.value = []) : (selectedDocs.value = docsArray.value.map(d=>d.id)); };
const toggleDoc = id => { const i = selectedDocs.value.indexOf(id); i===-1 ? selectedDocs.value.push(id) : selectedDocs.value.splice(i,1); };

const formatFileSize = b => {
    if (!b) return '0 B';
    const u = ['B','KB','MB','GB'];
    const i = Math.floor(Math.log(b) / Math.log(1024));
    return (b / Math.pow(1024, i)).toFixed(1) + ' ' + u[i];
};
const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
const getExt    = n => (n || '').split('.').pop().toLowerCase();

const fileIcon = name => {
    const ext = getExt(name);
    if (ext === 'pdf')               return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-red-400', bg: 'bg-red-500/15', ext: 'PDF' };
    if (['doc','docx'].includes(ext)) return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-blue-400', bg: 'bg-blue-500/15', ext: 'DOC' };
    if (['jpg','jpeg','png','gif','webp'].includes(ext)) return { path: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'text-emerald-400', bg: 'bg-emerald-500/15', ext: 'IMG' };
    if (['xls','xlsx','csv'].includes(ext)) return { path: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', color: 'text-green-400', bg: 'bg-green-500/15', ext: 'XLS' };
    if (['zip','rar','7z'].includes(ext)) return { path: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', color: 'text-orange-400', bg: 'bg-orange-500/15', ext: 'ZIP' };
    return { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-gray-400', bg: 'bg-white/[0.06]', ext: 'FILE' };
};

const statusCfg = {
    uploaded:        { bg:'bg-blue-400/10',    border:'border-blue-400/20',    text:'text-blue-400',    dot:'bg-blue-400',    label:'Uploaded',       hex:'#60a5fa' },
    processing:      { bg:'bg-sky-400/10',     border:'border-sky-400/20',     text:'text-sky-400',     dot:'bg-sky-400',     label:'Processing',     hex:'#38bdf8' },
    pending_review:  { bg:'bg-amber-400/10',   border:'border-amber-400/20',   text:'text-amber-400',   dot:'bg-amber-400',   label:'Pending Review', hex:'#fbbf24' },
    approved:        { bg:'bg-emerald-400/10', border:'border-emerald-400/20', text:'text-emerald-400', dot:'bg-emerald-400', label:'Approved',       hex:'#34d399' },
    rejected:        { bg:'bg-red-400/10',     border:'border-red-400/20',     text:'text-red-400',     dot:'bg-red-400',     label:'Rejected',       hex:'#f87171' },
    requires_action: { bg:'bg-orange-400/10',  border:'border-orange-400/20',  text:'text-orange-400',  dot:'bg-orange-400',  label:'Action Required',hex:'#fb923c' },
    archived:        { bg:'bg-gray-400/10',    border:'border-gray-400/20',    text:'text-gray-400',    dot:'bg-gray-400',    label:'Archived',       hex:'#9ca3af' },
};
const getStatus = s => statusCfg[s] || { bg:'bg-gray-400/10', border:'border-gray-400/20', text:'text-gray-400', dot:'bg-gray-400', label: s||'Unknown', hex:'#9ca3af' };

const initials   = n => (n||'U').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
const avatarGrad = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600'];
    return p[[...(name||'U')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};

const downloadDocument = id => window.open(`/admin/documents/${id}/download`, '_blank');
const reviewDocument   = id => router.visit(`/admin/documents/${id}`);

// Preview modal
const previewDoc   = ref(null);
const openPreview  = doc => { previewDoc.value = doc; };
const closePreview = () => { previewDoc.value = null; };
const isImage = doc => doc?.mime_type?.startsWith('image/');
const isPdf   = doc => doc?.mime_type === 'application/pdf';

// Inline status update
const updateDocStatus = async (docId, newStatus) => {
    try {
        await axios.patch(route('admin.documents.updateStatus', docId), { status: newStatus });
        router.reload({ preserveScroll: true, only: ['documents'] });
    } catch (e) {
        console.error('Status update failed', e);
    }
};

// Bulk actions
const bulkAction = action => {
    if (!selectedDocs.value.length) return;
    router.post(route('admin.documents.bulkAction'), { ids: selectedDocs.value, action }, {
        preserveScroll: true,
        onSuccess: () => { selectedDocs.value = []; },
    });
};

const imagePreview = ref(null);

const handleFileChange = e => {
    const f = e.target.files[0];
    if (!f) return;
    if (f.size > 10 * 1024 * 1024) { alert('File must be < 10MB'); e.target.value = ''; return; }
    uploadForm.value.file = f;
    if (f.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = ev => { imagePreview.value = ev.target.result; };
        reader.readAsDataURL(f);
    } else { imagePreview.value = null; }
};

const uploadDocument = () => {
    if (!uploadForm.value.file || !uploadForm.value.user_id || !uploadForm.value.category || !uploadForm.value.name) {
        alert('Please fill all required fields and select a file.'); return;
    }
    uploading.value = true;
    const fd = new FormData();
    fd.append('file',    uploadForm.value.file);
    fd.append('user_id', uploadForm.value.user_id);
    fd.append('category',uploadForm.value.category);
    fd.append('name',    uploadForm.value.name);
    if (uploadForm.value.notes) fd.append('admin_notes', uploadForm.value.notes);
    router.post(route('admin.documents.store'), fd, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showModal.value = false; uploading.value = false;
            uploadForm.value = { user_id:'', category:'', name:'', file:null, notes:'' };
            const fi = document.getElementById('doc-file-upload'); if (fi) fi.value = '';
        },
        onError: errs => { uploading.value = false; alert('Upload failed:\n' + Object.values(errs).flat().join('\n')); },
    });
};

const kpis = [
    { label:'Total Documents', value:() => (props.documents?.total || 0).toLocaleString(), accent:'#60a5fa', glow:'rgba(96,165,250,0.15)',  bg:'from-blue-500/[0.10]',   iconBg:'bg-blue-500/15',   iconColor:'text-blue-400',   icon:'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { label:'Approved',         value:() => approvedDocuments.value.toLocaleString(),       accent:'#34d399', glow:'rgba(52,211,153,0.15)',  bg:'from-emerald-500/[0.10]',iconBg:'bg-emerald-500/15',iconColor:'text-emerald-400', icon:'M5 13l4 4L19 7' },
    { label:'Pending Review',   value:() => pendingDocuments.value.toLocaleString(),        accent:'#f4b840', glow:'rgba(244,184,64,0.18)',  bg:'from-amber-500/[0.12]',  iconBg:'bg-amber-500/15',  iconColor:'text-amber-400',  icon:'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label:'Storage Used',     value:() => formatFileSize(totalStorageUsed.value),         accent:'#c084fc', glow:'rgba(192,132,252,0.15)', bg:'from-purple-500/[0.10]', iconBg:'bg-purple-500/15', iconColor:'text-purple-400', icon:'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' },
];

const inp = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
</script>

<template>
    <Head title="Document Management" />
    <AdminLayout>
        <div>

        <!-- ── Header ── -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Document Management</h1>
                <p class="text-[13px] text-gray-500 mt-1">Manage client documents, uploads, and approvals</p>
            </div>
            <button @click="showModal = true"
                class="inline-flex items-center gap-2 px-4 h-9 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 transition shadow-lg shadow-amber-500/20 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload Document
            </button>
        </div>

        <!-- ── KPI Cards ── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="kpi in kpis" :key="kpi.label"
                class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 flex flex-col gap-3 hover:border-white/[0.12] transition-all duration-200"
                :style="{boxShadow: '0 0 28px 0 ' + kpi.glow}">
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
                <div class="absolute bottom-0 left-0 right-0 h-[2px] rounded-b-2xl" :style="{background: 'linear-gradient(90deg,' + kpi.accent + ',transparent)'}"></div>
            </div>
        </div>

        <!-- ── Filters ── -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-600 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="searchQuery" @input="debounceFilter" type="text" placeholder="Name or client…" :class="inp.replace('px-3.5','pl-9 pr-3')"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Status</label>
                    <select v-model="statusFilter" @change="applyServerFilters" :class="sel">
                        <option value="">All Statuses</option>
                        <option value="uploaded">Uploaded</option>
                        <option value="processing">Processing</option>
                        <option value="pending_review">Pending Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="requires_action">Action Required</option>
                        <option value="archived">Archived</option>
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
                        <option value="client_upload">Client Upload</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Client</label>
                    <select v-model="clientFilter" @change="applyServerFilters" :class="sel">
                        <option value="">All Clients</option>
                        <option v-for="c in uniqueClients" :key="c" :value="c">{{ c }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ── Bulk action bar ── -->
        <Transition name="slide-down">
            <div v-if="selectedDocs.length" class="flex items-center justify-between px-5 py-3 rounded-2xl bg-amber-400/10 border border-amber-400/30 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"/>
                    <span class="text-[13px] font-bold text-amber-400">{{ selectedDocs.length }} selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="selectedDocs = []" class="text-[12px] text-gray-500 hover:text-white transition px-2">Deselect all</button>
                    <button @click="bulkAction('approve')"
                        class="inline-flex items-center gap-1.5 h-8 px-4 rounded-xl bg-emerald-400/20 border border-emerald-400/30 text-[12px] font-bold text-emerald-400 hover:bg-emerald-400/30 transition">
                        ✓ Approve All
                    </button>
                    <button @click="bulkAction('reject')"
                        class="inline-flex items-center gap-1.5 h-8 px-4 rounded-xl bg-red-500/15 border border-red-400/25 text-[12px] font-bold text-red-400 hover:bg-red-500/25 transition">
                        ✕ Reject All
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ── Data Table ── -->
        <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
            <!-- Table header bar -->
            <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-white">All Documents
                    <span class="ml-2 text-[11px] font-semibold text-amber-400 bg-amber-400/10 rounded-full px-2 py-0.5">{{ props.documents?.total ?? sortedDocuments.length }}</span>
                </h2>
                <span class="text-[11px] text-gray-600">Click column headers to sort</span>
            </div>

            <!-- Empty -->
            <div v-if="!sortedDocuments.length" class="px-6 py-16 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-[13px] font-semibold text-gray-600">No documents found</p>
                <p class="text-[11px] text-gray-700 mt-1">Adjust filters or upload a new document.</p>
            </div>

            <!-- Table -->
            <template v-else>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/[0.06]">
                                <!-- Checkbox -->
                                <th class="pl-6 pr-3 py-3 w-10">
                                    <input type="checkbox" :checked="allSelected" @change="toggleAll"
                                        class="w-4 h-4 rounded border-white/20 accent-amber-400 cursor-pointer"/>
                                </th>
                                <!-- File -->
                                <th @click="setSort('file_name')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none min-w-[200px]">
                                    <div class="flex items-center gap-1">
                                        File
                                        <span v-if="sortBy==='file_name'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span>
                                        <span v-else class="text-gray-700">↕</span>
                                    </div>
                                </th>
                                <!-- Client -->
                                <th @click="setSort('client')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none min-w-[160px]">
                                    <div class="flex items-center gap-1">
                                        Client
                                        <span v-if="sortBy==='client'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span>
                                        <span v-else class="text-gray-700">↕</span>
                                    </div>
                                </th>
                                <!-- Category -->
                                <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 select-none hidden lg:table-cell min-w-[120px]">Category</th>
                                <!-- Status -->
                                <th @click="setSort('status')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none min-w-[140px]">
                                    <div class="flex items-center gap-1">
                                        Status
                                        <span v-if="sortBy==='status'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span>
                                        <span v-else class="text-gray-700">↕</span>
                                    </div>
                                </th>
                                <!-- Size -->
                                <th @click="setSort('file_size')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none hidden sm:table-cell min-w-[80px]">
                                    <div class="flex items-center gap-1">
                                        Size
                                        <span v-if="sortBy==='file_size'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span>
                                        <span v-else class="text-gray-700">↕</span>
                                    </div>
                                </th>
                                <!-- Date -->
                                <th @click="setSort('created_at')" class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 cursor-pointer hover:text-gray-300 transition select-none hidden md:table-cell min-w-[100px]">
                                    <div class="flex items-center gap-1">
                                        Date
                                        <span v-if="sortBy==='created_at'" class="text-amber-400">{{ sortDir==='asc' ? '↑' : '↓' }}</span>
                                        <span v-else class="text-gray-700">↕</span>
                                    </div>
                                </th>
                                <!-- Actions -->
                                <th class="pl-3 pr-6 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-600 text-right select-none">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            <tr v-for="doc in sortedDocuments" :key="doc.id"
                                :class="['group transition-colors', selectedDocs.includes(doc.id) ? 'bg-amber-400/[0.03]' : 'hover:bg-white/[0.02]']">
                                <!-- Checkbox -->
                                <td class="pl-6 pr-3 py-4 w-10">
                                    <input type="checkbox" :checked="selectedDocs.includes(doc.id)" @change="toggleDoc(doc.id)"
                                        class="w-4 h-4 rounded border-white/20 accent-amber-400 cursor-pointer"/>
                                </td>
                                <!-- File -->
                                <td class="px-3 py-4">
                                    <div class="flex items-center gap-3">
                                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0', fileIcon(doc.file_name).bg]">
                                            <svg :class="['w-4 h-4', fileIcon(doc.file_name).color]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" :d="fileIcon(doc.file_name).path"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-semibold text-gray-200 truncate max-w-[180px]">{{ doc.file_name || doc.name }}</p>
                                            <span :class="['text-[9px] font-black px-1.5 py-0.5 rounded-md', fileIcon(doc.file_name).bg, fileIcon(doc.file_name).color]">
                                                {{ fileIcon(doc.file_name).ext }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <!-- Client -->
                                <td class="px-3 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full flex-shrink-0 overflow-hidden">
                                            <img v-if="doc.client?.avatar" :src="doc.client.avatar" :alt="doc.client.name" class="w-full h-full object-cover"/>
                                            <div v-else :class="['w-full h-full flex items-center justify-center text-white text-[9px] font-bold bg-gradient-to-br', avatarGrad(doc.client?.name)]">
                                                {{ initials(doc.client?.name) }}
                                            </div>
                                        </div>
                                        <span class="text-[12px] text-gray-300 truncate max-w-[120px]">{{ doc.client?.name || 'Unknown' }}</span>
                                    </div>
                                </td>
                                <!-- Category -->
                                <td class="px-3 py-4 hidden lg:table-cell">
                                    <span class="text-[12px] text-gray-500 capitalize">{{ (doc.category_display || doc.type || doc.category || '—').replace(/_/g,' ') }}</span>
                                </td>
                                <!-- Status (inline editable) -->
                                <td class="px-3 py-4">
                                    <div class="relative">
                                        <!-- Trigger badge -->
                                        <button @click.stop="openDropdown(doc.id, $event)"
                                            :class="['inline-flex items-center gap-1.5 h-7 pl-2.5 pr-2 rounded-lg text-[11px] font-bold border transition whitespace-nowrap', getStatus(doc.status).bg, getStatus(doc.status).border]"
                                            :style="{ color: getStatus(doc.status).hex }">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :style="{ background: getStatus(doc.status).hex }"></span>
                                            {{ getStatus(doc.status).label }}
                                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>
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
                                            class="h-7 w-7 flex items-center justify-center rounded-lg border border-white/[0.08] bg-white/[0.03] text-gray-500 hover:text-white hover:bg-white/[0.08] transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <button @click="downloadDocument(doc.id)"
                                            class="h-7 w-7 flex items-center justify-center rounded-lg border border-white/[0.08] bg-white/[0.03] text-gray-500 hover:text-white hover:bg-white/[0.08] transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                            </svg>
                                        </button>
                                        <button @click="reviewDocument(doc.id)"
                                            class="h-7 px-3 rounded-lg bg-amber-400/15 border border-amber-400/25 text-[11px] font-bold text-amber-400 hover:bg-amber-400/25 transition">
                                            Review
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

        <!-- ── Upload Modal ── -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto">
                    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showModal = false"></div>
                    <div class="relative w-full max-w-lg my-8 rounded-2xl border border-white/[0.10] bg-[#0d1e35] shadow-2xl overflow-hidden" style="box-shadow:0 0 60px 0 rgba(0,0,0,0.5),0 0 0 1px rgba(255,255,255,0.07)">
                        <!-- Header -->
                        <div class="px-6 pt-6 pb-4 border-b border-white/[0.06] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-500/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-white">Upload Document</h3>
                                    <p class="text-[11px] text-gray-600">Add a document for a client</p>
                                </div>
                            </div>
                            <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-600 hover:text-white hover:bg-white/[0.08] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Form -->
                        <form @submit.prevent="uploadDocument" class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Client <span class="text-amber-400">*</span></label>
                                <select v-model="uploadForm.user_id" required :class="sel">
                                    <option value="">Select a client</option>
                                    <option v-for="c in props.clients" :key="c.value" :value="c.value">{{ c.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Document Category <span class="text-amber-400">*</span></label>
                                <select v-model="uploadForm.category" required :class="sel">
                                    <option value="">Select category</option>
                                    <option value="formation">Formation Documents</option>
                                    <option value="certificates">Certificates</option>
                                    <option value="compliance">Compliance Documents</option>
                                    <option value="additional">Additional Documents</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Document Name <span class="text-amber-400">*</span></label>
                                <input v-model="uploadForm.name" type="text" required placeholder="Enter document name" :class="inp"/>
                            </div>
                            <!-- Drop zone -->
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Select File <span class="text-amber-400">*</span></label>
                                <label for="doc-file-upload" class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed cursor-pointer transition group overflow-hidden"
                                    :class="uploadForm.file ? 'border-amber-400/40 bg-amber-400/[0.04]' : 'border-white/[0.10] hover:border-amber-400/40 p-6'">
                                    <template v-if="imagePreview">
                                        <img :src="imagePreview" alt="preview" class="w-full max-h-52 object-contain rounded-lg p-2"/>
                                        <div class="w-full px-4 pb-3 flex items-center justify-between gap-2">
                                            <p class="text-[12px] text-emerald-400 font-semibold truncate">✓ {{ uploadForm.file.name }}</p>
                                            <p class="text-[11px] text-gray-600 flex-shrink-0">{{ formatFileSize(uploadForm.file.size) }}</p>
                                        </div>
                                    </template>
                                    <template v-else-if="uploadForm.file">
                                        <div class="p-6 flex flex-col items-center gap-2 text-center">
                                            <div class="w-12 h-12 rounded-xl bg-white/[0.06] flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <p class="text-[12px] text-emerald-400 font-semibold">✓ {{ uploadForm.file.name }}</p>
                                            <p class="text-[11px] text-gray-600">{{ formatFileSize(uploadForm.file.size) }}</p>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <svg class="w-9 h-9 text-gray-600 group-hover:text-amber-400/60 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <div class="text-center">
                                            <span class="text-[13px] text-amber-400 font-semibold">Upload a file</span>
                                            <span class="text-[13px] text-gray-500"> or drag and drop</span>
                                            <p class="text-[11px] text-gray-700 mt-1">PDF, DOC, DOCX, JPG, PNG up to 10MB</p>
                                        </div>
                                    </template>
                                    <input id="doc-file-upload" type="file" class="sr-only" @change="handleFileChange" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"/>
                                </label>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Notes</label>
                                <textarea v-model="uploadForm.notes" rows="3" placeholder="Add any notes about this document…"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition resize-none"></textarea>
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-white/[0.06]">
                                <button type="button" @click="showModal = false"
                                    class="inline-flex items-center justify-center px-5 h-9 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="uploading"
                                    class="inline-flex items-center justify-center gap-2 px-5 h-9 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 disabled:opacity-50 transition-all shadow-lg shadow-amber-500/20">
                                    <svg v-if="uploading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                    </svg>
                                    {{ uploading ? 'Uploading…' : 'Upload Document' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Preview Modal ── -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="previewDoc" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closePreview"></div>
                    <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl border border-white/[0.10] bg-[#0d1e35] shadow-2xl overflow-hidden"
                        style="box-shadow:0 0 80px 0 rgba(0,0,0,0.6)">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-white/[0.07] flex-shrink-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div :class="['w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0', fileIcon(previewDoc.file_name || previewDoc.name).bg]">
                                    <svg :class="['w-4 h-4', fileIcon(previewDoc.file_name || previewDoc.name).color]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="fileIcon(previewDoc.file_name || previewDoc.name).path"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[14px] font-bold text-white truncate">{{ previewDoc.name }}</p>
                                    <p class="text-[11px] text-gray-500">{{ formatFileSize(previewDoc.file_size) }} · {{ formatDate(previewDoc.created_at) }} · {{ previewDoc.client?.name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button @click="downloadDocument(previewDoc.id)"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 rounded-xl bg-amber-400/15 border border-amber-400/25 text-[12px] font-bold text-amber-400 hover:bg-amber-400/25 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                    </svg>
                                    Download
                                </button>
                                <button @click="reviewDocument(previewDoc.id)"
                                    class="inline-flex items-center h-8 px-3 rounded-xl bg-white/[0.06] border border-white/[0.10] text-[12px] font-bold text-gray-300 hover:text-white hover:bg-white/[0.10] transition">
                                    Review
                                </button>
                                <button @click="closePreview" class="w-8 h-8 flex items-center justify-center rounded-xl border border-white/[0.08] text-gray-400 hover:text-white hover:bg-white/[0.08] transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <!-- Body -->
                        <div class="flex-1 overflow-auto bg-[#080f1c] min-h-0">
                            <div v-if="isImage(previewDoc)" class="flex items-center justify-center p-6 min-h-[400px]">
                                <img :src="previewDoc.preview_url" :alt="previewDoc.name"
                                    class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-2xl"/>
                            </div>
                            <iframe v-else-if="isPdf(previewDoc)" :src="previewDoc.preview_url + '#toolbar=1&navpanes=0'"
                                class="w-full" style="height:70vh" frameborder="0"></iframe>
                            <div v-else class="flex flex-col items-center justify-center py-20 gap-4">
                                <div :class="['w-20 h-20 rounded-2xl flex items-center justify-center', fileIcon(previewDoc.file_name || previewDoc.name).bg]">
                                    <svg :class="['w-10 h-10', fileIcon(previewDoc.file_name || previewDoc.name).color]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="fileIcon(previewDoc.file_name || previewDoc.name).path"/>
                                    </svg>
                                </div>
                                <p class="text-[15px] font-bold text-white">Preview not available</p>
                                <p class="text-[13px] text-gray-500 mb-2">This file type cannot be previewed in the browser.</p>
                                <button @click="downloadDocument(previewDoc.id)"
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
                            <span class="text-[11px] text-gray-600">{{ previewDoc.category_display || '—' }}</span>
                            <span v-if="previewDoc.client?.name" class="text-[11px] text-gray-600">· {{ previewDoc.client.name }}</span>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Status dropdown portal (escapes overflow-hidden) ── -->
        <Teleport to="body">
            <Transition name="fade-down">
                <div v-if="openStatusDropdown !== null" @click.stop
                    class="fixed z-[9999] min-w-[180px] rounded-xl border border-white/[0.10] bg-[#0d1f35] shadow-2xl py-1"
                    :style="{ top: dropdownPos.top + 'px', left: dropdownPos.left + 'px' }">
                    <template v-for="doc in sortedDocuments" :key="doc.id">
                        <template v-if="openStatusDropdown === doc.id">
                            <button v-for="opt in Object.entries(statusCfg)" :key="opt[0]"
                                @click="updateDocStatus(doc.id, opt[0]); openStatusDropdown = null"
                                :class="['w-full flex items-center gap-2.5 px-3 py-2 text-[12px] font-semibold transition', doc.status === opt[0] ? 'bg-white/[0.06]' : 'hover:bg-white/[0.04]']"
                                :style="{ color: opt[1].hex }">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ background: opt[1].hex }"></span>
                                {{ opt[1].label }}
                                <svg v-if="doc.status === opt[0]" class="ml-auto w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </template>
                    </template>
                </div>
            </Transition>
        </Teleport>

        </div>
    </AdminLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
.fade-down-enter-active, .fade-down-leave-active { transition: all 0.15s ease; }
.fade-down-enter-from, .fade-down-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
