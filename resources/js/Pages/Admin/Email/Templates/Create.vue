<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps({ template: { type: Object, default: null } });

const isEdit = computed(() => !!props.template);

const form = useForm({
    name:         props.template?.name         ?? '',
    subject:      props.template?.subject      ?? '',
    preheader:    props.template?.preheader    ?? '',
    body_html:    props.template?.body_html    ?? '',
    body_text:    props.template?.body_text    ?? '',
    category:     props.template?.category     ?? 'marketing',
    ai_generated: props.template?.ai_generated ?? false,
});

const submit = () => {
    if (isEdit.value) {
        form.patch(route('admin.email.templates.update', props.template.id), {
            onSuccess: () => router.visit(route('admin.email.templates.index'))
        });
    } else {
        form.post(route('admin.email.templates.store'));
    }
};

/* Preview */
const showPreview = ref(false);

/* AI Assist */
const aiPanel = ref(false);
const aiMode  = ref('draft');
const aiInput = ref('');
const aiTone  = ref('formal');
const aiLang  = ref('en');
const aiLoading = ref(false);
const aiResult  = ref('');
const aiError   = ref('');

const aiModes = [
    { value: 'draft',      label: 'Draft from idea' },
    { value: 'improve',    label: 'Improve text' },
    { value: 'shorten',    label: 'Shorten' },
    { value: 'expand',     label: 'Expand' },
    { value: 'translate',  label: 'Translate' },
    { value: 'subjects',   label: 'Subject lines' },
    { value: 'sequence',   label: 'Email sequence' },
    { value: 'compliance', label: 'Compliance check' },
];

const runAi = async () => {
    aiLoading.value = true; aiError.value = ''; aiResult.value = '';
    try {
        const { data } = await axios.post(route('admin.email.ai.generate'), {
            mode: aiMode.value, input: aiInput.value, tone: aiTone.value, language: aiLang.value
        });
        if (data.success) {
            aiResult.value = typeof data.result === 'string' ? data.result : JSON.stringify(data.result, null, 2);
        } else { aiError.value = data.error ?? 'Unknown error'; }
    } catch (e) {
        aiError.value = e?.response?.data?.error ?? 'Request failed';
    }
    aiLoading.value = false;
};

