<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Data soal statis
const words = ref([
    {
        id: 1,
        word: 'Selamat pagi',
        pronunciation: 'selamat pagi',
        meaning: 'Good morning',
        category: 'Sapaan'
    },
    {
        id: 2,
        word: 'Terime kasih',
        pronunciation: 'terime kasih',
        meaning: 'Thank you',
        category: 'Ucapan'
    },
    {
        id: 3,
        word: 'Ape khabar',
        pronunciation: 'ape khabar',
        meaning: 'How are you',
        category: 'Sapaan'
    },
    {
        id: 4,
        word: 'Makan',
        pronunciation: 'makan',
        meaning: 'Eat',
        category: 'Aktivitas'
    },
    {
        id: 5,
        word: 'Minum',
        pronunciation: 'minum',
        meaning: 'Drink',
        category: 'Aktivitas'
    },
    {
        id: 6,
        word: 'Rumah',
        pronunciation: 'rumah',
        meaning: 'House',
        category: 'Tempat'
    },
    {
        id: 7,
        word: 'Sekolah',
        pronunciation: 'sekolah',
        meaning: 'School',
        category: 'Tempat'
    },
    {
        id: 8,
        word: 'Ibu',
        pronunciation: 'ibu',
        meaning: 'Mother',
        category: 'Keluarga'
    },
    {
        id: 9,
        word: 'Bapak',
        pronunciation: 'bapak',
        meaning: 'Father',
        category: 'Keluarga'
    },
    {
        id: 10,
        word: 'Anak',
        pronunciation: 'anak',
        meaning: 'Child',
        category: 'Keluarga'
    }
]);

const currentIndex = ref(0);
const score = ref(0);
const totalAttempts = ref(0);
const isListening = ref(false);
const transcript = ref('');
const feedback = ref('');
const feedbackType = ref(''); // 'success' or 'error'
const showFeedback = ref(false);
const isQuizComplete = ref(false);
const attemptHistory = ref([]);
const isProcessing = ref(false);

const currentWord = computed(() => words.value[currentIndex.value]);
const progress = computed(() => ((currentIndex.value / words.value.length) * 100).toFixed(0));
const accuracy = computed(() => {
    if (totalAttempts.value === 0) return 0;
    return ((score.value / totalAttempts.value) * 100).toFixed(0);
});

// Simulasi Speech Recognition (Prototipe)
const startListening = () => {
    if (isProcessing.value) return;
    
    isListening.value = true;
    transcript.value = '';
    feedback.value = '';
    showFeedback.value = false;
    
    // Simulasi mendengarkan (2 detik)
    setTimeout(() => {
        simulateSpeechRecognition();
    }, 2000);
};

const stopListening = () => {
    if (!isListening.value) return;
    isListening.value = false;
};

const simulateSpeechRecognition = () => {
    isListening.value = false;
    isProcessing.value = true;
    
    // Generate random similarity (40% - 100%)
    const randomSimilarity = Math.floor(Math.random() * 21) + 75; // 40-100
    
    // Generate simulated transcript with variations
    const variations = [
        currentWord.value.word.toLowerCase(),
        currentWord.value.word.toLowerCase().replace(/a/g, 'e'),
        currentWord.value.word.toLowerCase() + ' aja',
        currentWord.value.pronunciation.toLowerCase(),
        currentWord.value.word.toLowerCase().slice(0, -1),
    ];
    
    const simulatedTranscript = variations[Math.floor(Math.random() * variations.length)];
    
    // Show transcript
    transcript.value = simulatedTranscript;
    
    // Wait 1 second then validate
    setTimeout(() => {
        validatePronunciation(simulatedTranscript, randomSimilarity);
        isProcessing.value = false;
    }, 1000);
};

const validatePronunciation = (spokenText, similarity) => {
    totalAttempts.value++;
    
    const attempt = {
        word: currentWord.value.word,
        spoken: spokenText,
        correct: currentWord.value.pronunciation,
        similarity: similarity,
        isCorrect: similarity >= 70
    };
    
    attemptHistory.value.push(attempt);
    
    if (similarity >= 70) {
        score.value++;
        feedback.value = `✓ Bagus! Pelafalan Anda ${similarity}% mirip!`;
        feedbackType.value = 'success';
        
        setTimeout(() => {
            nextWord();
        }, 2500);
    } else {
        feedback.value = `✗ Coba lagi! Pelafalan Anda ${similarity}% mirip. Ucapkan: "${currentWord.value.word}"`;
        feedbackType.value = 'error';
    }
    
    showFeedback.value = true;
};

