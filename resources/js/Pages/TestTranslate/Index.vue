<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const props = defineProps({
    connectionStatus: Object,
    databaseStats: Object,
    user: Object,
});

// Form state
const form = reactive({
    text: '',
    direction: 'belitung_to_indonesia',
    method: 'hybrid',
});

// Component state
const isLoading = ref(false);
const result = ref(null);
const error = ref('');

// Method icons mapping
const getMethodIcon = (method) => {
    const methodType = method?.split('_')[0] || method;
    switch (methodType) {
        case 'hybrid':
            return '🧠';
        case 'rule':
            return '⚡';
        default:
            return '🔧';
    }
};

// Confidence badge class
const getConfidenceClass = (confidence) => {
    switch (confidence) {
        case 'high':
            return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
        case 'medium':
            return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
        case 'low':
            return 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300';
        default:
            return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
    }
};

// Get method description
const getMethodDescription = (method) => {
    switch (method) {
        case 'hybrid_direct':
            return 'V2 - Direct Match';
        case 'hybrid_fuzzy':
            return 'V2 - Fuzzy Match';
        case 'hybrid_word_by_word':
            return 'V2 - Word by Word';
        case 'hybrid_ai':
            return 'V2 - AI Translation';
        case 'rule_direct':
            return 'V1 - Direct Match';
        case 'rule_word_by_word':
            return 'V1 - Word by Word';
        default:
            return method?.replace('_', ' ') || 'Unknown';
    }
};

// Get model version display
const getModelVersion = (method) => {
    return method === 'hybrid' ? 'V2' : 'V1';
};

// Toggle direction
const toggleDirection = () => {
    form.direction = form.direction === 'belitung_to_indonesia' ? 'indonesia_to_belitung' : 'belitung_to_indonesia';
};

// Handle translate
const handleTranslate = async () => {
    if (!form.text.trim()) return;

    isLoading.value = true;
    error.value = '';
    result.value = null;

    try {
        const response = await fetch(route('translate.process'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                text: form.text,
                direction: form.direction,
                method: form.method,
            }),
        });

        const data = await response.json();

        if (data.success) {
            result.value = data.data;
        } else {
            error.value = data.message;
        }
    } catch (err) {
        error.value = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
    } finally {
        isLoading.value = false;
    }
};

// Clear form
const clearForm = () => {
    form.text = '';
    result.value = null;
    error.value = '';
};
</script>

