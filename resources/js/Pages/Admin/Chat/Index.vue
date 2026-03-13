<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    conversations: Array,
});

const search = ref('');
const selectedStatus = ref('all');

// Auto-refresh every 10 seconds
onMounted(() => {
    setInterval(() => {
        router.reload({ only: ['conversations'], preserveScroll: true });
    }, 10000);
});

const filteredConversations = computed(() => {
    return props.conversations?.filter(conv => {
        const matchesSearch = conv.meta?.sender?.name?.toLowerCase().includes(search.value.toLowerCase()) ||
                            conv.meta?.sender?.email?.toLowerCase().includes(search.value.toLowerCase());
        const matchesStatus = selectedStatus.value === 'all' || conv.status === selectedStatus.value;
        return matchesSearch && matchesStatus;
    }) || [];
});

const getTimeAgo = (timestamp) => {
    const seconds = Math.floor((Date.now() / 1000) - timestamp);
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    return `${Math.floor(seconds / 86400)}d ago`;
};
</script>

<template>
    <Head title="Live Chat - Admin" />
    
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-white">Live Chat Messages</h1>
                    <p class="text-gray-400 mt-1">Manage and respond to client messages</p>
                </div>

                <!-- Filters -->
                <div class="mb-6 flex gap-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search conversations..."
                        class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:border-[#d4a02f] focus:ring-1 focus:ring-[#d4a02f]"
                    />
                    <select
                        v-model="selectedStatus"
                        class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:border-[#d4a02f] focus:ring-1 focus:ring-[#d4a02f]"
                    >
                        <option value="all">All Status</option>
                        <option value="open">Open</option>
                        <option value="resolved">Resolved</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <!-- Conversations List -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
                    <div v-if="filteredConversations.length === 0" class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-white">No conversations</h3>
                        <p class="mt-1 text-sm text-gray-400">Start chatting with clients to see messages here</p>
                    </div>

                    <div v-else class="divide-y divide-gray-700">
                        <Link
                            v-for="conversation in filteredConversations"
                            :key="conversation.id"
                            :href="route('admin.chat.show', conversation.id)"
                            class="block hover:bg-gray-700 transition-colors duration-150"
                        >
                            <div class="p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-3 flex-1">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-gradient-to-br from-[#d4a02f] to-[#f4b840] rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-[#0b1e33]">
                                                    {{ conversation.meta?.sender?.name?.charAt(0).toUpperCase() || 'U' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-white truncate">
                                                    {{ conversation.meta?.sender?.name || 'Unknown User' }}
                                                </p>
                                                <span class="text-xs text-gray-400">
                                                    {{ getTimeAgo(conversation.last_activity_at) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-400 truncate mt-1">
                                                {{ conversation.meta?.sender?.email || 'No email' }}
                                            </p>
                                            <p class="text-sm text-gray-300 mt-1 line-clamp-1">
                                                {{ conversation.messages?.[0]?.content || 'No messages yet' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span
                                            :class="{
                                                'bg-green-500/20 text-green-400': conversation.status === 'open',
                                                'bg-gray-500/20 text-gray-400': conversation.status === 'resolved',
                                                'bg-yellow-500/20 text-yellow-400': conversation.status === 'pending',
                                            }"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ conversation.status }}
                                        </span>
                                        <div v-if="conversation.unread_count > 0" class="mt-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#d4a02f] text-[#0b1e33] text-xs font-bold rounded-full">
                                                {{ conversation.unread_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
