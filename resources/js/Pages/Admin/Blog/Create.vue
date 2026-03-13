<script setup>
import RichTextEditor from '@/Components/RichTextEditor.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const form = useForm({
    title:            '',
    excerpt:          '',
    content:          '',
    category:         '',
    author:           'CORPIUS Team',
    tags:             '',
    read_time:        5,
    featured:         false,
    published:        false,
    meta_title:       '',
    meta_description: '',
    image:            null,
});

const imagePreview = ref(null);

function onImageChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;
    const reader = new FileReader();
    reader.onload = ev => { imagePreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function removeImage() {
    form.image = null;
    imagePreview.value = null;
}

function submitForm() {
    form.post(route('admin.blog.store'), { preserveScroll: true, forceFormData: true });
}

// ─── AI Generation ──────────────────────────────────────────────────────────
const aiPanel      = ref(false);
const aiTopic      = ref('');
const aiCategory   = ref('');
const aiTone       = ref('professional');
const aiGenerating = ref(false);
const aiError      = ref('');

const tones = [
    { value: 'professional',  label: 'Professional' },
    { value: 'friendly',      label: 'Friendly' },
    { value: 'educational',   label: 'Educational' },
    { value: 'technical',     label: 'Technical' },
];

async function generateWithAI() {
    if (!aiTopic.value.trim()) return;
    aiGenerating.value = true;
    aiError.value      = '';

    try {
        const { data } = await axios.post(route('admin.blog.generate-ai'), {
            topic:    aiTopic.value.trim(),
            category: aiCategory.value || undefined,
            tone:     aiTone.value,
        });

        form.title            = data.title            ?? '';
        form.excerpt          = data.excerpt          ?? '';
        form.content          = data.content          ?? '';
        form.tags             = Array.isArray(data.tags) ? data.tags.join(', ') : (data.tags ?? '');
        form.category         = data.category         ?? aiCategory.value ?? '';
        form.meta_title       = data.meta_title       ?? '';
        form.meta_description = data.meta_description ?? '';
        form.read_time        = data.read_time        ?? 5;

        aiPanel.value = false;
        aiTopic.value = '';
    } catch (err) {
        const status = err.response?.status;
        const msg = err.response?.data?.error || err.response?.data?.message || err.message || 'Unknown error';
        aiError.value = status ? `Error ${status}: ${msg}` : `Network error: ${msg}`;
        console.error('AI generate error:', err.response?.data ?? err);
    } finally {
        aiGenerating.value = false;
    }
}

const inp  = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
const sel  = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
const area = 'w-full px-3.5 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition resize-none';
</script>

<template>
    <Head title="New Blog Post" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">New Blog Post</h1>
                <p class="text-[13px] text-gray-500 mt-1">Write a new blog post and upload a featured image</p>
            </div>
            <div class="flex items-center gap-3 self-start sm:self-auto">
                <!-- AI Generate button -->
                <button type="button" @click="aiPanel = !aiPanel"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-violet-500/40 bg-violet-500/10 text-[13px] font-semibold text-violet-300 hover:bg-violet-500/20 hover:text-violet-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    AI Generate
                </button>
                <Link :href="route('admin.blog.index')"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                    ← Back to Blog
                </Link>
            </div>
        </div>

        <!-- ── AI Generation Panel ────────────────────────────────────────── -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2">
            <div v-if="aiPanel" class="mb-6 rounded-2xl border border-violet-500/30 bg-violet-500/[0.06] p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-violet-500/20">
                        <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-white">AI Blog Post Generator</h3>
                        <p class="text-[12px] text-gray-500">Enter a topic and Gemini AI will write the full post for you</p>
                    </div>
                </div>

                <!-- Topic -->
                <div class="mb-4">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Topic / Title Idea <span class="text-violet-400">*</span></label>
                    <input v-model="aiTopic" type="text"
                        placeholder="e.g. How to form an LLC in Delaware as a foreigner"
                        class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-violet-500/30 text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-violet-400/60 focus:ring-1 focus:ring-violet-400/20 transition"
                        @keyup.enter="generateWithAI"/>
                </div>

                <!-- Category + Tone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Category (optional)</label>
                        <select v-model="aiCategory"
                            class="w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-violet-500/30 text-[13px] text-gray-300 focus:outline-none focus:border-violet-400/60 focus:ring-1 focus:ring-violet-400/20 transition [&>option]:bg-[#0a1628]">
                            <option value="">Auto-detect</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Tone</label>
                        <select v-model="aiTone"
                            class="w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-violet-500/30 text-[13px] text-gray-300 focus:outline-none focus:border-violet-400/60 focus:ring-1 focus:ring-violet-400/20 transition [&>option]:bg-[#0a1628]">
                            <option v-for="t in tones" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>
                </div>

                <!-- Error -->
                <p v-if="aiError" class="text-[12px] text-red-400 mb-4">{{ aiError }}</p>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button type="button" @click="generateWithAI"
                        :disabled="aiGenerating || !aiTopic.trim()"
                        class="inline-flex items-center gap-2 px-5 h-9 rounded-xl bg-violet-500 text-[13px] font-bold text-white hover:bg-violet-400 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-violet-500/20">
                        <svg v-if="aiGenerating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ aiGenerating ? 'Generating… (30-60 sec)' : 'Generate Post' }}
                    </button>
                    <button type="button" @click="aiPanel = false; aiError = ''"
                        class="inline-flex items-center justify-center px-4 h-9 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-400 hover:text-white hover:bg-white/[0.08] transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </transition>

        <form @submit.prevent="submitForm" class="space-y-6" enctype="multipart/form-data">

            <!-- Title -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Post Title <span class="text-amber-400">*</span></label>
                <input v-model="form.title" type="text" required placeholder="Enter blog post title" :class="inp"/>
                <p v-if="form.errors.title" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.title }}</p>
            </div>

            <!-- Featured Image Upload -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-3">Featured Image</label>

                <!-- Preview -->
                <div v-if="imagePreview" class="mb-4 relative group w-full max-w-lg">
                    <img :src="imagePreview" alt="Preview" class="w-full h-56 object-cover rounded-xl border border-white/[0.08]"/>
                    <button type="button" @click="removeImage"
                        class="absolute top-2 right-2 w-8 h-8 rounded-full bg-red-500/80 text-white flex items-center justify-center hover:bg-red-500 transition opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Drop zone -->
                <label v-if="!imagePreview"
                    class="flex flex-col items-center justify-center w-full h-40 rounded-xl border-2 border-dashed border-white/[0.12] bg-white/[0.02] hover:bg-white/[0.04] hover:border-amber-400/40 transition cursor-pointer">
                    <svg class="w-10 h-10 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-[13px] text-gray-500">Click to upload image</p>
                    <p class="text-[11px] text-gray-700 mt-1">PNG, JPG, GIF, WebP — max 4 MB</p>
                    <input type="file" accept="image/*" @change="onImageChange" class="hidden"/>
                </label>

                <!-- Change button when image selected -->
                <label v-else class="mt-3 inline-flex items-center gap-2 px-4 h-8 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[12px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Change Image
                    <input type="file" accept="image/*" @change="onImageChange" class="hidden"/>
                </label>

                <p v-if="form.errors.image" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.image }}</p>
            </div>

            <!-- Category + Read Time -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Category <span class="text-amber-400">*</span></label>
                    <select v-model="form.category" required :class="sel">
                        <option value="">Select a category</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                    <p v-if="form.errors.category" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.category }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Read Time (min) <span class="text-amber-400">*</span></label>
                    <input v-model="form.read_time" type="number" min="1" max="120" required :class="inp"/>
                    <p v-if="form.errors.read_time" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.read_time }}</p>
                </div>
            </div>

            <!-- Excerpt -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Excerpt / Summary</label>
                <textarea v-model="form.excerpt" rows="3" maxlength="600" placeholder="Short summary shown on blog listing…" :class="area"></textarea>
                <p class="text-[11px] text-gray-700 mt-1.5">{{ form.excerpt?.length || 0 }}/600 characters</p>
                <p v-if="form.errors.excerpt" class="text-[11px] text-red-400 mt-1">{{ form.errors.excerpt }}</p>
            </div>

            <!-- Content -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-3">Post Content <span class="text-amber-400">*</span></label>
                <RichTextEditor v-model="form.content"/>
                <p v-if="form.errors.content" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.content }}</p>
            </div>

            <!-- Author + Tags -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Author</label>
                    <input v-model="form.author" type="text" :class="inp" placeholder="CORPIUS Team"/>
                    <p v-if="form.errors.author" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.author }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Tags <span class="text-[10px] text-gray-700 normal-case tracking-normal">(comma-separated)</span></label>
                    <input v-model="form.tags" type="text" :class="inp" placeholder="LLC, Business, Delaware"/>
                </div>
            </div>

            <!-- SEO -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 space-y-4">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600">SEO (Optional)</label>
                <div>
                    <label class="block text-[10px] text-gray-600 mb-1.5">Meta Title</label>
                    <input v-model="form.meta_title" type="text" :class="inp" placeholder="Defaults to post title"/>
                </div>
                <div>
                    <label class="block text-[10px] text-gray-600 mb-1.5">Meta Description</label>
                    <textarea v-model="form.meta_description" rows="2" maxlength="500" :class="area" placeholder="Defaults to excerpt"></textarea>
                </div>
            </div>

            <!-- Toggles + Submit -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <button type="button" @click="form.featured = !form.featured"
                            :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors focus:outline-none', form.featured ? 'bg-amber-400' : 'bg-white/[0.10]']">
                            <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', form.featured ? 'translate-x-5' : 'translate-x-0']"></span>
                        </button>
                        <span class="text-[13px] font-medium text-gray-300">Featured Post</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <button type="button" @click="form.published = !form.published"
                            :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors focus:outline-none', form.published ? 'bg-emerald-500' : 'bg-white/[0.10]']">
                            <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', form.published ? 'translate-x-5' : 'translate-x-0']"></span>
                        </button>
                        <span class="text-[13px] font-medium text-gray-300">Publish Now</span>
                    </label>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.blog.index')"
                        class="inline-flex items-center justify-center px-5 h-9 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 px-5 h-9 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 disabled:opacity-50 transition-all shadow-lg shadow-amber-500/20">
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        {{ form.processing ? 'Publishing…' : 'Create Post' }}
                    </button>
                </div>
            </div>

        </form>
    </AdminLayout>
</template>
