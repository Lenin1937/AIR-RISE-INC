<template>
  <AdminLayout title="Live Chat - Admin">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-white sm:text-3xl sm:truncate">
              Live Chat
            </h1>
            <p class="mt-1 text-sm text-gray-400">
              Live chat with clients - Admin Dashboard
            </p>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <div class="flex items-center space-x-2 text-sm text-gray-400">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
              <span>Online</span>
              <span class="mx-2">•</span>
              <span>{{ messages?.length || 0 }} messages</span>
            </div>
          </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-lg overflow-hidden" style="height: 600px;">
          <div class="flex h-full">
            <!-- Client List Sidebar -->
            <div class="w-1/3 border-r border-gray-600 bg-gray-750">
              <div class="bg-gray-700 px-4 py-3 border-b border-gray-600">
                <h3 class="text-lg font-medium text-white">Clients</h3>
                <p class="text-sm text-gray-400">{{ clients?.length || 0 }} active conversations</p>
              </div>
              <div class="overflow-y-auto h-full">
                <div v-if="!clients || clients.length === 0" class="p-4 text-center">
                  <p class="text-gray-400 text-sm">No client conversations yet</p>
                </div>
                <div v-else>
                  <button
                    v-for="client in clients"
                    :key="client.id"
                    @click="selectClient(client.id)"
                    :class="{
                      'bg-[#d4a02f] text-[#0b1e33]': selectedClientId === client.id,
                      'bg-gray-800 text-white hover:bg-gray-700': selectedClientId !== client.id
                    }"
                    class="w-full px-4 py-3 text-left border-b border-gray-600 transition-colors duration-200"
                  >
                    <div class="flex items-center space-x-3">
                      <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-white">
                          {{ client.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ client.name }}</p>
                        <p class="text-xs opacity-70 truncate">{{ client.email }}</p>
                      </div>
                    </div>
                  </button>
                </div>
              </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
              <!-- Chat Header -->
              <div class="bg-gray-700 px-6 py-4 border-b border-gray-600">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-[#d4a02f] to-[#f4b840] rounded-full flex items-center justify-center">
                      <svg class="w-4 h-4 text-[#0b1e33]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-white">
                        {{ selectedClientId ? getClientName(selectedClientId) : 'Select a client' }}
                      </h3>
                      <p class="text-sm text-gray-400">Live Chat - Admin</p>
                    </div>
                  </div>
                  <button
                    @click="refreshMessages"
                    class="inline-flex items-center px-3 py-2 border border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-300 bg-gray-700 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#d4a02f] transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                  </button>
                </div>
              </div>

              <!-- Messages Area -->
              <div 
                ref="messagesContainer"
                class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-800"
              >
                <div v-if="!selectedClientId" class="text-center py-12">
                  <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-white">Select a client</h3>
                  <p class="mt-1 text-sm text-gray-400">Choose a client from the left to start chatting</p>
                </div>

                <div v-else-if="filteredMessages.length === 0" class="text-center py-12">
                  <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-white">No messages yet</h3>
                  <p class="mt-1 text-sm text-gray-400">Start a conversation with this client!</p>
                </div>

                <!-- Chat Messages -->
                <div 
                  v-for="message in filteredMessages" 
                  :key="message.id"
                  :class="{
                    'flex justify-end': message.is_from_admin,
                    'flex justify-start': !message.is_from_admin
                  }"
                  class="flex"
                >
                  <div 
                    :class="{
                      'bg-[#d4a02f] text-[#0b1e33]': message.is_from_admin,
                      'bg-gray-700 text-white': !message.is_from_admin
                    }"
                    class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg shadow-md"
                  >
                    <div class="flex items-center space-x-2 mb-1">
                      <span class="text-xs font-medium">
                        {{ message.sender_name }}
                      </span>
                      <span 
                        :class="{
                          'text-[#0b1e33] opacity-70': message.is_from_admin,
                          'text-gray-400': !message.is_from_admin
                        }"
                        class="text-xs"
                      >
                        {{ formatTime(message.created_at) }}
                      </span>
                    </div>
                    <p class="text-sm">{{ message.message }}</p>
                  </div>
                </div>
              </div>

              <!-- Message Input -->
              <div class="bg-gray-700 px-6 py-4 border-t border-gray-600">
                <form @submit.prevent="sendMessage" class="flex space-x-4">
                  <div class="flex-1">
                    <input
                      v-model="newMessage"
                      type="text"
                      :placeholder="selectedClientId ? 'Type your message...' : 'Select a client first'"
                      :disabled="!selectedClientId"
                      class="w-full bg-gray-600 border-gray-500 rounded-lg shadow-sm focus:ring-[#d4a02f] focus:border-[#d4a02f] text-white placeholder-gray-400 sm:text-sm disabled:opacity-50"
                    />
                  </div>
                  <button
                    type="submit"
                    :disabled="!newMessage.trim() || isSending || !selectedClientId"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-[#0b1e33] bg-[#d4a02f] hover:bg-[#f4b840] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#d4a02f] disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                  >
                    <svg v-if="isSending" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    {{ isSending ? 'Sending...' : 'Send' }}
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { nextTick, onMounted, ref } from 'vue'

const props = defineProps({
  messages: Array,
  clients: Array,
  user: Object
})

// Chat state
const newMessage = ref('')
const isSending = ref(false)
const messagesContainer = ref(null)
const selectedClientId = ref(null)
const filteredMessages = ref([])

// Methods
const formatTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.abs(now - date) / 60000

  if (diffInMinutes < 1) {
    return 'Now'
  } else if (diffInMinutes < 60) {
    return `${Math.floor(diffInMinutes)}m ago`
  } else if (diffInMinutes < 1440) {
    return `${Math.floor(diffInMinutes / 60)}h ago`
  } else {
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value || !selectedClientId.value) return

  isSending.value = true
  
  try {
    const response = await axios.post('/admin/messages', {
      message: newMessage.value.trim(),
      recipient_id: selectedClientId.value
    })

    if (response.data.success) {
      // Add the new message to the local state
      props.messages.push(response.data.message)
      newMessage.value = ''
      filterMessages()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Error sending message:', error)
    alert('Failed to send message. Please try again.')
  } finally {
    isSending.value = false
  }
}

const selectClient = (clientId) => {
  selectedClientId.value = clientId
  filterMessages()
  scrollToBottom()
}

const filterMessages = () => {
  if (!selectedClientId.value) {
    filteredMessages.value = []
    return
  }
  
  filteredMessages.value = props.messages.filter(message => 
    message.client_id === selectedClientId.value
  )
}

const getClientName = (clientId) => {
  const client = props.clients.find(c => c.id === clientId)
  return client ? client.name : 'Unknown Client'
}

const refreshMessages = () => {
  router.reload({ 
    only: ['messages', 'clients'],
    onSuccess: () => {
      // Maintain the selected client after refresh
      if (selectedClientId.value) {
        filterMessages();
        scrollToBottom();
      }
    }
  });
};

// Auto-scroll to bottom when messages change
onMounted(() => {
  // Select first client by default if available
  if (props.clients && props.clients.length > 0) {
    selectedClientId.value = props.clients[0].id
    filterMessages()
  }
  scrollToBottom()
})
</script>