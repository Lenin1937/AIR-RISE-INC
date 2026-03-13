<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const { __ } = useTranslations();

const props = defineProps({
    chat: { type: Object, required: true },
});

const statusForm = useForm({ status: props.chat.status });

const updateStatus = s => {
    statusForm.status = s;
    statusForm.patch(route('admin.chats.updateStatus', props.chat.id), { preserveScroll: true });
};

const deleteChat = () => {
    if (confirm('Delete this chat conversation?')) router.delete(route('admin.chats.destroy', props.chat.id));
};

const formatDate = d => d ? new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' }) : '—';

const statusCfg = {
    active:   { bg: 'bg-emerald-400/10', text: 'text-emerald-400', dot: 'bg-emerald-400', label: 'Active' },
    closed:   { bg: 'bg-gray-400/10',    text: 'text-gray-400',    dot: 'bg-gray-400',    label: 'Closed' },
    archived: { bg: 'bg-blue-400/10',    text: 'text-blue-400',    dot: 'bg-blue-400',    label: 'Archived' },
};
const getStatus = s => statusCfg[(s||'').toLowerCase()] || statusCfg.closed;

const initials   = n => (n||'G').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
const avatarGrad = name => {
    const p = ['from-violet-500 to-indigo-600','from-amber-400 to-orange-500','from-emerald-400 to-teal-600','from-rose-400 to-pink-600','from-sky-400 to-cyan-600'];
    return p[[...(name||'G')].reduce((a,c)=>a+c.charCodeAt(0),0) % p.length];
};
</script>

<template>
    <Head title="Chat Details" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
            <div>
                <nav class="flex items-center gap-2 text-[12px] text-gray-600 mb-3">
                    <button @click="router.visit(route('admin.chats.index'))" class="hover:text-gray-400 transition">AI Chat Logs</button>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-gray-400 font-mono truncate max-w-[220px]">{{ chat.session_id }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-white tracking-tight">Chat Details</h1>
                <p class="text-[13px] text-gray-500 mt-1">{{ chat.messages?.length || 0 }} messages in this conversation</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <button @click="router.visit(route('admin.chats.index'))"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back
                </button>
                <button @click="deleteChat"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg bg-red-500/10 border border-red-500/20 text-[13px] font-semibold text-red-400 hover:bg-red-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Conversation -->
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden flex flex-col" style="max-height:680px">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3 flex-shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[14px] font-bold text-white">Conversation</h2>
                            <p class="text-[11px] text-gray-600">{{ chat.messages?.length || 0 }} messages</p>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-4" style="scrollbar-width:thin;scrollbar-color:#1e3a5f transparent">
                        <div v-if="!chat.messages || !chat.messages.length" class="flex flex-col items-center justify-center h-32 text-center">
                            <svg class="w-9 h-9 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <p class="text-[13px] text-gray-600">No messages yet</p>
                        </div>

                        <div v-for="msg in chat.messages" :key="msg.id"
                            :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
                            <!-- AI avatar -->
                            <div v-if="msg.role !== 'user'" class="w-7 h-7 rounded-full bg-blue-500/20 flex items-center justify-center mr-2 mt-1 flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2m-6-9h.01"/>
                                </svg>
                            </div>
                            <div :class="[
                                'max-w-[75%] rounded-2xl p-4 text-[13px] leading-relaxed',
                                msg.role === 'user'
                                    ? 'bg-amber-400/10 border border-amber-400/20 text-gray-200 rounded-br-sm'
                                    : 'bg-white/[0.04] border border-white/[0.07] text-gray-300 rounded-bl-sm'
                            ]">
                                <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                                <div class="flex items-center gap-2 mt-2 pt-2 border-t border-white/[0.06]">
                                    <span class="text-[10px] text-gray-600">{{ formatDate(msg.created_at) }}</span>
                                    <span v-if="msg.model" class="text-[10px] text-gray-700">• {{ msg.model }}</span>
                                    <span v-if="msg.response_time_ms" class="text-[10px] text-gray-700">• {{ msg.response_time_ms }}ms</span>
                                </div>
                            </div>
                            <!-- User avatar -->
                            <div v-if="msg.role === 'user'" :class="['w-7 h-7 rounded-full flex items-center justify-center ml-2 mt-1 flex-shrink-0 text-white text-[9px] font-bold bg-gradient-to-br', avatarGrad(chat.user?.name)]">
                                {{ initials(chat.user?.name) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">

                <!-- User Info -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-bold text-white">User Information</h3>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-white/[0.05]">
                            <div :class="['w-11 h-11 rounded-2xl flex items-center justify-center text-white text-[13px] font-bold bg-gradient-to-br flex-shrink-0', avatarGrad(chat.user?.name)]">
                                {{ initials(chat.user?.name) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[14px] font-bold text-white">{{ chat.user?.name || 'Guest User' }}</p>
                                <p class="text-[11px] text-gray-600 truncate">{{ chat.user?.email || 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">User Type</p>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold rounded-full px-2.5 py-1 bg-blue-400/10 text-blue-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                    {{ chat.user_type || 'guest' }}
                                </span>
                            </div>
                            <div v-if="chat.is_lead">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Lead Status</p>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold rounded-full px-2.5 py-1 bg-amber-400/10 text-amber-400">
                                    ★ Marked as Lead
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Info -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-bold text-white">Page Information</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Page Name</p>
                            <p class="text-[13px] text-gray-200">{{ chat.page_name || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Page URL</p>
                            <a :href="chat.page_url" target="_blank" class="text-[12px] text-amber-400 hover:text-amber-300 break-all transition">{{ chat.page_url || '—' }}</a>
                        </div>
                    </div>
                </div>

                <!-- Status Management -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-bold text-white">Status Management</h3>
                    </div>
                    <div class="p-5 space-y-2">
                        <button v-for="s in ['active','closed','archived']" :key="s"
                            @click="updateStatus(s)"
                            :disabled="statusForm.processing"
                            :class="[
                                'w-full h-9 rounded-xl text-[13px] font-semibold transition-all',
                                statusForm.status === s
                                    ? s === 'active'   ? 'bg-emerald-400/15 border border-emerald-400/40 text-emerald-400'
                                    : s === 'closed'   ? 'bg-gray-400/15 border border-gray-400/40 text-gray-300'
                                    : 'bg-blue-400/15 border border-blue-400/40 text-blue-400'
                                    : 'bg-white/[0.03] border border-white/[0.07] text-gray-500 hover:text-gray-300 hover:bg-white/[0.06]'
                            ]">
                            {{ s.charAt(0).toUpperCase() + s.slice(1) }}
                        </button>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-bold text-white">Metadata</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Created</p>
                            <p class="text-[13px] text-gray-200">{{ formatDate(chat.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Last Activity</p>
                            <p class="text-[13px] text-gray-200">{{ formatDate(chat.updated_at) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Message Count</p>
                            <p class="text-[13px] text-amber-400 font-semibold">{{ chat.message_count || 0 }}</p>
                        </div>
                        <div v-if="chat.metadata?.ip_address">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">IP Address</p>
                            <p class="text-[12px] font-mono text-gray-300">{{ chat.metadata.ip_address }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-1">Session ID</p>
                            <p class="text-[11px] font-mono text-gray-600 break-all">{{ chat.session_id }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>
