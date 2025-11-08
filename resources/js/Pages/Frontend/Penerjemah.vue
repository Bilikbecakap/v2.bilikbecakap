<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';

const props = defineProps({
    user: Object,
});

// Form state
const inputText = ref('');
const outputText = ref('');
const direction = ref('indonesia_to_belitung');
const isTranslating = ref(false);

// Character counter
const charCount = computed(() => inputText.value.length);
const maxChars = 10000;

// Swap direction
const swapDirection = () => {
    const temp = inputText.value;
    inputText.value = outputText.value;
    outputText.value = temp;
    
    direction.value = direction.value === 'indonesia_to_belitung' 
        ? 'belitung_to_indonesia' 
        : 'indonesia_to_belitung';
};

// Clear all
const clearAll = () => {
    inputText.value = '';
    outputText.value = '';
};

// Copy to clipboard
const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
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
</script>

<template>
    <Head title="Penerjemah - Bilik Bercakap" />

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
                        Penerjemah Bahasa Melayu Belitung
                    </h1>
                    <p class="text-lg text-[#002b44]/80 max-w-2xl mx-auto drop-shadow-sm">
                        Terjemahkan teks antara Bahasa Indonesia dan Bahasa Melayu Belitung dengan teknologi Kecerdasan Buatan
                    </p>
                </div>

                <!-- Translator -->
                <div class="max-w-7xl mx-auto">
                    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 overflow-hidden">
                        <!-- Language Direction Header -->
                        <div class="p-6 bg-gradient-to-r from-[#54b0af] to-[#459a99]">
                            <div class="flex items-center justify-center gap-4">
                                <span class="text-g font-semibold text-white w-64 text-right">
                                    {{ direction === 'indonesia_to_belitung' ? 'Bahasa Indonesia' : 'Bahasa Melayu Belitung' }}
                                </span>
                                <button
                                    @click="swapDirection"
                                    class="p-3 bg-[#e0a013] text-white hover:bg-[#FCB415] hover:shadow-100 rounded-full transition-all duration-200 shadow-md flex-shrink-0"
                                    title="Tukar Bahasa"
                                >
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </button>
                                <span class="text-g font-semibold text-white w-64 text-left">
                                    {{ direction === 'indonesia_to_belitung' ? 'Bahasa Melayu Belitung' : 'Bahasa Indonesia' }}
                                </span>
                            </div>
                        </div>

                        <!-- Side by Side Layout -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                            <!-- Input Column (LEFT) -->
                            <div class="p-6 border-r border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-semibold text-[#54b0af]">
                                        {{ direction === 'indonesia_to_belitung' ? 'Bahasa Indonesia' : 'Bahasa Melayu Belitung' }}
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
                                <div class="mt-3 flex gap-2 justify-end">
                                    <button
                                        @click="copyToClipboard(inputText)"
                                        class="p-2 text-gray-600 hover:text-[#54b0af] transition-colors"
                                        title="Salin"
                                    >
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Output Column (RIGHT) -->
                            <div class="p-6 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-semibold text-[#54b0af]">
                                        {{ direction === 'indonesia_to_belitung' ? 'Bahasa Melayu Belitung' : 'Bahasa Indonesia' }}
                                    </h3>
                                </div>
                                <textarea
                                    v-model="outputText"
                                    rows="14"
                                    readonly
                                    class="w-full p-4 border-2 border-gray-200 rounded-xl bg-white resize-none cursor-default focus:outline-none"
                                    placeholder="Hasil terjemahan..."
                                ></textarea>
                                <div class="mt-3 flex gap-2 justify-end">
                                    <button
                                        @click="copyToClipboard(outputText)"
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
                                    🗑️ Hapus
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
                                    <span>{{ isTranslating ? 'Menerjemahkan...' : 'Terjemahkan' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="max-w-7xl mx-auto mt-8 bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-[#54b0af]/10 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-[#002b44] mb-2">Tentang Penerjemah</h4>
                            <p class="text-gray-600 leading-relaxed">
                                Penerjemah ini menggunakan metode <strong>Hybrid</strong> yang menggabungkan database kata dengan teknologi AI. 
                                Sistem akan mencari di database terlebih dahulu untuk hasil yang akurat, dan menggunakan AI sebagai fallback 
                                jika kata tidak ditemukan di database.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
</style>