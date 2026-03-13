<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { nextTick, onMounted, ref } from 'vue';

const props = defineProps({
    conversation: Object,
    messages: Array,
});

const messageContainer = ref(null);
const messageForm = useForm({
    message: '',
    private: false,
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
    // Auto-refresh every 5 seconds
    setInterval(() => {
        router.reload({ only: ['messages'], preserveScroll: true });
    }, 5000);
});

const sendMessage = () => {
    if (!messageForm.message.trim()) return;
    
    messageForm.post(`/admin/chat/${props.conversation.id}/send`, {
        preserveScroll: true,
        onSuccess: () => {
            messageForm.reset();
            scrollToBottom();
        },
    });
};

const toggleStatus = () => {
    router.post(route('admin.chat.toggleStatus', props.conversation.id), {}, {
        preserveScroll: true,
    });
};

const formatTime = (timestamp) => {
    return new Date(timestamp * 1000).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Chat with ${conversation?.meta?.sender?.name || 'User'}`" />
    
    <AdminLayout>
        <div class="h-screen flex flex-col">
            <!-- Header -->
            <div class="bg-gray-800 border-b border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between max-w-7xl mx-auto">
                    <div class="flex items-center space-x-4">
                        <Link :href="route('admin.chat.index')" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-[#d4a02f] to-[#f4b840] rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-[#0b1e33]">
                                    {{ conversation?.meta?.sender?.name?.charAt(0).toUpperCase() || 'U' }}
                                </span>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-white">
                                    {{ conversation?.meta?.sender?.name || 'Unknown User' }}
                                </h2>
                                <p class="text-sm text-gray-400">
                                    {{ conversation?.meta?.sender?.email || 'No email' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button
                            @click="toggleStatus"
                            :class="{
                                'bg-green-500/20 text-green-400 hover:bg-green-500/30': conversation?.status === 'open',
                                'bg-gray-500/20 text-gray-400 hover:bg-gray-500/30': conversation?.status === 'resolved',
                            }"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150"
                        >
                            {{ conversation?.status === 'open' ? 'Mark Resolved' : 'Reopen' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="flex-1 overflow-hidden bg-[#0b1e33]">
                <div class="max-w-4xl mx-auto h-full flex flex-col">
                    <!-- Messages -->
                    <div ref="messageContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            :class="message.message_type === 'outgoing' ? 'flex justify-end' : 'flex justify-start'"
                        >
                            <div
                                :class="{
                                    'bg-[#d4a02f] text-[#0b1e33]': message.message_type === 'outgoing',
                                    'bg-gray-700 text-white': message.message_type === 'incoming',
                                    'bg-orange-500/20 text-orange-300 border border-orange-500/30': message.private,
                                }"
                                class="max-w-xs lg:max-w-md px-4 py-3 rounded-2xl shadow-lg"
                            >
                                <div v-if="message.private" class="flex items-center text-xs mb-1 opacity-75">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    Private note
                                </div>
                                <p class="text-sm whitespace-pre-wrap break-words">{{ message.content }}</p>
                                <p class="text-xs mt-1 opacity-60">
                                    {{ formatTime(message.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="border-t border-gray-700 bg-gray-800 p-4">
                        <form @submit.prevent="sendMessage" class="space-y-3">
                            <div class="flex items-center space-x-2">
                                <label class="flex items-center space-x-2 text-sm text-gray-400 cursor-pointer">
                                    <input
                                        v-model="messageForm.private"
                                        type="checkbox"
                                        class="rounded border-gray-600 text-[#d4a02f] focus:ring-[#d4a02f] focus:ring-offset-gray-800"
                                    />
                                    <span>Private note (only visible to team)</span>
                                </label>
                            </div>
                            <div class="flex space-x-2">
                                <textarea
                                    v-model="messageForm.message"
                                    placeholder="Type your message..."
                                    rows="2"
                                    class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:border-[#d4a02f] focus:ring-1 focus:ring-[#d4a02f] resize-none"
                                    @keydown.enter.exact.prevent="sendMessage"
                                ></textarea>
                                <button
                                    type="submit"
                                    :disabled="messageForm.processing || !messageForm.message.trim()"
                                    class="px-6 py-2 bg-[#d4a02f] text-[#0b1e33] rounded-lg font-medium hover:bg-[#f4b840] disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                                >
                                    <svg v-if="messageForm.processing" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
