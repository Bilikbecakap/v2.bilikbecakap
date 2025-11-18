<script setup>
import { ref, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { useTranslations } from '@/composables/useTranslations'

const props = defineProps({
    user: Object,
});

const { t } = useTranslations();

// Available languages
const languages = [
    { code: 'id', name: 'Indonesia', short: 'ID' },
    { code: 'en', name: 'English', short: 'EN' },
    { code: 'mb', name: 'Melayu Belitung', short: 'MB' }
];

// Form state
const inputText = ref('');
const outputText = ref('');
const sourceLanguage = ref('id');
const targetLanguage = ref('mb');
const isTranslating = ref(false);
const showCopySuccess = ref({ input: false, output: false });

// OCR Modal state
const showOcrModal = ref(false);
const imagePreview = ref(null);
const selectedFile = ref(null);
const isExtracting = ref(false);

// Character counter
const charCount = computed(() => inputText.value.length);
const maxChars = 10000;

// Get direction string for API
const direction = computed(() => {
    const source = sourceLanguage.value;
    const target = targetLanguage.value;
    
    if (source === 'id' && target === 'mb') return 'indonesia_to_belitung';
    if (source === 'mb' && target === 'id') return 'belitung_to_indonesia';
    if (source === 'id' && target === 'en') return 'indonesia_to_english';
    if (source === 'en' && target === 'id') return 'english_to_indonesia';
    if (source === 'mb' && target === 'en') return 'belitung_to_english';
    if (source === 'en' && target === 'mb') return 'english_to_belitung';
    
    return 'indonesia_to_belitung';
});

// Filter available target languages
const availableTargetLanguages = computed(() => {
    return languages.filter(lang => lang.code !== sourceLanguage.value);
});

// Watch source language change
watch(sourceLanguage, (newSource) => {
    if (newSource === targetLanguage.value) {
        const available = languages.find(lang => lang.code !== newSource);
        if (available) {
            targetLanguage.value = available.code;
        }
    }
});

// Get language name
const getLanguageName = (code) => {
    return languages.find(lang => lang.code === code)?.name || '';
};

// Swap languages
const swapLanguages = () => {
    const temp = sourceLanguage.value;
    sourceLanguage.value = targetLanguage.value;
    targetLanguage.value = temp;
    
    const tempText = inputText.value;
    inputText.value = outputText.value;
    outputText.value = tempText;
};

// Clear all
const clearAll = () => {
    inputText.value = '';
    outputText.value = '';
};

// Copy to clipboard
const copyToClipboard = async (text, type) => {
    try {
        await navigator.clipboard.writeText(text);
        showCopySuccess.value[type] = true;
        setTimeout(() => {
            showCopySuccess.value[type] = false;
        }, 2000);
    } catch (err) {
        console.error('Gagal menyalin teks');
    }
};

// Translate
const translate = async () => {
    if (!inputText.value.trim()) {
        alert('Masukkan teks yang akan diterjemahkan');
        return;
    }

    if (sourceLanguage.value === targetLanguage.value) {
        alert('Bahasa sumber dan tujuan tidak boleh sama');
        return;
    }

    isTranslating.value = true;

    try {
        const response = await fetch(route('penerjemah.process'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                text: inputText.value,
                direction: direction.value,
                method: 'hybrid',
            }),
        });

        const data = await response.json();

        if (data.success) {
            outputText.value = data.data.translation;
        } else {
            alert(data.message || 'Gagal menerjemahkan');
        }
    } catch (error) {
        console.error('Translation error:', error);
        alert('Terjadi kesalahan sistem');
    } finally {
        isTranslating.value = false;
    }
};

// ========== OCR FUNCTIONS ==========

const openOcrModal = () => {
    showOcrModal.value = true;
};

