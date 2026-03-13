<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const { __ } = useTranslations();

const props = defineProps({
    messages: Array,
    stats: Object,
    user: Object,
    chatwootToken: String,
    identifierHash: String,
});

const widgetUrl = computed(() => {
    if (!props.chatwootToken) return null;
    return `https://chatwoot.corpius.net/widget?website_token=${props.chatwootToken}`;
});
</script>

<template>
    <Head :title="__('client.messages')" />
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">

            <!-- Page header -->
            <div>
                <h1 class="text-[22px] font-bold text-white tracking-tight">{{ __('client.messages') }}</h1>
                <p class="mt-0.5 text-[13px] text-gray-400">{{ __('client.messages_subtitle') }}</p>
            </div>

            <!-- Chat card -->
            <div class="flex flex-col rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden"
                 style="height:620px; box-shadow:0 0 40px 0 rgba(244,184,64,.06)">

                <!-- Chat header bar -->
                <div class="flex-shrink-0 flex items-center justify-between px-5 py-3.5 border-b border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-9 h-9 rounded-full bg-amber-400/20 flex items-center justify-center">
                                <svg style="width:16px;height:16px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-green-400 border-2 border-[#0c1c30]"/>
                        </div>
                        <div>
                            <p class="text-[13px] font-bold text-white">CORPIUS Support</p>
                            <p class="text-[11px] text-green-400 font-medium">● Online</p>
                        </div>
                    </div>
                    <div class="text-[11px] text-gray-500">Typical reply: &lt; 1 hour</div>
                </div>

                <!-- Chatwoot widget iframe -->
                <div class="flex-1 relative">
                    <iframe
                        v-if="widgetUrl"
                        :src="widgetUrl"
                        class="absolute inset-0 w-full h-full border-0"
                        allow="microphone; camera"
                    />
                    <div v-else class="flex flex-col items-center justify-center h-full text-center px-8">
                        <p class="text-[14px] font-bold text-white mb-1">{{ __('client.chat_unavailable') }}</p>
                        <p class="text-[12px] text-gray-500">{{ __('client.contact_support_email') }}</p>
                    </div>
                </div>
            </div>

            <!-- Info cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-amber-400/20 bg-amber-400/[0.04] p-5">
                    <div class="w-9 h-9 rounded-xl bg-amber-400/20 flex items-center justify-center mb-3">
                        <svg style="width:16px;height:16px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-[13px] font-bold text-white mb-1">{{ __('client.general_questions') }}</p>
                    <p class="text-[12px] text-gray-400">{{ __('client.general_questions_desc') }}</p>
                </div>
                <div class="rounded-2xl border border-green-400/20 bg-green-400/[0.04] p-5">
                    <div class="w-9 h-9 rounded-xl bg-green-400/20 flex items-center justify-center mb-3">
                        <svg style="width:16px;height:16px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-[13px] font-bold text-white mb-1">{{ __('client.order_support') }}</p>
                    <p class="text-[12px] text-gray-400">{{ __('client.order_support_desc') }}</p>
                </div>
                <div class="rounded-2xl border border-yellow-400/20 bg-yellow-400/[0.04] p-5">
                    <div class="w-9 h-9 rounded-xl bg-yellow-400/20 flex items-center justify-center mb-3">
                        <svg style="width:16px;height:16px" class="text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-[13px] font-bold text-white mb-1">{{ __('client.document_issues') }}</p>
                    <p class="text-[12px] text-gray-400">{{ __('client.document_issues_desc') }}</p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>