<template>
    <Head title="Test Translate" />

    <AdminLayout>
        <template #title>Test Translate</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
                        Test Translate Bahasa Melayu Belitung
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        Translate antara Bahasa Melayu Belitung dan Bahasa Indonesia dengan 2 model berbeda
                    </p>
                </div>
                <!-- Status Connection -->
                <div class="flex items-center space-x-2">
                    <span v-if="connectionStatus?.success" 
                          class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <svg class="w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        API Connected
                    </span>
                    <span v-else 
                          class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-full">
                        <svg class="w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        API Disconnected
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Kosa Kata</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">
                            {{ databaseStats?.total_words?.toLocaleString() || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Status API</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">
                            {{ connectionStatus?.success ? 'Online' : 'Offline' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                        <span class="text-xl">{{ getMethodIcon(form.method) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Selected Model</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ getModelVersion(form.method) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Bar -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                <!-- Direction Switch -->
                <div class="flex items-center">
                    <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                        <span class="px-3 py-2 text-sm font-medium transition-colors duration-200"
                              :class="form.direction === 'belitung_to_indonesia' 
                                ? 'text-blue-600 dark:text-blue-400' 
                                : 'text-slate-600 dark:text-slate-400'">
                            Melayu Belitung
                        </span>

                        <button type="button" 
                                @click="toggleDirection"
                                class="relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mx-3 bg-blue-600">
                            <span class="sr-only">Toggle direction</span>
                            <span class="inline-block h-6 w-6 transform rounded-full bg-white transition-transform duration-200"
                                  :class="form.direction === 'belitung_to_indonesia' ? 'translate-x-1' : 'translate-x-7'">
                                <svg class="h-6 w-6 p-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          :d="form.direction === 'belitung_to_indonesia' ? 'M13 7l5 5m0 0l-5 5m5-5H6' : 'M11 17l-5-5m0 0l5-5m-5 5h12'" />
                                </svg>
                            </span>
                        </button>

                        <span class="px-3 py-2 text-sm font-medium transition-colors duration-200"
                              :class="form.direction === 'indonesia_to_belitung' 
                                ? 'text-blue-600 dark:text-blue-400' 
                                : 'text-slate-600 dark:text-slate-400'">
                            Indonesia
                        </span>
                    </div>
                </div>

                <!-- Model Selection -->
                <div class="flex items-center gap-3">
                    <label for="method" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                        Model:
                    </label>
                    <select id="method"
                            v-model="form.method"
                            class="px-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150">
                        <option value="rule_based">⚡ V1 - Rule Based</option>
                        <option value="hybrid">🧠 V2 - AI Hybrid</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Translation Interface -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
            <form @submit.prevent="handleTranslate">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <!-- Input Panel -->
                    <div class="p-6 border-b lg:border-b-0 lg:border-r border-slate-200 dark:border-slate-700 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ form.direction === 'belitung_to_indonesia' ? 'Bahasa Melayu Belitung' : 'Bahasa Indonesia' }}
                            </h3>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                {{ form.text.length }}/1000
                            </span>
                        </div>
                        
                        <div class="flex-1 min-h-[300px] mb-4">
                            <textarea v-model="form.text"
                                      maxlength="1000"
                                      :placeholder="form.direction === 'belitung_to_indonesia' ? 'Masukkan teks dalam Bahasa Melayu Belitung...' : 'Masukkan teks dalam Bahasa Indonesia...'"
                                      class="w-full h-full resize-none border-0 focus:ring-0 focus:outline-none bg-transparent text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-base leading-relaxed p-0"></textarea>
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" 
                                    @click="clearForm"
                                    class="inline-flex items-center px-3 py-2 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Clear
                            </button>
                            <button type="submit" 
                                    :disabled="isLoading || !form.text.trim()"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="isLoading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isLoading ? 'Translating...' : 'Translate' }}
                            </button>
                        </div>
                    </div>

                    <!-- Output Panel -->
                    <div class="p-6 bg-slate-50 dark:bg-slate-800/50 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ form.direction === 'belitung_to_indonesia' ? 'Bahasa Indonesia' : 'Bahasa Melayu Belitung' }}
                            </h3>
                            <span v-if="result" class="text-xs text-slate-500 dark:text-slate-400">
                                {{ result.processing_time_ms }}ms
                            </span>
                        </div>
                        
                        <div class="flex-1 min-h-[300px] mb-4">
                            <div v-if="result" class="text-slate-900 dark:text-white text-base leading-relaxed">
                                {{ result.translation }}
                            </div>
                            <div v-else-if="error" class="text-red-600 dark:text-red-400 text-sm">
                                {{ error }}
                            </div>
                            <div v-else class="text-slate-400 dark:text-slate-500 text-base italic">
                                Hasil terjemahan akan muncul di sini...
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-600">
                            <div v-if="result" class="flex items-center justify-between">
                                <button type="button" 
                                        @click="navigator.clipboard.writeText(result.translation)"
                                        class="inline-flex items-center px-3 py-2 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copy
                                </button>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                          :class="getConfidenceClass(result.confidence)">
                                        {{ result.confidence }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ getModelVersion(result.selected_method || form.method) }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="h-10"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Translation Details -->
        <div v-if="result" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Detail Terjemahan</h3>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <span class="text-lg mr-2">{{ getMethodIcon(result.method) }}</span>
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wider">Method</span>
                        </div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                            {{ getMethodDescription(result.method) }}
                        </p>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wider">Confidence</span>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize"
                              :class="getConfidenceClass(result.confidence)">
                            {{ result.confidence || 'Unknown' }}
                        </span>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wider">Time</span>
                        </div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                            {{ result.processing_time_ms }}ms
                        </p>
                    </div>
                </div>

                <div v-if="result.translation_rate !== null && result.translation_rate !== undefined" class="mb-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                            Translation Rate:
                        </h4>
                        <div class="flex items-center">
                            <div class="flex-1 bg-blue-200 dark:bg-blue-800 rounded-full h-2 mr-3">
                                <div class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full transition-all duration-300"
                                     :style="`width: ${result.translation_rate}%`"></div>
                            </div>
                            <span class="text-sm font-bold text-blue-800 dark:text-blue-200">
                                {{ result.translation_rate.toFixed(1) }}%
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="result.matched_terms && result.matched_terms.length > 0" class="mb-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-green-800 dark:text-green-200 mb-2">
                            Kata yang Berhasil Diterjemahkan ({{ result.matched_terms.length }}):
                        </h4>
                        <div class="flex flex-wrap gap-1">
                            <span v-for="term in result.matched_terms" :key="term"
                                  class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">
                                {{ term }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="result.untranslated_words && result.untranslated_words.length > 0" class="mb-4">
                    <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-orange-800 dark:text-orange-200 mb-2">
                            Kata yang Tidak Ditemukan ({{ result.untranslated_words.length }}):
                        </h4>
                        <div class="flex flex-wrap gap-1">
                            <span v-for="word in result.untranslated_words" :key="word"
                                  class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-200">
                                {{ word }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="result.ai_used" class="mb-4">
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-sm font-medium text-purple-800 dark:text-purple-200">
                                AI Translation digunakan untuk hasil terjemahan ini
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                    <h4 class="text-xs font-medium text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Input Text:</h4>
                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ result.input }}</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>