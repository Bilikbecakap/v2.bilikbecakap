<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

// Data soal statis
const questions = ref([
    {
        id: 1,
        audio_text: 'Selamat pagi',
        correct_answer: 'selamat pagi',
        options: ['Selamat pagi', 'Selamat siang', 'Selamat malam', 'Selamat tinggal'],
        meaning: 'Good morning',
        category: 'Sapaan'
    },
    {
        id: 2,
        audio_text: 'Terime kasih',
        correct_answer: 'terime kasih',
        options: ['Terime kasih', 'Sama-sama', 'Maaf', 'Permisi'],
        meaning: 'Thank you',
        category: 'Ucapan'
    },
    {
        id: 3,
        audio_text: 'Ape khabar',
        correct_answer: 'ape khabar',
        options: ['Ape khabar', 'Siape name', 'Kemane', 'Ape'],
        meaning: 'How are you',
        category: 'Sapaan'
    },
    {
        id: 4,
        audio_text: 'Makan',
        correct_answer: 'makan',
        options: ['Makan', 'Minum', 'Tidur', 'Kerja'],
        meaning: 'Eat',
        category: 'Aktivitas'
    },
    {
        id: 5,
        audio_text: 'Minum',
        correct_answer: 'minum',
        options: ['Minum', 'Makan', 'Lari', 'Duduk'],
        meaning: 'Drink',
        category: 'Aktivitas'
    },
    {
        id: 6,
        audio_text: 'Rumah',
        correct_answer: 'rumah',
        options: ['Rumah', 'Sekolah', 'Pasar', 'Kantor'],
        meaning: 'House',
        category: 'Tempat'
    },
    {
        id: 7,
        audio_text: 'Sekolah',
        correct_answer: 'sekolah',
        options: ['Sekolah', 'Rumah', 'Toko', 'Masjid'],
        meaning: 'School',
        category: 'Tempat'
    },
    {
        id: 8,
        audio_text: 'Ibu',
        correct_answer: 'ibu',
        options: ['Ibu', 'Bapak', 'Adik', 'Kakak'],
        meaning: 'Mother',
        category: 'Keluarga'
    },
    {
        id: 9,
        audio_text: 'Bapak',
        correct_answer: 'bapak',
        options: ['Bapak', 'Ibu', 'Anak', 'Nenek'],
        meaning: 'Father',
        category: 'Keluarga'
    },
    {
        id: 10,
        audio_text: 'Anak',
        correct_answer: 'anak',
        options: ['Anak', 'Orang tua', 'Kakek', 'Paman'],
        meaning: 'Child',
        category: 'Keluarga'
    }
]);

const currentIndex = ref(0);
const score = ref(0);
const isAnswered = ref(false);
const selectedAnswer = ref('');
const isCorrect = ref(false);
const isQuizComplete = ref(false);
const hasPlayedAudio = ref(false);
const answerHistory = ref([]);

// Speech Synthesis
const isSpeaking = ref(false);

const currentQuestion = computed(() => questions.value[currentIndex.value]);
const progress = computed(() => ((currentIndex.value / questions.value.length) * 100).toFixed(0));
const accuracy = computed(() => {
    if (currentIndex.value === 0) return 0;
    return ((score.value / Math.max(currentIndex.value, 1)) * 100).toFixed(0);
});

onMounted(() => {
    // Auto play first audio after 1 second
    setTimeout(() => {
        playAudio();
    }, 1000);
});

onUnmounted(() => {
    // Stop any ongoing speech
    window.speechSynthesis.cancel();
});

const playAudio = () => {
    if (isSpeaking.value) {
        window.speechSynthesis.cancel();
        isSpeaking.value = false;
        return;
    }

    const utterance = new SpeechSynthesisUtterance(currentQuestion.value.audio_text);
    utterance.lang = 'id-ID';
    utterance.rate = 0.8;
    utterance.pitch = 1;
    
    utterance.onstart = () => {
        isSpeaking.value = true;
        hasPlayedAudio.value = true;
    };
    
    utterance.onend = () => {
        isSpeaking.value = false;
    };

    window.speechSynthesis.speak(utterance);
};

const selectAnswer = (answer) => {
    if (isAnswered.value) return;
    
    selectedAnswer.value = answer;
};

const submitAnswer = () => {
    if (!selectedAnswer.value || isAnswered.value) return;
    
    isAnswered.value = true;
    const correct = selectedAnswer.value.toLowerCase() === currentQuestion.value.correct_answer.toLowerCase();
    isCorrect.value = correct;
    
    if (correct) {
        score.value++;
    }

    answerHistory.value.push({
        question: currentQuestion.value.audio_text,
        meaning: currentQuestion.value.meaning,
        userAnswer: selectedAnswer.value,
        correctAnswer: currentQuestion.value.correct_answer,
        isCorrect: correct
    });
};

