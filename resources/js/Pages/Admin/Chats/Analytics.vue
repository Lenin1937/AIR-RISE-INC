<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    topPages: Array,
    recentChats: Array,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getPageIcon = (pageType) => {
    const icons = {
        'service_c_corp': '🏢',
        'service_s_corp': '🏛️',
        'service_llc': '💼',
        'service_nonprofit': '❤️',
        'service_greencard': '🌍',
        'pricing': '💰',
        'dashboard': '📊',
        'home': '🏠',
    };
    return icons[pageType] || '📄';
};
</script>

<template>
    <Head title="AI Chat Analytics" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">AI Chat Analytics</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Monitor chat performance and engagement metrics
                        </p>
                    </div>
                    <Link
                        :href="route('admin.chats.index')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        View All Chats
                    </Link>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Chats -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Chats</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_chats }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active Chats -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Active Chats</p>
                                <p class="mt-2 text-3xl font-bold text-green-600">{{ stats.active_chats }}</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Leads Generated -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Leads Generated</p>
                                <p class="mt-2 text-3xl font-bold text-yellow-600">{{ stats.leads }}</p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Messages -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Messages</p>
                                <p class="mt-2 text-3xl font-bold text-purple-600">{{ stats.total_messages }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Period Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-sm border border-blue-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-700">Today</p>
                                <p class="mt-2 text-2xl font-bold text-blue-900">{{ stats.chats_today }}</p>
                                <p class="mt-1 text-xs text-blue-600">New conversations</p>
                            </div>
                            <svg class="h-10 w-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-700">This Week</p>
                                <p class="mt-2 text-2xl font-bold text-green-900">{{ stats.chats_this_week }}</p>
                                <p class="mt-1 text-xs text-green-600">New conversations</p>
                            </div>
                            <svg class="h-10 w-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm border border-purple-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-700">This Month</p>
                                <p class="mt-2 text-2xl font-bold text-purple-900">{{ stats.chats_this_month }}</p>
                                <p class="mt-1 text-xs text-purple-600">New conversations</p>
                            </div>
                            <svg class="h-10 w-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Pages -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Top Pages by Chats</h2>
                            <p class="text-sm text-gray-500">Most popular pages for chat interactions</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="(page, index) in topPages"
                                    :key="index"
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex items-center space-x-3">
                                        <span class="text-2xl">{{ getPageIcon(page.page_type) }}</span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ page.page_name }}</p>
                                            <p class="text-xs text-gray-500">{{ page.page_type }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-corpius-navy">{{ page.chat_count }}</p>
                                        <p class="text-xs text-gray-500">chats</p>
                                    </div>
                                </div>

                                <div v-if="topPages.length === 0" class="text-center py-8">
                                    <p class="text-sm text-gray-500">No chat data available yet</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Chats -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Conversations</h2>
                            <p class="text-sm text-gray-500">Latest chat activity</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <Link
                                    v-for="chat in recentChats"
                                    :key="chat.id"
                                    :href="route('admin.chats.show', chat.id)"
                                    class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ chat.user?.name || 'Guest' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">{{ chat.page_name }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(chat.created_at) }}</p>
                                        </div>
                                        <div class="flex flex-col items-end space-y-1">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs font-semibold rounded-full',
                                                    chat.status === 'active' ? 'bg-corpius-navy text-white' : 'bg-gray-100 text-gray-800'
                                                ]"
                                            >
                                                {{ chat.status }}
                                            </span>
                                            <span v-if="chat.is_lead" class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Lead
                                            </span>
                                        </div>
                                    </div>
                                </Link>

                                <div v-if="recentChats.length === 0" class="text-center py-8">
                                    <p class="text-sm text-gray-500">No recent chats</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