const applyToBody = () => {
    if (!aiResult.value) return;
    // Try to extract HTML body from JSON result
    try {
        const parsed = JSON.parse(aiResult.value);
        if (parsed.body)    form.body_html = parsed.body;
        if (parsed.subject) form.subject   = parsed.subject;
        if (parsed.preheader) form.preheader = parsed.preheader;
    } catch {
        form.body_html = aiResult.value;
    }
    form.ai_generated = true;
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Template' : 'New Template'" />
    <AdminLayout>
        <div class="flex items-center gap-3 mb-8">
            <Link :href="route('admin.email.templates.index')" class="w-8 h-8 rounded-lg border border-white/[0.08] bg-white/[0.04] text-gray-500 hover:text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ isEdit ? 'Edit Template' : 'New Template' }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update existing template' : 'Create an HTML email template' }}</p>
            </div>
            <div class="ml-auto flex flex-wrap items-center justify-end gap-2">
                <button @click="aiPanel = !aiPanel" :class="['inline-flex items-center gap-2 rounded-lg px-3 sm:px-4 h-9 text-[13px] font-semibold transition', aiPanel ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'border border-white/[0.10] bg-white/[0.04] text-gray-300 hover:text-white hover:bg-white/[0.08]']">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                    <span class="hidden sm:inline">AI Assist</span>
                </button>
                <button @click="showPreview = !showPreview" class="hidden sm:inline-flex items-center gap-2 border border-white/[0.10] bg-white/[0.04] text-gray-300 hover:text-white hover:bg-white/[0.08] rounded-lg px-4 h-9 text-[13px] font-semibold transition">
                    Preview
                </button>
                <button @click="submit" :disabled="form.processing" class="inline-flex items-center gap-2 bg-amber-400 text-[#0b1e33] hover:bg-amber-300 rounded-lg px-4 sm:px-5 h-9 text-[13px] font-semibold transition disabled:opacity-60">
                    <span class="hidden sm:inline">{{ isEdit ? 'Update' : 'Save Template' }}</span>
                    <span class="sm:hidden">{{ isEdit ? 'Update' : 'Save' }}</span>
                </button>
            </div>
        </div>

        <div :class="['grid gap-6 transition-all', aiPanel ? 'lg:grid-cols-[1fr_360px]' : 'lg:grid-cols-1']">
            <!-- Editor panel -->
            <div class="space-y-5">
                <!-- Meta fields -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 space-y-4">
                    <h2 class="text-[13px] font-bold text-white">Template Info</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Template Name *</label>
                            <input v-model="form.name" type="text" required class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                            <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Category *</label>
                            <select v-model="form.category" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40">
                                <option value="marketing">Marketing</option>
                                <option value="transactional">Transactional</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Subject Line *</label>
                        <input v-model="form.subject" type="text" required class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        <p v-if="form.errors.subject" class="text-red-400 text-xs mt-1">{{ form.errors.subject }}</p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Preheader <span class="text-gray-600 font-normal">(preview text)</span></label>
                        <input v-model="form.preheader" type="text" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                    </div>
                </div>

                <!-- HTML Editor -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-[13px] font-bold text-white">HTML Body</h2>
                        <p class="hidden sm:block text-[11px] text-gray-600">Supports <code class="text-amber-400" v-pre>{{FirstName}}</code>, <code class="text-amber-400" v-pre>{{Email}}</code> placeholders</p>
                    </div>
                    <textarea v-model="form.body_html" rows="22" spellcheck="false"
                        class="w-full bg-[#070f1c] border border-white/[0.08] text-gray-200 text-[12px] font-mono rounded-xl px-4 py-3 focus:outline-none focus:border-amber-400/30 resize-y transition"
                        placeholder="<!DOCTYPE html>&#10;<html>&#10;  <body>&#10;    <h1>Hello {{FirstName}}</h1>&#10;  </body>&#10;</html>"/>
                    <p v-if="form.errors.body_html" class="text-red-400 text-xs mt-1">{{ form.errors.body_html }}</p>
                </div>

                <!-- Preview pane -->
                <div v-if="showPreview && form.body_html" class="rounded-2xl border border-white/[0.07] bg-white overflow-hidden">
                    <div class="bg-gray-100 px-4 py-2 flex items-center gap-2 border-b border-gray-200">
                        <div class="flex gap-1.5"><div class="w-3 h-3 rounded-full bg-red-400"/><div class="w-3 h-3 rounded-full bg-yellow-400"/><div class="w-3 h-3 rounded-full bg-green-400"/></div>
                        <p class="text-xs text-gray-500 flex-1 text-center">Email Preview</p>
                    </div>
                    <iframe :srcdoc="form.body_html" class="w-full h-[500px] border-0" sandbox="allow-same-origin"/>
                </div>
            </div>

            <!-- AI Assist panel -->
            <div v-if="aiPanel" class="rounded-2xl border border-purple-500/20 bg-[#0c1c30] p-5 flex flex-col gap-4 h-fit sticky top-20">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="text-[14px] font-bold text-white">AI Assistant</h3>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Mode</label>
                    <select v-model="aiMode" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-[13px] rounded-lg px-3 h-9 focus:outline-none focus:border-purple-400/40">
                        <option v-for="m in aiModes" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                        {{ aiMode === 'draft' ? 'Describe the email goal' : aiMode === 'translate' ? 'Paste text to translate' : 'Input' }}
                    </label>
                    <textarea v-model="aiInput" rows="5" class="w-full bg-[#070f1c] border border-white/[0.08] text-gray-200 text-[12px] rounded-xl px-3 py-2.5 focus:outline-none focus:border-purple-400/30 resize-none"
                        :placeholder="aiMode === 'draft' ? 'e.g. Welcome email for new LLC clients thanking them…' : 'Paste your email text here…'"/>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase">Tone</label>
                        <select v-model="aiTone" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-[12px] rounded-lg px-2 h-8 focus:outline-none">
                            <option value="formal">Formal</option>
                            <option value="friendly">Friendly</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase">Language</label>
                        <select v-model="aiLang" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-[12px] rounded-lg px-2 h-8 focus:outline-none">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="ar">Arabic</option>
                            <option value="ru">Russian</option>
                        </select>
                    </div>
                </div>

                <button @click="runAi" :disabled="aiLoading || !aiInput" class="w-full h-9 rounded-lg bg-purple-500 hover:bg-purple-400 text-white text-[13px] font-bold transition disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg v-if="aiLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ aiLoading ? 'Generating…' : 'Generate' }}
                </button>

                <div v-if="aiError" class="text-[12px] text-red-400 bg-red-500/10 rounded-lg px-3 py-2">{{ aiError }}</div>

                <div v-if="aiResult" class="flex flex-col gap-2">
                    <div class="bg-[#070f1c] rounded-xl p-3 max-h-60 overflow-y-auto">
                        <pre class="text-[11px] text-gray-300 whitespace-pre-wrap font-mono">{{ aiResult }}</pre>
                    </div>
                    <button @click="applyToBody" class="w-full h-8 rounded-lg border border-amber-400/30 bg-amber-400/10 text-amber-400 hover:bg-amber-400/20 text-[12px] font-bold transition">
                        Apply to Template
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
