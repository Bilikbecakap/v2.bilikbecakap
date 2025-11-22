<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Data soal statis
const wordPairs = ref([
    {
        id: 1,
        belitung: 'Selamat pagi',
        indonesian: 'Selamat pagi',
        image: '',
        category: 'Sapaan'
    },
    {
        id: 2,
        belitung: 'Terime kasih',
        indonesian: 'Terima kasih',
        image: '',
        category: 'Ucapan'
    },
    {
        id: 3,
        belitung: 'Ape khabar',
        indonesian: 'Apa kabar',
        image: '',
        category: 'Sapaan'
    },
    {
        id: 4,
        belitung: 'Makan',
        indonesian: 'Makan',
        image: '',
        category: 'Aktivitas'
    },
    {
        id: 5,
        belitung: 'Minum',
        indonesian: 'Minum',
        image: '',
        category: 'Aktivitas'
    },
    {
        id: 6,
        belitung: 'Rumah',
        indonesian: 'Rumah',
        image: '',
        category: 'Tempat'
    },
    {
        id: 7,
        belitung: 'Sekolah',
        indonesian: 'Sekolah',
        image: '',
        category: 'Tempat'
    },
    {
        id: 8,
        belitung: 'Ibu',
        indonesian: 'Ibu',
        image: '',
        category: 'Keluarga'
    }
]);

const leftItems = ref([]);
const rightItems = ref([]);
const matches = ref([]);
const draggedItem = ref(null);
const draggedFrom = ref(null); // 'left' or 'right'
const score = ref(0);
const attempts = ref(0);
const isQuizComplete = ref(false);
const showConfetti = ref(false);
const matchHistory = ref([]);

onMounted(() => {
    initializeGame();
});

const initializeGame = () => {
    // Shuffle and prepare items
    const shuffled = [...wordPairs.value].sort(() => Math.random() - 0.5);
    
    leftItems.value = shuffled.map(item => ({
        id: item.id,
        text: item.belitung,
        image: item.image,
        type: 'belitung',
        matched: false
    }));
    
    // Shuffle right items differently
    const rightShuffled = [...shuffled].sort(() => Math.random() - 0.5);
    rightItems.value = rightShuffled.map(item => ({
        id: item.id,
        text: item.indonesian,
        type: 'indonesian',
        matched: false
    }));
};

const totalPairs = computed(() => wordPairs.value.length);
const matchedPairs = computed(() => matches.value.length);
const progress = computed(() => ((matchedPairs.value / totalPairs.value) * 100).toFixed(0));
const accuracy = computed(() => {
    if (attempts.value === 0) return 0;
    return ((score.value / attempts.value) * 100).toFixed(0);
});

const handleDragStart = (item, from) => {
    if (item.matched) return;
    
    draggedItem.value = item;
    draggedFrom.value = from;
};

const handleDragOver = (event) => {
    event.preventDefault();
};

const handleDrop = (targetItem, targetFrom) => {
    event.preventDefault();
    
    if (!draggedItem.value || targetItem.matched || draggedItem.value.matched) {
        return;
    }
    
    // Can only match left to right or right to left
    if (draggedFrom.value === targetFrom) {
        return;
    }
    
    attempts.value++;
    
    // Check if it's a correct match
    if (draggedItem.value.id === targetItem.id) {
        // Correct match!
        score.value++;
        
        const leftItem = draggedFrom.value === 'left' ? draggedItem.value : targetItem;
        const rightItem = draggedFrom.value === 'right' ? draggedItem.value : targetItem;
        
        leftItem.matched = true;
        rightItem.matched = true;
        
        matches.value.push({
            id: leftItem.id,
            belitung: leftItem.text,
            indonesian: rightItem.text,
            image: leftItem.image,
            isCorrect: true
        });
        
        matchHistory.value.push({
            belitung: leftItem.text,
            indonesian: rightItem.text,
            isCorrect: true
        });
        
        // Play success sound
        playSound('success');
        
        // Check if all matched
        if (matchedPairs.value === totalPairs.value) {
            setTimeout(() => {
                completeQuiz();
            }, 500);
        }
    } else {
        // Wrong match
        matchHistory.value.push({
            belitung: draggedFrom.value === 'left' ? draggedItem.value.text : targetItem.text,
            indonesian: draggedFrom.value === 'right' ? draggedItem.value.text : targetItem.text,
            isCorrect: false
        });
        
        // Play error sound
        playSound('error');
        
        // Show shake animation
        const element = event.currentTarget;
        element.classList.add('shake');
        setTimeout(() => {
            element.classList.remove('shake');
        }, 500);
    }
    
    draggedItem.value = null;
    draggedFrom.value = null;
};

const playSound = (type) => {
    // Create simple audio feedback using Web Audio API
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    if (type === 'success') {
        oscillator.frequency.value = 800;
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } else {
        oscillator.frequency.value = 200;
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    }
};

const completeQuiz = () => {
    isQuizComplete.value = true;
    showConfetti.value = true;
    
    setTimeout(() => {
        showConfetti.value = false;
    }, 3000);
};

