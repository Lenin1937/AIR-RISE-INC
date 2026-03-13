<script setup>
import { usePage } from '@inertiajs/vue3';
import { useStream } from '@laravel/stream-vue';
import { computed, ref } from 'vue';

const page = usePage();
const isOpen = ref(false);
const messages = ref([]);
const sessionId = ref(null);
const chatId = ref(null);
const inputMessage = ref('');
const messagesContainer = ref(null);

// Get page context
const pageContext = computed(() => {
    const url = window.location.href;
    const pathname = window.location.pathname;
    
    let context = `User is viewing: ${pathname}\n`;
    
    if (pathname === '/') {
        context += 'This is the homepage. Provide general information about CORPIUS services.';
    } else if (pathname.includes('/services/c-corporation')) {
        context += 'Page: C-Corporation Formation Service\nFocus on: C-Corp benefits, unlimited shareholders, separate legal entity, best for large businesses.';
    } else if (pathname.includes('/services/s-corporation')) {
        context += 'Page: S-Corporation Formation Service\nFocus on: S-Corp tax benefits, pass-through taxation, avoiding double taxation, best for SMBs.';
    } else if (pathname.includes('/services/llc')) {
        context += 'Page: LLC Formation Service\nFocus on: LLC simplicity, flexibility, pass-through taxation, best for small businesses and startups.';
    } else if (pathname.includes('/services/nonprofit')) {
        context += 'Page: Nonprofit Formation\nFocus on: Tax-exempt status, grants, charitable organizations.';
    } else if (pathname.includes('/services/green-card')) {
        context += 'Page: Green Card Lottery Assistance\nFocus on: Immigration services, DV lottery application help.';
    } else if (pathname.includes('/pricing')) {
        context += 'Page: Pricing\nFocus on: Package options, pricing tiers, and value propositions.';
    } else if (pathname.includes('/about')) {
        context += 'Page: About Us\nFocus on: Company background, mission, values, team.';
    } else if (pathname.includes('/contact')) {
        context += 'Page: Contact Us\nFocus on: How to reach support, consultation booking.';
    } else if (pathname.includes('/dashboard')) {
        context += 'Page: Client Dashboard\nFocus on: User account, orders, documents, support.';
    } else if (pathname.includes('/admin')) {
        context += 'Page: Admin Dashboard\nFocus on: Administrative functions and management.';
    }
    
    return context;
});

// Setup streaming with useStream hook
const { data, isFetching, isStreaming, send: sendStream, cancel } = useStream('/api/chat/stream', {
    onData: (chunk) => {
        // Update the last assistant message with the streaming content
        const lastMessage = messages.value[messages.value.length - 1];
        if (lastMessage && lastMessage.role === 'assistant') {
            try {
                // Split chunk by newlines to handle multiple JSON objects
                const lines = chunk.split('\n').filter(line => line.trim());
                
                for (const line of lines) {
                    const parsed = JSON.parse(line);
                    
                    if (parsed.type === 'content' && parsed.data) {
                        lastMessage.content += parsed.data;
                    } else if (parsed.type === 'error') {
                        lastMessage.content = parsed.data;
                        lastMessage.isError = true;
                    }
                }
            } catch (e) {
                // Skip malformed chunks
                console.warn('Failed to parse chunk:', e);
            }
        }
        scrollToBottom();
    },
    onError: (error) => {
        console.error('Stream error:', error);
        const lastMessage = messages.value[messages.value.length - 1];
        if (lastMessage && lastMessage.role === 'assistant' && !lastMessage.content) {
            lastMessage.content = 'Sorry, I encountered an error. Please try again.';
            lastMessage.isError = true;
        }
    }
});

// Toggle chat window
const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && !sessionId.value) {
        startNewSession();
    }
};

// Start new chat session
const startNewSession = async () => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        
        const response = await fetch('/api/chat/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                page_url: window.location.href,
                page_name: document.title,
                page_context: pageContext.value,
            }),
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        sessionId.value = data.session_id;
        chatId.value = data.chat_id;
        
        // Load existing history if any
        await loadHistory();
        
    } catch (error) {
        console.error('Failed to start session:', error);
        // Add error message to chat
        messages.value.push({
            role: 'assistant',
            content: 'Sorry, I\'m having trouble connecting. Please refresh the page and try again.',
            created_at: new Date().toISOString(),
            isError: true,
        });
    }
};