const closeOcrModal = () => {
    showOcrModal.value = false;
    imagePreview.value = null;
    selectedFile.value = null;
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const extractTextFromImage = async () => {
    if (!selectedFile.value) {
        alert('Pilih gambar terlebih dahulu');
        return;
    }
    
    isExtracting.value = true;
    const formData = new FormData();
    formData.append('image', selectedFile.value);

    try {
        const response = await fetch(route('penerjemah.extractText'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        });

        const data = await response.json();
        
        if (data.success) {
            inputText.value = data.text;
            closeOcrModal();
        } else {
            alert('Gagal mengekstrak teks: ' + data.error);
        }
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan sistem');
    } finally {
        isExtracting.value = false;
    }
};
</script>

<template>
    <Head title="Penerjemah - Bilikbecakap" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(179,229,252,0.3)]"></div>
            </div>

            <!-- Decorative -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <div class="container mx-auto px-6 relative z-20">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-[#54b0af] mb-4 drop-shadow-sm">
                        {{ t('messages.Penerjemah Bahasa Melayu Belitung') }}
                    </h1>
                    <p class="text-lg text-[#002b44]/80 max-w-2xl mx-auto drop-shadow-sm">
                        {{ t('messages.Penerjemah Bahasa Melayu Belitung Deskripsi') }}
                    </p>
                </div>

                <!-- Translator -->
                <div class="max-w-7xl mx-auto">
                    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 overflow-hidden">
                        <!-- Language Selector Header -->
                        <div class="p-6 bg-gradient-to-r from-[#54b0af] to-[#459a99]">
                            <div class="flex items-center justify-center gap-4">
                                <!-- Source Language Dropdown -->
                                <div class="relative w-[200px]">
                                    <select 
                                        v-model="sourceLanguage"
                                        class="appearance-none bg-white text-gray-800 font-semibold px-6 py-3 pr-10 rounded-xl border-2 border-white/20 focus:outline-none focus:ring-2 focus:ring-white/50 cursor-pointer w-full"
                                    >
                                        <option v-for="lang in languages" :key="lang.code" :value="lang.code">
                                            {{ lang.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Swap Button -->
                                <button
                                    @click="swapLanguages"
                                    class="p-3 bg-[#e0a013] text-white rounded-full transition-all duration-200 shadow-md flex-shrink-0 group"
                                    title="Tukar Bahasa"
                                >
                                    <svg class="w-6 h-6 group-hover:rotate-180 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </button>

                                <!-- Target Language Dropdown -->
                                <div class="relative w-[200px]">
                                    <select 
                                        v-model="targetLanguage"
                                        class="appearance-none bg-white text-gray-800 font-semibold px-6 py-3 pr-10 rounded-xl border-2 border-white/20 focus:outline-none focus:ring-2 focus:ring-white/50 cursor-pointer w-full"
                                    >
                                        <option v-for="lang in availableTargetLanguages" :key="lang.code" :value="lang.code">
                                            {{ lang.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Side by Side Layout -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                            <!-- Input Column (LEFT) -->
                            <div class="p-6 border-r border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-semibold text-[#54b0af]">
                                        {{ getLanguageName(sourceLanguage) }}
                                    </h3>
                                    <span class="text-sm text-gray-500">
                                        {{ charCount }} / {{ maxChars }}
                                    </span>
                                </div>
                                <textarea
                                    v-model="inputText"
                                    :maxlength="maxChars"
                                    rows="14"
                                    class="w-full p-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent resize-none"
                                    placeholder="Masukkan teks yang ingin diterjemahkan..."
                                ></textarea>
                                <div class="mt-3 flex gap-2 justify-between items-center">
                                    <!-- OCR Button (LEFT) -->
                                    <button
                                        @click="openOcrModal"
                                        class="flex items-center gap-2 px-4 py-2 bg-[#e0a013] hover:bg-[#c78a10] text-white text-sm font-medium rounded-lg transition-colors shadow-md hover:shadow-lg"
                                        title="Ekstrak teks dari gambar manuskrip atau dokumen"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        OCR Manuskrip
                                    </button>
                                    
                                    <!-- Copy Button (RIGHT) -->
                                    <div class="flex gap-2 items-center">
                                        <transition name="fade">
                                            <span v-if="showCopySuccess.input" class="text-sm text-green-600 font-medium">
                                                ✓ Berhasil disalin!
                                            </span>
                                        </transition>
                                        <button
                                            @click="copyToClipboard(inputText, 'input')"
                                            class="p-2 text-gray-600 hover:text-[#54b0af] transition-colors"
                                            title="Salin"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Output Column (RIGHT) -->
                            <div class="p-6 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-semibold text-[#54b0af]">
                                        {{ getLanguageName(targetLanguage) }}
                                    </h3>
                                </div>
                                <textarea
                                    v-model="outputText"
                                    rows="14"
                                    readonly
                                    class="w-full p-4 border-2 border-gray-200 rounded-xl bg-white resize-none cursor-default focus:outline-none"
                                    placeholder="Hasil terjemahan..."
                                ></textarea>
                                <div class="mt-3 flex gap-2 justify-end items-center">
                                    <transition name="fade">
                                        <span v-if="showCopySuccess.output" class="text-sm text-green-600 font-medium">
                                            ✓ Berhasil disalin!
                                        </span>
                                    </transition>
                                    <button
                                        @click="copyToClipboard(outputText, 'output')"
                                        class="p-2 text-gray-600 hover:text-[#54b0af] transition-colors"
                                        title="Salin Hasil"
                                    >
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="p-6 bg-white border-t border-gray-200">
                            <div class="flex justify-center gap-3">
                                <button
                                    @click="clearAll"
                                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors"
                                >
                                    🗑️ {{ t('messages.delete') }}
                                </button>
                                <button
                                    @click="translate"
                                    :disabled="isTranslating || !inputText.trim()"
                                    class="px-8 py-2.5 bg-[#54b0af] hover:bg-[#459a99] hover:shadow-lg text-white font-semibold rounded-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center gap-2"
                                >
                                    <svg v-if="isTranslating" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ isTranslating ? t('messages.translating') : t('messages.translate') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="max-w-7xl mx-auto mt-8 space-y-4">
                    <!-- Info Card Penerjemah -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <div class="flex items-start gap-4">
                            <div class="bg-[#54b0af]/10 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-6 h-6 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-[#002b44] mb-2">{{ t('messages.tentang penerjemah') }}</h4>
                                <p class="text-gray-600 leading-relaxed">
                                {{ t('messages.tentang penerjemah deskripsi') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- OCR Modal -->
        <Transition name="modal">
            <div v-if="showOcrModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
                    <!-- Modal Header -->
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-[#54b0af]">📷 Ekstrak Teks dari Gambar</h3>
                        <button @click="closeOcrModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <!-- Upload Area -->
                        <div v-if="!imagePreview" class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-[#54b0af] transition-colors">
                            <input type="file" @change="handleFileSelect" accept="image/*" class="hidden" id="ocrFileInput">
                            <label for="ocrFileInput" class="cursor-pointer">
                                <div class="text-6xl mb-4">📸</div>
                                <p class="text-lg font-semibold text-gray-700 mb-2">Pilih Gambar</p>
                                <p class="text-sm text-gray-500">PNG, JPG, JPEG (Max 5MB)</p>
                            </label>
                        </div>

                        <!-- Image Preview -->
                        <div v-else class="space-y-4">
                            <img :src="imagePreview" class="w-full rounded-xl shadow-lg border-2 border-gray-200">
                            
                            <div class="flex gap-3">
                                <button
                                    @click="extractTextFromImage"
                                    :disabled="isExtracting"
                                    class="flex-1 bg-[#54b0af] hover:bg-[#459a99] text-white py-3 px-6 rounded-xl font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                >
                                    <svg v-if="isExtracting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ isExtracting ? '⏳ Memproses...' : '🔍 Ekstrak Teks' }}</span>
                                </button>
                                <label for="ocrFileInput" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold cursor-pointer transition-colors flex items-center justify-center">
                                    🔄 Ganti
                                </label>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-xl">
                            <p class="text-sm text-blue-800">
                                Memiliki manuskrip kuno, buku tua, atau dokumen tulisan tangan dalam Bahasa Indonesia atau Melayu Belitung? Gunakan fitur OCR (Optical Character Recognition) untuk mengekstrak teks dari gambar secara otomatis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </FrontendLayout>
</template>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Modal Animation */
.modal-enter-active, .modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>