const restartQuiz = () => {
    leftItems.value = [];
    rightItems.value = [];
    matches.value = [];
    draggedItem.value = null;
    draggedFrom.value = null;
    score.value = 0;
    attempts.value = 0;
    isQuizComplete.value = false;
    showConfetti.value = false;
    matchHistory.value = [];
    
    initializeGame();
};

const getItemClass = (item) => {
    if (item.matched) {
        return 'bg-green-100 border-green-500 cursor-default opacity-75';
    }
    return 'bg-white border-gray-300 hover:border-[#54b0af] hover:shadow-md cursor-grab active:cursor-grabbing';
};
</script>

<template>
    <Head title="Kuis Cocokkan Kata - Bilikbecakap" />
    
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

            <!-- Confetti Animation -->
            <div v-if="showConfetti" class="fixed inset-0 z-50 pointer-events-none">
                <div class="confetti">🎉</div>
                <div class="confetti">🎊</div>
                <div class="confetti">✨</div>
                <div class="confetti">🌟</div>
                <div class="confetti">⭐</div>
                <div class="confetti">💫</div>
            </div>

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
                    <h1 class="text-2xl md:text-3xl font-bold text-[#002b44] mb-1">🎯 Kuis Cocokkan Kata</h1>
                    <p class="text-sm text-gray-600">Tarik dan lepas untuk mencocokkan kata Bahasa Melayu Belitung dengan Bahasa Indonesia!</p>
                </div>

                <div v-if="!isQuizComplete" class="max-w-6xl mx-auto">
                    <!-- Progress Bar - Compact -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-3 mb-3">
                        <div class="flex items-center justify-between mb-2 text-center">
                            <div class="flex-1">
                                <p class="text-xs text-gray-600">Pasangan Cocok</p>
                                <p class="text-xl font-bold text-[#54b0af]">{{ matchedPairs }}/{{ totalPairs }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-600">Percobaan</p>
                                <p class="text-xl font-bold text-blue-600">{{ attempts }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-600">Akurasi</p>
                                <p class="text-xl font-bold text-green-600">{{ accuracy }}%</p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-[#54b0af] to-blue-500 transition-all duration-500"
                                :style="{ width: `${progress}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Instructions - Compact -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded-lg mb-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h3 class="text-sm font-semibold text-blue-800 mb-1">Cara Bermain:</h3>
                                <ul class="text-xs text-blue-700 space-y-0.5">
                                    <li>• <strong>Desktop:</strong> Klik dan tarik kata ke pasangan yang cocok</li>
                                    <li>• <strong>Mobile:</strong> Tekan tahan dan geser ke pasangan yang cocok</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Matching Game Area - Compact -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Left Column: Belitung Words -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-3">
                            <div class="flex items-center justify-center mb-3 pb-2 border-b-2 border-[#54b0af]">
                                <div class="bg-[#54b0af] text-white px-3 py-1.5 rounded-lg font-bold text-sm">
                                    🏝️ Bahasa Melayu Belitung
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div
                                    v-for="item in leftItems"
                                    :key="item.id"
                                    :draggable="!item.matched"
                                    @dragstart="handleDragStart(item, 'left')"
                                    @dragover="handleDragOver"
                                    @drop="handleDrop(item, 'left')"
                                    :class="[
                                        'p-2.5 rounded-lg border-2 transition-all duration-200 select-none',
                                        getItemClass(item)
                                    ]"
                                >
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-2">{{ item.image }}</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-base font-semibold text-gray-800 truncate">{{ item.text }}</p>
                                            <p v-if="item.matched" class="text-xs text-green-600 mt-0.5">✓ Cocok</p>
                                        </div>
                                        <svg v-if="!item.matched" class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Indonesian Words -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-3">
                            <div class="flex items-center justify-center mb-3 pb-2 border-b-2 border-blue-500">
                                <div class="bg-blue-500 text-white px-3 py-1.5 rounded-lg font-bold text-sm">
                                    🇮🇩 Bahasa Indonesia
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div
                                    v-for="item in rightItems"
                                    :key="item.id"
                                    :draggable="!item.matched"
                                    @dragstart="handleDragStart(item, 'right')"
                                    @dragover="handleDragOver"
                                    @drop="handleDrop(item, 'right')"
                                    :class="[
                                        'p-2.5 rounded-lg border-2 transition-all duration-200 select-none',
                                        getItemClass(item)
                                    ]"
                                >
                                    <div class="flex items-center">
                                        <svg v-if="!item.matched" class="w-5 h-5 text-gray-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-base font-semibold text-gray-800 truncate">{{ item.text }}</p>
                                            <p v-if="item.matched" class="text-xs text-green-600 mt-0.5">✓ Cocok</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Instructions -->
                    <div class="md:hidden mt-3 bg-yellow-50 border-l-4 border-yellow-500 p-2.5 rounded-lg">
                        <p class="text-xs text-yellow-800">
                            <strong>💡 Tips:</strong> Tekan tahan kartu, geser ke pasangannya, lepaskan untuk mencocokkan.
                        </p>
                    </div>

                    <!-- Recent Attempts - Compact -->
                    <div v-if="matchHistory.length > 0" class="mt-3 bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-3">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Riwayat Percobaan:</h3>
                        <div class="space-y-1.5 max-h-32 overflow-y-auto">
                            <div
                                v-for="(history, index) in matchHistory.slice(-5).reverse()"
                                :key="index"
                                :class="[
                                    'p-2 rounded-lg border-l-4 flex items-center justify-between',
                                    history.isCorrect ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'
                                ]"
                            >
                                <div class="flex-1 min-w-0 text-xs">
                                    <span class="font-medium text-gray-800">{{ history.belitung }}</span>
                                    <span class="mx-1 text-gray-400">→</span>
                                    <span class="font-medium text-gray-800">{{ history.indonesian }}</span>
                                </div>
                                <span 
                                    :class="[
                                        'px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0 ml-2',
                                        history.isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ history.isCorrect ? '✓' : '✗' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Complete - Compact -->
                <div v-else class="max-w-3xl mx-auto">
                    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20 p-4 md:p-6 text-center">
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full mb-3 animate-bounce">
                                <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-[#002b44] mb-1">Selamat! 🎉</h2>
                            <p class="text-sm text-gray-600">Anda berhasil mencocokkan semua kata!</p>
                        </div>

                        <!-- Score Summary - Compact -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="bg-gradient-to-br from-[#54b0af]/10 to-[#54b0af]/5 rounded-xl p-3">
                                <p class="text-xs text-gray-600 mb-1">Pasangan Cocok</p>
                                <p class="text-3xl font-bold text-[#54b0af]">{{ matchedPairs }}</p>
                                <p class="text-xs text-gray-600">dari {{ totalPairs }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3">
                                <p class="text-xs text-gray-600 mb-1">Percobaan</p>
                                <p class="text-3xl font-bold text-blue-600">{{ attempts }}</p>
                                <p class="text-xs text-gray-600">kali</p>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-3">
                                <p class="text-xs text-gray-600 mb-1">Akurasi</p>
                                <p class="text-3xl font-bold text-green-600">{{ accuracy }}%</p>
                                <p class="text-xs text-gray-600">kebenaran</p>
                            </div>
                        </div>

                        <!-- Performance Message - Compact -->
                        <div class="mb-4">
                            <div
                                v-if="accuracy >= 90"
                                class="bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-500 p-3 rounded-lg"
                            >
                                <p class="text-sm font-semibold text-yellow-800">
                                    🌟 Sempurna! Anda sangat menguasai kosa kata!
                                </p>
                            </div>
                            <div
                                v-else-if="accuracy >= 70"
                                class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-3 rounded-lg"
                            >
                                <p class="text-sm font-semibold text-green-800">
                                    👏 Bagus! Terus berlatih!
                                </p>
                            </div>
                            <div
                                v-else
                                class="bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500 p-3 rounded-lg"
                            >
                                <p class="text-sm font-semibold text-blue-800">
                                    💪 Terus semangat!
                                </p>
                            </div>
                        </div>

                        <!-- Matched Words Summary - Compact -->
                        <div class="text-left mb-4 max-h-48 overflow-y-auto bg-gray-50 rounded-lg p-3">
                            <h3 class="text-sm font-semibold text-gray-800 mb-2 sticky top-0 bg-gray-50 pb-2">Pasangan Kata yang Berhasil:</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div
                                    v-for="match in matches"
                                    :key="match.id"
                                    class="bg-green-50 border-2 border-green-200 rounded-lg p-2"
                                >
                                    <div class="flex items-center">
                                        <span class="text-xl mr-2">{{ match.image }}</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-800 truncate">{{ match.belitung }}</p>
                                            <p class="text-xs text-gray-600">↓</p>
                                            <p class="text-xs font-medium text-blue-600 truncate">{{ match.indonesian }}</p>
                                        </div>
                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons - Compact -->
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                            <button
                                @click="restartQuiz"
                                class="w-full sm:w-auto px-6 py-2 bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all"
                            >
                                🔄 Main Lagi
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
/* Shake animation for wrong answers */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
    20%, 40%, 60%, 80% { transform: translateX(10px); }
}

.shake {
    animation: shake 0.5s;
}

/* Confetti animation */
.confetti {
    position: fixed;
    font-size: 2rem;
    animation: confetti-fall 3s linear;
}

@keyframes confetti-fall {
    0% {
        top: -10%;
        opacity: 1;
    }
    100% {
        top: 100%;
        opacity: 0;
    }
}

.confetti:nth-child(1) { left: 10%; animation-delay: 0s; }
.confetti:nth-child(2) { left: 25%; animation-delay: 0.2s; }
.confetti:nth-child(3) { left: 40%; animation-delay: 0.4s; }
.confetti:nth-child(4) { left: 55%; animation-delay: 0.6s; }
.confetti:nth-child(5) { left: 70%; animation-delay: 0.8s; }
.confetti:nth-child(6) { left: 85%; animation-delay: 1s; }

/* Drag and drop cursor */
[draggable="true"] {
    touch-action: none;
}

/* Custom scrollbar */
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