// Load chat history
const loadHistory = async () => {
    if (!sessionId.value) return;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const response = await fetch(`/api/chat/history/${sessionId.value}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            credentials: 'same-origin',
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        messages.value = data.messages.map(msg => ({
            ...msg,
            isError: false
        }));
        scrollToBottom();
    } catch (error) {
        console.error('Failed to load history:', error);
    }
};

// Send message
const sendMessage = () => {
    if (!inputMessage.value.trim() || !sessionId.value) return;
    
    const userMsg = inputMessage.value.trim();
    inputMessage.value = '';
    
    // Add user message to UI
    messages.value.push({
        role: 'user',
        content: userMsg,
        created_at: new Date().toISOString(),
    });
    
    // Add empty assistant message for streaming
    messages.value.push({
        role: 'assistant',
        content: '',
        created_at: new Date().toISOString(),
        isError: false,
    });
    
    scrollToBottom();
    
    // Send to backend streaming endpoint
    sendStream({
        session_id: sessionId.value,
        message: userMsg,
        page_context: pageContext.value,
    });
};

// Handle Enter key
const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

// Scroll to bottom
const scrollToBottom = () => {
    setTimeout(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    }, 50);
};

// Format timestamp
const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

// Clear chat
const clearChat = () => {
    if (confirm('Are you sure you want to clear this chat?')) {
        messages.value = [];
        sessionId.value = null;
        chatId.value = null;
        startNewSession();
    }
};
</script>

<template>
    <div class="ai-chat-widget">
        <!-- Floating Chat Button -->
        <button 
            @click="toggleChat"
            class="chat-toggle-btn"
            :class="{ 'open': isOpen }"
            aria-label="Toggle AI Chat"
        >
            <!-- AI Widget Icon -->
            <img v-if="!isOpen" src="/images/ai-widget-icon.jpg" alt="AI" class="w-full h-full object-cover rounded-full"/>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Chat Window -->
        <transition name="slide-up">
            <div v-if="isOpen" class="chat-window">
                <!-- Header -->
                <div class="chat-header">
                    <div class="flex items-center space-x-3">
                        <div class="chat-avatar">
                            <img src="/images/ai-widget-icon.jpg" alt="AI" class="w-full h-full object-cover rounded-full"/>
                        </div>
                        <div>
                            <h3 class="chat-title">CORPIUS AI Assistant</h3>
                            <p class="chat-subtitle">
                                <span v-if="isStreaming" class="status-indicator pulsing"></span>
                                <span v-else-if="isFetching" class="status-indicator connecting"></span>
                                <span v-else class="status-indicator active"></span>
                                {{ isStreaming ? 'Typing...' : isFetching ? 'Connecting...' : 'Online' }}
                            </p>
                        </div>
                    </div>
                    <button @click="clearChat" class="clear-btn" title="Clear chat">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                <!-- Messages -->
                <div ref="messagesContainer" class="chat-messages">
                    <div v-if="messages.length === 0" class="empty-state">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">Welcome to CORPIUS!</h4>
                        <p class="text-sm text-gray-500">Ask me anything about our services, pricing, or business formation.</p>
                    </div>

                    <div 
                        v-for="(message, index) in messages" 
                        :key="index"
                        class="message"
                        :class="message.role"
                    >
                        <div class="message-bubble" :class="{ 'error': message.isError }">
                            <div class="message-content" v-html="message.content.replace(/\n/g, '<br>')"></div>
                            <div class="message-time">{{ formatTime(message.created_at) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="chat-input-container">
                    <textarea
                        v-model="inputMessage"
                        @keypress="handleKeyPress"
                        placeholder="Type your message..."
                        class="chat-input"
                        rows="1"
                        :disabled="isStreaming || isFetching"
                    ></textarea>
                    <button 
                        @click="sendMessage" 
                        class="send-btn"
                        :disabled="!inputMessage.trim() || isStreaming || isFetching"
                    >
                        <svg v-if="!isStreaming && !isFetching" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Powered by footer -->
                <div class="powered-by">
                    <span>Powered by</span>
                    <img src="/images/ai-widget-icon.jpg" alt="REVOLD AI" class="powered-logo" />
                    <span class="powered-name">REVOLD AI</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.ai-chat-widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
}

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #d4a02f 0%, #b8872a 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(212, 160, 47, 0.4);
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    overflow: hidden;
    padding: 0;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(212, 160, 47, 0.6);
}

.chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 400px;
    height: 600px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    background: linear-gradient(135deg, #d4a02f 0%, #b8872a 100%);
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-title {
    font-weight: 600;
    font-size: 16px;
    margin: 0;
}

.chat-subtitle {
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 2px;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
}

.status-indicator.pulsing {
    animation: pulse 1.5s ease-in-out infinite;
}

.status-indicator.connecting {
    background: #fbbf24;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.clear-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: background 0.2s;
}

.clear-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f9fafb;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.message {
    margin-bottom: 16px;
    display: flex;
}

.message.user {
    justify-content: flex-end;
}

.message.assistant {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 75%;
    padding: 12px 16px;
    border-radius: 16px;
    position: relative;
}

.message.user .message-bubble {
    background: linear-gradient(135deg, #d4a02f 0%, #b8872a 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message.assistant .message-bubble {
    background: white;
    color: #1f2937;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.message-bubble.error {
    background: #fee2e2 !important;
    color: #991b1b !important;
}

.message-content {
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 6px;
}

.chat-input-container {
    padding: 16px 20px;
    background: white;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.chat-input {
    flex: 1;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    resize: none;
    max-height: 120px;
    font-family: inherit;
}

.chat-input:focus {
    outline: none;
    border-color: #d4a02f;
}

.chat-input:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
}

.send-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #d4a02f 0%, #b8872a 100%);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.send-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 160, 47, 0.4);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.powered-by {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 6px 0 8px;
    font-size: 11px;
    color: #9ca3af;
    background: white;
    border-top: 1px solid #f3f4f6;
}

.powered-logo {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    object-fit: cover;
}

.powered-name {
    font-weight: 600;
    color: #6b7280;
}

.slide-up-enter-active, .slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.slide-up-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

/* Mobile responsive */
@media (max-width: 640px) {
    .chat-window {
        width: calc(100vw - 32px);
        height: calc(100vh - 120px);
        bottom: 80px;
        right: 16px;
    }
}
</style>