const nextWord = () => {
    showFeedback.value = false;
    transcript.value = '';
    
    if (currentIndex.value < words.value.length - 1) {
        currentIndex.value++;
    } else {
        completeQuiz();
    }
};

const skipWord = () => {
    totalAttempts.value++;
    
    attemptHistory.value.push({
        word: currentWord.value.word,
        spoken: '-',
        correct: currentWord.value.pronunciation,
        similarity: 0,
        isCorrect: false
    });
    
    nextWord();
};

const completeQuiz = () => {
    isQuizComplete.value = true;
};

const restartQuiz = () => {
    currentIndex.value = 0;
    score.value = 0;
    totalAttempts.value = 0;
    transcript.value = '';
    feedback.value = '';
    showFeedback.value = false;
    isQuizComplete.value = false;
    attemptHistory.value = [];
    isProcessing.value = false;
};

const playPronunciation = () => {
    const utterance = new SpeechSynthesisUtterance(currentWord.value.word);
    utterance.lang = 'id-ID';
    utterance.rate = 0.8;
    window.speechSynthesis.speak(utterance);
};
</script>

<template>
    <Head title="Kuis Pelafalan Kata - Bilikbecakap" />
    
    <FrontendLayout>
        <section class="py-6 min-h-screen relative overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <!-- Decorative -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full blur-3xl z-10"></div>

            <div class="container mx-auto px-4 relative z-20">
                <!-- Back Button -->
                <Link href="/kuis"
                    class="inline-flex items-center gap-2 text-[#54b0af] hover:text-[#459a99] mb-3 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Kuis
                </Link>

                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="text-2xl md:text-3xl font-bold text-[#002b44] mb-1">🎤 Kuis Pelafalan Kata</h1>
                    <p class="text-sm text-gray-600">Ucapkan kata dalam Bahasa Melayu Belitung dengan benar!</p>
                </div>

                <div v-if="!isQuizComplete" class="max-w-3xl mx-auto">
                    <!-- Progress Bar - Compact -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-3 mb-3">
                        <div class="flex items-center justify-between mb-2 text-center">
                            <div class="flex-1">
                                <p class="text-xs text-gray-600">Soal</p>
                                <p class="text-xl font-bold text-[#54b0af]">{{ currentIndex + 1 }}/{{ words.length }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-600">Skor</p>
                                <p class="text-xl font-bold text-green-600">{{ score }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-600">Akurasi</p>
                                <p class="text-xl font-bold text-blue-600">{{ accuracy }}%</p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-[#54b0af] to-[#459a99] transition-all duration-500"
                                :style="{ width: `${progress}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Quiz Card - Compact -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20 p-4 md:p-6">
                        <div class="text-center mb-4">
                            <span class="inline-block px-3 py-1 bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white rounded-full text-xs font-semibold mb-3 shadow-md">
                                {{ currentWord.category }}
                            </span>
                            
                            <h2 class="text-4xl md:text-5xl font-bold text-[#002b44] mb-3">
                                {{ currentWord.word }}
                            </h2>
                            
                            <p class="text-lg text-gray-600 mb-3">
                                Artinya: <span class="font-bold text-[#54b0af]">{{ currentWord.meaning }}</span>
                            </p>

                            <!-- Play Pronunciation Button - Smaller -->
                            <button
                                @click="playPronunciation"
                                class="inline-flex items-center gap-1 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm rounded-lg transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                </svg>
                                Dengar Pelafalan
                            </button>
                        </div>

                        <!-- Microphone Button - Smaller -->
                        <div class="text-center mb-4">
                            <button
                                v-if="!isListening && !isProcessing"
                                @click="startListening"
                                class="group relative inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-red-500 to-pink-500 rounded-full shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                            >
                                <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </button>

                            <button
                                v-else-if="isListening"
                                @click="stopListening"
                                class="group relative inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-red-600 to-pink-600 rounded-full shadow-xl"
                            >
                                <div class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75"></div>
                                <svg class="w-12 h-12 text-white relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </button>

                            <div
                                v-else-if="isProcessing"
                                class="group relative inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-full shadow-xl"
                            >
                                <svg class="w-12 h-12 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <!-- Status Text - Smaller -->
                            <p class="mt-3 text-sm font-semibold">
                                <span v-if="!isListening && !isProcessing" class="text-gray-700">
                                    Klik tombol untuk mulai berbicara
                                </span>
                                <span v-else-if="isListening" class="text-red-600 animate-pulse">
                                    🎤 Mendengarkan...
                                </span>
                                <span v-else-if="isProcessing" class="text-orange-600">
                                    ⚙️ Memproses ucapan...
                                </span>
                            </p>
                        </div>

                        <!-- Transcript - Compact -->
                        <div v-if="transcript" class="mb-3 animate-fade-in">
                            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-3 border-l-4 border-blue-500 shadow">
                                <p class="text-xs text-gray-600 mb-1 font-medium">Anda mengucapkan:</p>
                                <p class="text-lg font-bold text-gray-800">"{{ transcript }}"</p>
                            </div>
                        </div>

                        <!-- Feedback - Compact -->
                        <div v-if="showFeedback" class="mb-3 animate-slide-down">
                            <div 
                                :class="[
                                    'rounded-lg p-3 border-l-4 shadow-md',
                                    feedbackType === 'success' ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-500' : 'bg-gradient-to-r from-red-50 to-pink-50 border-red-500'
                                ]"
                            >
                                <p 
                                    :class="[
                                        'text-base font-bold',
                                        feedbackType === 'success' ? 'text-green-800' : 'text-red-800'
                                    ]"
                                >
                                    {{ feedback }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons - Smaller -->
                        <div class="flex items-center justify-center gap-3 pt-3 border-t border-gray-200">
                            <button
                                @click="skipWord"
                                :disabled="isListening || isProcessing"
                                class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
                            >
                                ⏭️ Lewati
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quiz Complete - Compact -->
                <div v-else class="max-w-3xl mx-auto">
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20 p-4 md:p-6 text-center">
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full mb-3 animate-bounce-slow shadow-xl">
                                <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-[#002b44] mb-1">Kuis Selesai! 🎉</h2>
                            <p class="text-sm text-gray-600">Berikut hasil pelafalan Anda</p>
                        </div>

                        <!-- Score Summary - Compact -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="bg-gradient-to-br from-[#54b0af]/10 to-[#54b0af]/5 rounded-xl p-3 shadow-lg">
                                <p class="text-xs text-gray-600 mb-1">Skor Anda</p>
                                <p class="text-3xl font-bold text-[#54b0af] mb-1">{{ score }}</p>
                                <p class="text-xs text-gray-600">dari {{ words.length }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-3 shadow-lg">
                                <p class="text-xs text-gray-600 mb-1">Akurasi</p>
                                <p class="text-3xl font-bold text-green-600 mb-1">{{ accuracy }}%</p>
                                <p class="text-xs text-gray-600">keseluruhan</p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-3 shadow-lg">
                                <p class="text-xs text-gray-600 mb-1">Percobaan</p>
                                <p class="text-3xl font-bold text-blue-600 mb-1">{{ totalAttempts }}</p>
                                <p class="text-xs text-gray-600">kali</p>
                            </div>
                        </div>

                        <!-- Attempt History - Compact & Scrollable -->
                        <div class="text-left mb-4 max-h-48 overflow-y-auto bg-gray-50 rounded-lg p-3">
                            <h3 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2 sticky top-0 bg-gray-50 pb-2">
                                <svg class="w-4 h-4 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Riwayat Jawaban
                            </h3>
                            <div class="space-y-2">
                                <div 
                                    v-for="(attempt, index) in attemptHistory" 
                                    :key="index"
                                    :class="[
                                        'p-2 rounded-lg border-l-4 shadow-sm',
                                        attempt.isCorrect ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-gray-800 text-sm mb-0.5 truncate">{{ attempt.word }}</p>
                                            <p class="text-xs text-gray-600 truncate">
                                                Ucapan: <span class="font-semibold text-gray-800">{{ attempt.spoken }}</span>
                                            </p>
                                        </div>
                                        <span 
                                            :class="[
                                                'inline-block px-2 py-1 rounded-full text-xs font-bold shadow-sm ml-2 flex-shrink-0',
                                                attempt.isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ attempt.similarity }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons - Compact -->
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                            <button
                                @click="restartQuiz"
                                class="w-full sm:w-auto px-6 py-2 bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white text-sm font-bold rounded-lg hover:shadow-lg transition-all transform hover:-translate-y-0.5"
                            >
                                🔄 Coba Lagi
                            </button>
                            
                            <Link href="/kuis"
                                class="w-full sm:w-auto px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold rounded-lg transition-all shadow-md hover:shadow-lg text-center"
                            >
                                📋 Kembali ke Daftar Kuis
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

@keyframes bounce-slow {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-down {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out;
}

.animate-slide-down {
    animation: slide-down 0.5s ease-out;
}

/* Custom scrollbar untuk riwayat */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #54b0af;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #459a99;
}
</style>