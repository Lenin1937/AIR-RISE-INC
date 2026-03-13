<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';

const props = defineProps({ article: { type: Object, required: true } });

const form = useForm({
    title:     props.article.title,
    category:  props.article.category,
    excerpt:   props.article.excerpt,
    content:   props.article.content,
    author:    props.article.author,
    tags:      props.article.tags || [],
    read_time: props.article.read_time,
    featured:  props.article.featured,
    published: props.article.published,
});

const tagsString = ref((props.article.tags || []).join(', '));
watch(tagsString, v => { form.tags = v.split(',').map(t => t.trim()).filter(t => t); });

function submit() {
    form.put(route('admin.articles.update', props.article.slug), { preserveScroll: true });
}

const inp = 'w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition';
const sel = 'w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-300 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition [&>option]:bg-[#0a1628]';
</script>

<template>
    <Head :title="`Edit — ${article.title}`" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Edit Article</h1>
                <p class="text-[13px] text-gray-500 mt-1 truncate max-w-lg">{{ article.title }}</p>
            </div>
            <Link :href="route('admin.articles.index')"
                class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-white/[0.10] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all self-start sm:self-auto">
                ← Back to Articles
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">

            <!-- Title -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Article Title <span class="text-amber-400">*</span></label>
                <input v-model="form.title" type="text" required :class="inp"/>
                <p v-if="form.errors.title" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.title }}</p>
            </div>

            <!-- Category + Read Time -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Category <span class="text-amber-400">*</span></label>
                    <select v-model="form.category" required :class="sel">
                        <option value="">Select Category</option>
                        <option value="Business Formation">Business Formation</option>
                        <option value="Tax & Compliance">Tax &amp; Compliance</option>
                        <option value="Getting Started">Getting Started</option>
                        <option value="Immigration">Immigration</option>
                    </select>
                    <p v-if="form.errors.category" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.category }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Read Time (minutes) <span class="text-amber-400">*</span></label>
                    <input v-model="form.read_time" type="number" min="1" required :class="inp"/>
                    <p v-if="form.errors.read_time" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.read_time }}</p>
                </div>
            </div>

            <!-- Excerpt -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Article Excerpt <span class="text-amber-400">*</span></label>
                <textarea v-model="form.excerpt" rows="3" maxlength="500" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition resize-none"></textarea>
                <p class="text-[11px] text-gray-700 mt-1.5">{{ form.excerpt?.length || 0 }}/500 characters</p>
                <p v-if="form.errors.excerpt" class="text-[11px] text-red-400 mt-1">{{ form.errors.excerpt }}</p>
            </div>

            <!-- Content -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-3">Article Content <span class="text-amber-400">*</span></label>
                <RichTextEditor v-model="form.content"/>
                <p v-if="form.errors.content" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.content }}</p>
            </div>

            <!-- Author + Tags -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Author <span class="text-amber-400">*</span></label>
                    <input v-model="form.author" type="text" required :class="inp"/>
                    <p v-if="form.errors.author" class="text-[11px] text-red-400 mt-1.5">{{ form.errors.author }}</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Tags <span class="text-[10px] text-gray-700 normal-case tracking-normal">(comma-separated)</span></label>
                    <input v-model="tagsString" type="text" placeholder="Delaware, LLC, Business Formation" :class="inp"/>
                </div>
            </div>

            <!-- Toggles + Submit -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                <div class="flex flex-wrap gap-6">
                    <!-- Featured toggle -->
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <button type="button" @click="form.featured = !form.featured"
                            :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors focus:outline-none', form.featured ? 'bg-amber-400' : 'bg-white/[0.10]']">
                            <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', form.featured ? 'translate-x-5' : 'translate-x-0']"></span>
                        </button>
                        <span class="text-[13px] font-medium text-gray-300">Featured Article</span>
                    </label>
                    <!-- Published toggle -->
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <button type="button" @click="form.published = !form.published"
                            :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors focus:outline-none', form.published ? 'bg-emerald-500' : 'bg-white/[0.10]']">
                            <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', form.published ? 'translate-x-5' : 'translate-x-0']"></span>
                        </button>
                        <span class="text-[13px] font-medium text-gray-300">Published</span>
                    </label>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.articles.index')"
                        class="inline-flex items-center justify-center px-5 h-9 rounded-xl border border-white/[0.10] bg-white/[0.04] text-[13px] font-medium text-gray-300 hover:text-white hover:bg-white/[0.08] transition-all">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 px-5 h-9 rounded-xl bg-amber-400 text-[13px] font-bold text-[#0b1e33] hover:bg-amber-300 disabled:opacity-50 transition-all shadow-lg shadow-amber-500/20">
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        {{ form.processing ? 'Updating…' : 'Update Article' }}
                    </button>
                </div>
            </div>

        </form>
    </AdminLayout>
</template>