const nextQuestion = () => {
    if (currentIndex.value < questions.value.length - 1) {
        currentIndex.value++;
        isAnswered.value = false;
        selectedAnswer.value = '';
        isCorrect.value = false;
        hasPlayedAudio.value = false;
        
        // Auto play next audio
        setTimeout(() => {
            playAudio();
        }, 500);
    } else {
        completeQuiz();
    }
};

const completeQuiz = () => {
    isQuizComplete.value = true;
    window.speechSynthesis.cancel();
};

const restartQuiz = () => {
    currentIndex.value = 0;
    score.value = 0;
    isAnswered.value = false;
    selectedAnswer.value = '';
    isCorrect.value = false;
    isQuizComplete.value = false;
    hasPlayedAudio.value = false;
    answerHistory.value = [];
    
    setTimeout(() => {
        playAudio();
    }, 500);
};

const getOptionClass = (option) => {
    if (!isAnswered.value) {
        return selectedAnswer.value === option
            ? 'border-[#54b0af] bg-[#54b0af]/10'
            : 'border-gray-200 bg-gray-50 hover:border-[#54b0af]/50 hover:bg-[#54b0af]/5';
    }
    
    if (option.toLowerCase() === currentQuestion.value.correct_answer.toLowerCase()) {
        return 'border-green-500 bg-green-50';
    }
    
    if (selectedAnswer.value === option && !isCorrect.value) {
        return 'border-red-500 bg-red-50';
    }
    
    return 'border-gray-200 bg-gray-50 opacity-50';
};

const getOptionIcon = (option) => {
    if (!isAnswered.value) {
        return selectedAnswer.value === option ? 'selected' : '';
    }
    
    if (option.toLowerCase() === currentQuestion.value.correct_answer.toLowerCase()) {
        return 'correct';
    }
    
    if (selectedAnswer.value === option && !isCorrect.value) {
        return 'wrong';
    }
    
    return '';
};
</script>

