<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    templates: Array,
    segments:  Array,
});

const form = useForm({
    name:         '',
    template_id:  '',
    segment_id:   '',
    from_name:    'CORPIUS',
    from_email:   'noreply@corpius.com',
    reply_to:     '',
    scheduled_at: '',
});

const submit = () => form.post(route('admin.email.campaigns.store'));

const selectedTemplate = computed(() => props.templates.find(t => t.id == form.template_id));
const selectedSegment  = computed(() => props.segments.find(s => s.id == form.segment_id));
</script>

<template>
    <Head title="New Campaign" />
    <AdminLayout>
        <div class="flex items-center gap-3 mb-8">
            <Link :href="route('admin.email.campaigns.index')" class="w-8 h-8 rounded-lg border border-white/[0.08] bg-white/[0.04] text-gray-500 hover:text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-white">New Campaign</h1>
                <p class="text-sm text-gray-500 mt-0.5">Configure and schedule a campaign</p>
            </div>
        </div>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Basic info -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 space-y-4">
                    <h2 class="text-[13px] font-bold text-white">Campaign Details</h2>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Campaign Name *</label>
                        <input v-model="form.name" type="text" required class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Email Template</label>
                        <select v-model="form.template_id" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40">
                            <option value="">— No template —</option>
                            <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }} ({{ t.category }})</option>
                        </select>
                        <p v-if="selectedTemplate" class="text-[11px] text-gray-500 mt-1">Subject: {{ selectedTemplate.subject }}</p>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Audience Segment <span class="text-gray-600 font-normal">(leave blank = all subscribed)</span></label>
                        <select v-model="form.segment_id" class="w-full bg-[#0a1628] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40">
                            <option value="">All Subscribed Contacts</option>
                            <option v-for="s in segments" :key="s.id" :value="s.id">{{ s.name }} ({{ s.contact_count }} contacts)</option>
                        </select>
                    </div>
                </div>

                <!-- Sender info -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 space-y-4">
                    <h2 class="text-[13px] font-bold text-white">Sender Settings</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">From Name *</label>
                            <input v-model="form.from_name" type="text" required class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">From Email *</label>
                            <input v-model="form.from_email" type="email" required class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Reply-To <span class="text-gray-600 font-normal">(optional)</span></label>
                        <input v-model="form.reply_to" type="email" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 space-y-4">
                    <h2 class="text-[13px] font-bold text-white">Scheduling</h2>
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-400 mb-1.5">Scheduled At <span class="text-gray-600 font-normal">(leave blank to save as draft)</span></label>
                        <input v-model="form.scheduled_at" type="datetime-local" class="w-full bg-white/[0.04] border border-white/[0.08] text-white text-sm rounded-lg px-3 h-9 focus:outline-none focus:border-amber-400/40"/>
                    </div>
                    <p class="text-[11px] text-gray-600">You can manually send the campaign from the campaigns list at any time.</p>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('admin.email.campaigns.index')" class="px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-gray-300 hover:text-white text-[13px] font-semibold transition flex items-center">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-5 h-9 rounded-lg bg-amber-400 text-[#0b1e33] hover:bg-amber-300 text-[13px] font-semibold transition disabled:opacity-60 flex items-center">
                        {{ form.scheduled_at ? 'Schedule Campaign' : 'Save as Draft' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
