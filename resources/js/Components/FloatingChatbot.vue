<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const isOpen = ref(false);
const messages = ref([]);
const userInput = ref('');
const isLoading = ref(false);
const contextType = ref('general');
const chatContainer = ref(null);

const STORAGE_KEY = 'bilik_chatbot_history';
const CONTEXT_KEY = 'bilik_chatbot_context';
const MAX_MESSAGES = 50;

const contextOptions = {
    general: 'Umum',
    kamus: 'Tentang Kamus',
    penerjemah: 'Tentang Penerjemah',
    pembelajaran: 'Tentang Pembelajaran',
    tentang: 'Tentang Bilikbecakap'
};

const loadChatHistory = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        const storedContext = localStorage.getItem(CONTEXT_KEY);

        if (stored) {
            const parsed = JSON.parse(stored);
            messages.value = parsed.map(msg => ({
                ...msg,
                timestamp: new Date(msg.timestamp)
            }));
        } else {
            addWelcomeMessage();
        }

        if (storedContext) {
            contextType.value = storedContext;
        }
    } catch (error) {
        console.error('Error loading chat history:', error);
        addWelcomeMessage();
    }
};

const addWelcomeMessage = () => {
    messages.value = [{
        type: 'assistant',
        content: 'Halo! 👋 Saya adalah asisten AI Bilikbecakap. Ada yang bisa saya bantu? Tanyakan tentang fitur, bahasa, atau budaya Belitung!',
        timestamp: new Date()
    }];
};

const saveChatHistory = () => {
    try {
        const messagesToSave = messages.value.slice(-MAX_MESSAGES);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(messagesToSave));
        localStorage.setItem(CONTEXT_KEY, contextType.value);
    } catch (error) {
        console.error('Error saving chat history:', error);
        if (error.name === 'QuotaExceededError') {
            try {
                const halfMessages = messages.value.slice(-Math.floor(MAX_MESSAGES / 2));
                localStorage.setItem(STORAGE_KEY, JSON.stringify(halfMessages));
            } catch (e) {
                console.error('Error clearing old messages:', e);
            }
        }
    }
};

const clearChatHistory = () => {
    try {
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(CONTEXT_KEY);
        addWelcomeMessage();
    } catch (error) {
        console.error('Error clearing chat history:', error);
    }
};

onMounted(() => {
    loadChatHistory();
});

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        nextTick(() => scrollToBottom());
    }
};

const sendMessage = async () => {
    if (!userInput.value.trim()) return;

    const userMessage = userInput.value;
    userInput.value = '';
    isLoading.value = true;

    messages.value.push({
        type: 'user',
        content: userMessage,
        timestamp: new Date()
    });

    saveChatHistory();

    try {
        const response = await fetch('/chatbot/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({
                message: userMessage,
                context_type: contextType.value,
                history: messages.value.slice(0, -1).map(msg => ({
                    user: msg.type === 'user' ? msg.content : '',
                    assistant: msg.type === 'assistant' ? msg.content : ''
                }))
            })
        });

        const data = await response.json();

        if (data.success) {
            messages.value.push({
                type: 'assistant',
                content: data.assistant_answer,
                timestamp: new Date()
            });
        } else {
            messages.value.push({
                type: 'assistant',
                content: 'Maaf, terjadi kesalahan. Silakan coba lagi.',
                timestamp: new Date()
            });
        }
    } catch (error) {
        console.error('Error:', error);
        messages.value.push({
            type: 'assistant',
            content: 'Maaf, koneksi error. Silakan coba lagi.',
            timestamp: new Date()
        });
    } finally {
        isLoading.value = false;
        saveChatHistory();
        await nextTick();
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const startNewChat = () => {
    clearChatHistory();
    userInput.value = '';
};

const handleKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};
</script>

<template>
    <!-- Floating Button dengan Maskot -->
    <button v-if="!isOpen" @click="toggleChat"
        class="fixed bottom-6 right-6 w-20 h-20 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-110 z-40">
        <div
            class="relative w-full h-full flex items-end justify-center overflow-hidden bg-white border-2 border-[#54b0af] rounded-full">
            <img src="/images/maskot.png" alt="AI Maskot" class="h-full w-full object-cover" />
        </div>

        <!-- AI Badge - di luar -->
        <div
            class="absolute -top-3 -right-3 w-10 h-10 bg-gradient-to-br from-[#54b0af] to-[#459a99] rounded-full flex items-center justify-center shadow-md border-2 border-white">
            <span class="text-white text-xs font-bold">AI</span>
        </div>
    </button>

    <!-- Chat Window -->
    <transition enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 scale-95 translate-y-4" enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-4">
        <div v-if="isOpen"
            class="fixed bottom-6 right-6 w-80 h-96 sm:w-96 sm:h-[32rem] lg:w-96 lg:h-[32rem] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-40 flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white p-4 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-lg">Bilikbecakap AI</h3>
                    <p class="text-xs text-[#b8e6e5]">Asisten Digital Bahasa Belitung</p>
                </div>
                <button @click="toggleChat" class="text-white hover:bg-white/20 p-1.5 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Context Options -->
            <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 overflow-x-auto">
                <div class="flex gap-2 flex-nowrap">
                    <button v-for="(label, type) in contextOptions" :key="type" @click="contextType = type" :class="[
                        'px-2.5 py-1 text-xs rounded-full transition-colors whitespace-nowrap',
                        contextType === type
                            ? 'bg-[#54b0af] text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-200 border border-gray-300'
                    ]">
                        {{ label }}
                    </button>
                </div>
            </div>

            <!-- Chat Area -->
            <div ref="chatContainer"
                class="flex-1 overflow-y-auto p-4 bg-gradient-to-b from-white to-gray-50 space-y-3">
                <div v-for="(msg, index) in messages" :key="index"
                    :class="msg.type === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="[
                        'max-w-xs px-3 py-2 rounded-lg text-sm leading-relaxed',
                        msg.type === 'user'
                            ? 'bg-[#54b0af] text-white rounded-br-none'
                            : 'bg-gray-200 text-gray-800 rounded-bl-none'
                    ]">
                        {{ msg.content }}
                    </div>
                </div>

                <div v-if="isLoading" class="flex justify-start">
                    <div class="bg-gray-200 text-gray-800 px-3 py-2 rounded-lg rounded-bl-none">
                        <div class="flex gap-1.5">
                            <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s">
                            </div>
                            <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="bg-white border-t border-gray-200 p-3 space-y-2">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <textarea v-model="userInput" @keydown="handleKeydown" placeholder="Tulis pertanyaan..." rows="2"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] resize-none text-sm"
                        :disabled="isLoading" />
                    <div class="flex gap-1 flex-col">
                        <button type="submit" :disabled="isLoading || !userInput.trim()"
                            class="px-3 py-2 bg-[#54b0af] text-white rounded-lg hover:bg-[#459a99] disabled:opacity-50 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                        <button v-if="messages.length > 1" type="button" @click="startNewChat"
                            class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-xs">
                            Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>

    <!-- Overlay for mobile -->
    <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="isOpen" @click="toggleChat" class="fixed inset-0 bg-black/20 z-30 lg:hidden"></div>
    </transition>
</template>

<style scoped>
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #54b0af;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #459a99;
}
</style>