<template>
    <Head title="Kuis Dengar & Jawab - Bilikbecakap" />
    
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
                    <h1 class="text-2xl md:text-3xl font-bold text-[#002b44] mb-1">🎧 Kuis Dengar & Jawab</h1>
                    <p class="text-sm text-gray-600">Dengarkan kata dalam Bahasa Melayu Belitung dan pilih jawaban yang benar!</p>
                </div>

                <div v-if="!isQuizComplete" class="max-w-3xl mx-auto">
                    <!-- Progress Bar - Compact -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-3 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-xs text-gray-600">Soal</p>
                                <p class="text-xl font-bold text-[#54b0af]">{{ currentIndex + 1 }} / {{ questions.length }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-600">Skor</p>
                                <p class="text-xl font-bold text-green-600">{{ score }}</p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-500"
                                :style="{ width: `${progress}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Quiz Card - Compact -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20 p-4 md:p-6">
                        <div class="text-center mb-4">
                            <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium mb-3">
                                {{ currentQuestion.category }}
                            </span>

                            <!-- Audio Player Button - Smaller -->
                            <div class="mb-4">
                                <button
                                    @click="playAudio"
                                    :disabled="isSpeaking"
                                    class="group relative inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full shadow-xl hover:shadow-2xl transform hover:scale-110 transition-all duration-300 disabled:opacity-75"
                                    :class="{ 'animate-pulse': isSpeaking }"
                                >
                                    <svg v-if="!isSpeaking" class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                    </svg>
                                    <svg v-else class="w-12 h-12 text-white animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                    </svg>
                                </button>
                            </div>

                            <p v-if="!hasPlayedAudio" class="text-sm text-gray-500 italic mb-3">
                                🎵 Dengarkan audio dengan klik tombol di atas
                            </p>

                            <h3 class="text-base font-semibold text-gray-800 mb-3">
                                Pilih jawaban yang sesuai dengan yang Anda dengar:
                            </h3>
                        </div>

                        <!-- Options - Compact -->
                        <div class="space-y-2 mb-4">
                            <button
                                v-for="(option, index) in currentQuestion.options"
                                :key="index"
                                @click="selectAnswer(option)"
                                :disabled="isAnswered"
                                :class="[
                                    'w-full flex items-center justify-between p-3 rounded-lg border-2 text-left transition-all duration-150',
                                    getOptionClass(option),
                                    isAnswered ? 'cursor-not-allowed' : 'cursor-pointer'
                                ]"
                            >
                                <span class="text-base font-medium text-gray-800">{{ option }}</span>
                                
                                <!-- Icons -->
                                <svg v-if="getOptionIcon(option) === 'selected'" class="w-5 h-5 text-[#54b0af] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                
                                <svg v-if="getOptionIcon(option) === 'correct'" class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                
                                <svg v-if="getOptionIcon(option) === 'wrong'" class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Feedback - Compact -->
                        <div v-if="isAnswered" class="mb-4">
                            <div 
                                :class="[
                                    'rounded-lg p-3 border-l-4',
                                    isCorrect ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'
                                ]"
                            >
                                <p class="text-base font-semibold mb-1" :class="isCorrect ? 'text-green-800' : 'text-red-800'">
                                    {{ isCorrect ? '✓ Benar!' : '✗ Salah!' }}
                                </p>
                                <p class="text-sm text-gray-700">
                                    Kata yang diucapkan: <span class="font-semibold">"{{ currentQuestion.audio_text }}"</span>
                                </p>
                                <p class="text-xs text-gray-600">
                                    Artinya: {{ currentQuestion.meaning }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons - Compact -->
                        <div class="flex items-center justify-center gap-3">
                            <button
                                v-if="!isAnswered"
                                @click="submitAnswer"
                                :disabled="!selectedAnswer"
                                class="px-6 py-2 bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Submit Jawaban
                            </button>

                            <button
                                v-else
                                @click="nextQuestion"
                                class="px-6 py-2 bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all"
                            >
                                {{ currentIndex < questions.length - 1 ? 'Soal Selanjutnya' : 'Lihat Hasil' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quiz Complete - Compact -->
                <div v-else class="max-w-3xl mx-auto">
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20 p-4 md:p-6 text-center">
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full mb-3">
                                <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-[#002b44] mb-1">Kuis Selesai! 🎉</h2>
                            <p class="text-sm text-gray-600">Berikut hasil Anda</p>
                        </div>

                        <!-- Score Summary - Compact -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4">
                                <p class="text-xs text-gray-600 mb-1">Skor Anda</p>
                                <p class="text-3xl font-bold text-purple-600">{{ score }}</p>
                                <p class="text-xs text-gray-600">dari {{ questions.length }} soal</p>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4">
                                <p class="text-xs text-gray-600 mb-1">Persentase</p>
                                <p class="text-3xl font-bold text-green-600">{{ ((score / questions.length) * 100).toFixed(0) }}%</p>
                                <p class="text-xs text-gray-600">kebenaran</p>
                            </div>
                        </div>

                        <!-- Answer History - Compact & Scrollable -->
                        <div class="text-left mb-4 max-h-48 overflow-y-auto bg-gray-50 rounded-lg p-3">
                            <h3 class="text-sm font-semibold text-gray-800 mb-2 sticky top-0 bg-gray-50 pb-2">Riwayat Jawaban:</h3>
                            <div class="space-y-2">
                                <div 
                                    v-for="(answer, index) in answerHistory" 
                                    :key="index"
                                    :class="[
                                        'p-2 rounded-lg border-l-4',
                                        answer.isCorrect ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'
                                    ]"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 text-sm truncate">{{ answer.question }}</p>
                                            <p class="text-xs text-gray-600 truncate">Artinya: {{ answer.meaning }}</p>
                                            <p class="text-xs text-gray-600 mt-0.5">
                                                Jawaban Anda: <span :class="answer.isCorrect ? 'text-green-700 font-medium' : 'text-red-700 font-medium'">
                                                    {{ answer.userAnswer }}
                                                </span>
                                            </p>
                                            <p v-if="!answer.isCorrect" class="text-xs text-gray-600">
                                                Jawaban Benar: <span class="text-green-700 font-medium">{{ answer.correctAnswer }}</span>
                                            </p>
                                        </div>
                                        <span 
                                            :class="[
                                                'inline-block px-2 py-1 rounded-full text-xs font-medium flex-shrink-0',
                                                answer.isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ answer.isCorrect ? '✓' : '✗' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons - Compact -->
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                            <button
                                @click="restartQuiz"
                                class="w-full sm:w-auto px-6 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all"
                            >
                                🔄 Coba Lagi
                            </button>
                            
                            <Link href="/kuis"
                                class="w-full sm:w-auto px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition-colors text-center"
                            >
                                Kembali ke Daftar Kuis
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
/* Custom scrollbar untuk riwayat */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #9333ea;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #7e22ce;
}
</style>