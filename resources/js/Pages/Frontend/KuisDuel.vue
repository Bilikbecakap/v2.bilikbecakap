<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';

const props = defineProps({
    quiz: Object,
    attempt1: Object,
    attempt2: Object,
    questions: Array,
});

// Audio player refs
const audioPlayer = ref(null);
const isMusicPlaying = ref(false);
const isMusicMuted = ref(false);

// Game State
const showCountdown = ref(true);
const countdown = ref(3);
const gameStarted = ref(false);
const currentQuestionIndex = ref(0);
const player1Score = ref(0);
const player2Score = ref(0);
const player1Answer = ref('');
const player2Answer = ref('');
const player1SelectedOption = ref(null);
const player2SelectedOption = ref(null);
const ropePosition = ref(50);
const showResult = ref(false);
const winner = ref(null);

// Shake animation state
const shakePlayer1 = ref(false);
const shakePlayer2 = ref(false);

// Race condition handling - hanya 1 player yang bisa jawab benar per soal
const questionAnswered = ref(false);
const isProcessing = ref(false);

// Answers tracking
const player1Answers = ref([]);
const player2Answers = ref([]);

// Computed
const currentQuestion = computed(() => props.questions[currentQuestionIndex.value]);
const isLastQuestion = computed(() => currentQuestionIndex.value === props.questions.length - 1);
const winTarget = computed(() => Math.ceil(props.questions.length / 2));

const hasMusic = computed(() => {
    return props.quiz.master_media_music_quiz && props.quiz.master_media_music_quiz.audio_url;
});

const musicUrl = computed(() => {
    return props.quiz.master_media_music_quiz?.audio_url || null;
});

const isMultipleChoice = computed(() => {
    return currentQuestion.value?.question_type === 'multiple_choice';
});

const isFillBlank = computed(() => {
    return currentQuestion.value?.question_type === 'fill_blank';
});

onMounted(() => {
    startCountdown();
    initAnswers();
    setTimeout(() => {
        initAudio();
    }, 100);
});

onBeforeUnmount(() => {
    stopAudio();
});

const initAnswers = () => {
    player1Answers.value = props.questions.map(q => ({
        question_id: q.id,
        option_id: null,
        answer_text: null,
    }));
    
    player2Answers.value = props.questions.map(q => ({
        question_id: q.id,
        option_id: null,
        answer_text: null,
    }));
};

const initAudio = () => {
    if (hasMusic.value && audioPlayer.value) {
        audioPlayer.value.volume = 0.3;
        audioPlayer.value.loop = true;
        
        audioPlayer.value.play().then(() => {
            isMusicPlaying.value = true;
        }).catch(e => {
            console.log('Autoplay prevented:', e);
            isMusicPlaying.value = false;
        });
    }
};

const toggleMusic = () => {
    if (!audioPlayer.value) return;
    
    if (isMusicPlaying.value) {
        audioPlayer.value.pause();
        isMusicPlaying.value = false;
    } else {
        audioPlayer.value.play().catch(e => {
            console.error('Error playing audio:', e);
        });
        isMusicPlaying.value = true;
    }
};

const toggleMute = () => {
    if (!audioPlayer.value) return;
    
    audioPlayer.value.muted = !audioPlayer.value.muted;
    isMusicMuted.value = audioPlayer.value.muted;
};

const stopAudio = () => {
    if (audioPlayer.value) {
        audioPlayer.value.pause();
        audioPlayer.value.currentTime = 0;
        isMusicPlaying.value = false;
    }
};

const startCountdown = () => {
    const interval = setInterval(() => {
        countdown.value--;
        
        if (countdown.value === 0) {
            clearInterval(interval);
            setTimeout(() => {
                showCountdown.value = false;
                gameStarted.value = true;
            }, 1000);
        }
    }, 1000);
};

// Virtual Keyboard - QWERTY Layout
const keyboard = [
    ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
    ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
    ['Z', 'X', 'C', 'V', 'B', 'N', 'M', '⌫']
];

const typeKey = (key, player) => {
    if (key === '⌫') {
        if (player === 1) {
            player1Answer.value = player1Answer.value.slice(0, -1);
        } else {
            player2Answer.value = player2Answer.value.slice(0, -1);
        }
    } else {
        if (player === 1) {
            player1Answer.value += key;
        } else {
            player2Answer.value += key;
        }
    }
};

const triggerShake = (player) => {
    if (player === 1) {
        shakePlayer1.value = true;
        setTimeout(() => {
            shakePlayer1.value = false;
        }, 600);
    } else {
        shakePlayer2.value = true;
        setTimeout(() => {
            shakePlayer2.value = false;
        }, 600);
    }
};

// AUTO CHECK untuk Multiple Choice
const selectOptionP1 = (optionId) => {
    // Cek apakah soal sudah dijawab benar atau sedang diproses
    if (questionAnswered.value || isProcessing.value) {
        return;
    }
    
    player1SelectedOption.value = optionId;
    // Auto submit untuk multiple choice
    setTimeout(() => {
        submitPlayer1();
    }, 100);
};

const selectOptionP2 = (optionId) => {
    // Cek apakah soal sudah dijawab benar atau sedang diproses
    if (questionAnswered.value || isProcessing.value) {
        return;
    }
    
    player2SelectedOption.value = optionId;
    // Auto submit untuk multiple choice
    setTimeout(() => {
        submitPlayer2();
    }, 100);
};

const getOptionLabel = (index) => {
    return String.fromCharCode(65 + index);
};

// SUBMIT PLAYER 1
const submitPlayer1 = () => {
    // Cek apakah soal sudah dijawab benar atau sedang diproses
    if (questionAnswered.value || isProcessing.value) {
        return;
    }
    
    // Lock processing
    isProcessing.value = true;
    
    const question = currentQuestion.value;
    let correctAnswer = '';
    let p1Correct = false;
    
    if (question.question_type === 'fill_blank') {
        correctAnswer = question.correct_answer?.toLowerCase().trim() || '';
        const userAnswer = player1Answer.value.toLowerCase().trim();
        
        console.log('=== PLAYER 1 DEBUG ===');
        console.log('Correct Answer:', correctAnswer);
        console.log('User Answer:', userAnswer);
        console.log('Match:', correctAnswer === userAnswer);
        
        p1Correct = userAnswer === correctAnswer;
        
        player1Answers.value[currentQuestionIndex.value].answer_text = player1Answer.value;
        
    } else if (question.question_type === 'multiple_choice') {
        const correctOption = question.options?.find(opt => opt.is_correct);
        
        console.log('=== PLAYER 1 DEBUG ===');
        console.log('Correct Option ID:', correctOption?.id);
        console.log('Selected Option ID:', player1SelectedOption.value);
        console.log('Correct Option Text:', correctOption?.option_text);
        console.log('Match:', player1SelectedOption.value === correctOption?.id);
        
        p1Correct = player1SelectedOption.value === correctOption?.id;
        
        player1Answers.value[currentQuestionIndex.value].option_id = player1SelectedOption.value;
    }
    
    if (p1Correct) {
        // PLAYER 1 MENANG! Mark soal sudah dijawab
        questionAnswered.value = true;
        
        player1Score.value++;
        updateRopePosition(true, false);
        
        if (player1Score.value >= winTarget.value) {
            endGame(1);
            return;
        }
        
        if (isLastQuestion.value) {
            finalScoreCheck();
        } else {
            setTimeout(() => {
                nextQuestion();
            }, 1000);
        }
    } else {
        // Salah, unlock processing agar player lain bisa jawab
        isProcessing.value = false;
        // Trigger shake animation
        triggerShake(1);
    }
};

// SUBMIT PLAYER 2
const submitPlayer2 = () => {
    // Cek apakah soal sudah dijawab benar atau sedang diproses
    if (questionAnswered.value || isProcessing.value) {
        return;
    }
    
    // Lock processing
    isProcessing.value = true;
    
    const question = currentQuestion.value;
    let correctAnswer = '';
    let p2Correct = false;
    
    if (question.question_type === 'fill_blank') {
        correctAnswer = question.correct_answer?.toLowerCase().trim() || '';
        const userAnswer = player2Answer.value.toLowerCase().trim();
        
        console.log('=== PLAYER 2 DEBUG ===');
        console.log('Correct Answer:', correctAnswer);
        console.log('User Answer:', userAnswer);
        console.log('Match:', correctAnswer === userAnswer);
        
        p2Correct = userAnswer === correctAnswer;
        
        player2Answers.value[currentQuestionIndex.value].answer_text = player2Answer.value;
        
    } else if (question.question_type === 'multiple_choice') {
        const correctOption = question.options?.find(opt => opt.is_correct);
        
        console.log('=== PLAYER 2 DEBUG ===');
        console.log('Correct Option ID:', correctOption?.id);
        console.log('Selected Option ID:', player2SelectedOption.value);
        console.log('Correct Option Text:', correctOption?.option_text);
        console.log('Match:', player2SelectedOption.value === correctOption?.id);
        
        p2Correct = player2SelectedOption.value === correctOption?.id;
        
        player2Answers.value[currentQuestionIndex.value].option_id = player2SelectedOption.value;
    }
    
    if (p2Correct) {
        // PLAYER 2 MENANG! Mark soal sudah dijawab
        questionAnswered.value = true;
        
        player2Score.value++;
        updateRopePosition(false, true);
        
        if (player2Score.value >= winTarget.value) {
            endGame(2);
            return;
        }
        
        if (isLastQuestion.value) {
            finalScoreCheck();
        } else {
            setTimeout(() => {
                nextQuestion();
            }, 1000);
        }
    } else {
        // Salah, unlock processing agar player lain bisa jawab
        isProcessing.value = false;
        // Trigger shake animation
        triggerShake(2);
    }
};

const updateRopePosition = (p1Correct, p2Correct) => {
    if (p1Correct && !p2Correct) {
        ropePosition.value = Math.max(0, ropePosition.value - 1);
    } else if (p2Correct && !p1Correct) {
        ropePosition.value = Math.min(100, ropePosition.value + 1);
    }
};

const nextQuestion = () => {
    player1Answer.value = '';
    player2Answer.value = '';
    player1SelectedOption.value = null;
    player2SelectedOption.value = null;
    currentQuestionIndex.value++;
    
    // Reset race condition flags
    questionAnswered.value = false;
    isProcessing.value = false;
};

const finalScoreCheck = () => {
    if (player1Score.value > player2Score.value) {
        endGame(1);
    } else if (player2Score.value > player1Score.value) {
        endGame(2);
    } else {
        endGame(0);
    }
};

const endGame = (winnerPlayer) => {
    winner.value = winnerPlayer;
    showResult.value = true;
    
    setTimeout(() => {
        router.post(route('quiz-attempt.submit-duel', props.quiz.slug), {
            attempt1_id: props.attempt1.id,
            attempt2_id: props.attempt2.id,
            player1_answers: player1Answers.value,
            player2_answers: player2Answers.value,
        });
    }, 3000);
};

const canSubmitP1 = computed(() => {
    return player1Answer.value.trim() !== '';
});

const canSubmitP2 = computed(() => {
    return player2Answer.value.trim() !== '';
});

const ropeStyle = computed(() => ({
    transform: `translateX(${ropePosition.value - 50}%)`,
    transition: 'transform 0.7s ease-in-out'
}));

</script>

<template>
    <Head :title="`${quiz.title} - Arena Tantangan`" />

    <FrontendLayout>
        <!-- Hidden Audio Player -->
        <audio
            v-if="hasMusic"
            ref="audioPlayer"
            :src="musicUrl"
            preload="auto"
            class="hidden"
        ></audio>

        <!-- Countdown Overlay -->
        <div v-if="showCountdown" class="fixed inset-0 z-50 bg-[#002b44]/95 flex items-center justify-center">
            <div class="text-center">
                <div v-if="countdown > 0" class="text-white">
                    <div class="text-9xl font-bold animate-bounce mb-4">
                        {{ countdown }}
                    </div>
                    <p class="text-2xl font-semibold">Bersiap...</p>
                </div>
                <div v-else class="text-white">
                    <div class="text-9xl font-bold text-[#54b0af] animate-pulse">
                        GO!
                    </div>
                    <p class="text-2xl font-semibold">Mulai Bertanding!</p>
                </div>
            </div>
        </div>

        <!-- Winner Overlay -->
        <div v-if="showResult" class="fixed inset-0 z-50 bg-[#002b44]/95 flex items-center justify-center">
            <div class="text-center text-white space-y-6">
                <div v-if="winner === 1" class="space-y-4">
                    <div class="text-7xl">🏆</div>
                    <h2 class="text-5xl font-bold text-[#54b0af]">{{ attempt1.participant_name }} MENANG!</h2>
                    <p class="text-2xl">Skor: {{ player1Score }} - {{ player2Score }}</p>
                </div>
                <div v-else-if="winner === 2" class="space-y-4">
                    <div class="text-7xl">🏆</div>
                    <h2 class="text-5xl font-bold text-[#54b0af]">{{ attempt2.participant_name }} MENANG!</h2>
                    <p class="text-2xl">Skor: {{ player1Score }} - {{ player2Score }}</p>
                </div>
                <div v-else class="space-y-4">
                    <div class="text-7xl">🤝</div>
                    <h2 class="text-5xl font-bold text-[#54b0af]">SERI!</h2>
                    <p class="text-2xl">Skor: {{ player1Score }} - {{ player2Score }}</p>
                </div>
                <p class="text-lg text-gray-300">Mengalihkan ke hasil...</p>
            </div>
        </div>

        <!-- Main Game Arena -->
        <section v-if="gameStarted" class="min-h-screen relative overflow-hidden bg-gradient-to-b from-[#54b0af]/10 to-white py-8">
            
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <div class="container mx-auto px-4">
                <!-- Top: Score & Music Control -->
                <div class="mb-6">
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between">
                            <!-- Player 1 Score -->
                            <div class="text-center">
                                <div class="text-4xl font-bold text-[#54b0af]">{{ player1Score }}</div>
                                <div class="text-sm font-semibold text-gray-700 mt-1">{{ attempt1.participant_name }}</div>
                                <div class="text-xs text-gray-500">Target: {{ winTarget }}</div>
                            </div>

                            <!-- Center: Progress & Music -->
                            <div class="text-center space-y-2">
                                <div class="text-sm font-semibold text-gray-600">Soal {{ currentQuestionIndex + 1 }} / {{ questions.length }}</div>
                                <div class="w-64 bg-gray-200 rounded-full h-3">
                                    <div 
                                        class="bg-[#54b0af] h-3 rounded-full transition-all duration-300"
                                        :style="{ width: `${((currentQuestionIndex + 1) / questions.length) * 100}%` }"
                                    ></div>
                                </div>
                                
                                <!-- Music Controls -->
                                <div v-if="hasMusic" class="flex items-center justify-center gap-2">
                                    <button
                                        @click="toggleMusic"
                                        class="p-2 rounded-lg bg-[#54b0af]/10 hover:bg-[#54b0af]/20 transition-colors"
                                    >
                                        <svg
                                            v-if="!isMusicPlaying"
                                            class="w-5 h-5 text-[#54b0af]"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-5 h-5 text-[#54b0af]"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z"/>
                                        </svg>
                                    </button>

                                    <button
                                        @click="toggleMute"
                                        class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors"
                                    >
                                        <svg
                                            v-if="!isMusicMuted"
                                            class="w-5 h-5 text-slate-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M10 3.75a.75.75 0 00-1.264-.546L4.703 7H3.167a.75.75 0 00-.7.48A6.985 6.985 0 002 10c0 .887.165 1.737.468 2.52.111.29.39.48.7.48h1.535l4.033 3.796A.75.75 0 0010 16.25V3.75z"/>
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-5 h-5 text-slate-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M10 3.75a.75.75 0 00-1.264-.546L4.703 7H3.167a.75.75 0 00-.7.48A6.985 6.985 0 002 10c0 .887.165 1.737.468 2.52.111.29.39.48.7.48h1.535l4.033 3.796A.75.75 0 0010 16.25V3.75zM15.28 6.22a.75.75 0 10-1.06 1.06L15.44 8.5l-1.22 1.22a.75.75 0 001.06 1.06l1.22-1.22 1.22 1.22a.75.75 0 001.06-1.06L17.56 8.5l1.22-1.22a.75.75 0 00-1.06-1.06l-1.22 1.22-1.22-1.22z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Player 2 Score -->
                            <div class="text-center">
                                <div class="text-4xl font-bold text-[#54b0af]">{{ player2Score }}</div>
                                <div class="text-sm font-semibold text-gray-700 mt-1">{{ attempt2.participant_name }}</div>
                                <div class="text-xs text-gray-500">Target: {{ winTarget }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rope Animation Container -->
                <div class="relative bg-gradient-to-b from-[#FCB415]/20 to-white/50 rounded-2xl p-4 overflow-hidden">

                    <div class="mt-4 relative">
                        <div class="w-full bg-gray-300 rounded-full h-4 relative">
                            <div 
                                class="absolute top-0 left-0 h-4 bg-[#54b0af] rounded-full transition-all duration-700"
                                :style="{ width: `${ropePosition}%` }"
                            ></div>
                            <div 
                                class="absolute top-1/2 -translate-y-1/2 w-1 h-6 bg-gray-600 transition-all duration-700"
                                :style="{ left: `${ropePosition}%` }"
                            ></div>
                        </div>
                        <div class="flex justify-between mt-2 text-xs font-semibold text-gray-600">
                            <span>← {{ attempt1.participant_name }}</span>
                            <span>{{ attempt2.participant_name }} →</span>
                        </div>
                    </div>
                    
                    <div class="relative h-64 flex items-center justify-center">
                        <img 
                            src="/ilustration/tariktambang2.png" 
                            alt="Tarik Tambang"
                            class="absolute w-[1200px] h-auto"
                            :style="ropeStyle"
                        />
                    </div>

                    
                </div>

                <!-- Question Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 mb-6 border-4 border-[#54b0af]">
                    <div class="text-center">
                        <div 
                            class="text-lg md:text-xl font-semibold text-[#002b44] prose prose-sm max-w-none"
                            v-html="currentQuestion.question"
                        ></div>
                    </div>
                </div>

                

                <!-- Answer Section: Multiple Choice -->
                <div v-if="isMultipleChoice" class="grid grid-cols-2 gap-4 mb-6">
                    <!-- Player 1 Options -->
                    <div 
                        :class="[
                            'bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border-4 border-[#54b0af] transition-all duration-150',
                            shakePlayer1 ? 'shake-animation' : ''
                        ]"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <span class="bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm font-bold">PLAYER 1</span>
                            <span class="font-bold text-[#002b44]">{{ attempt1.participant_name }}</span>
                        </div>
                        <div class="space-y-2">
                            <button
                                v-for="(option, index) in currentQuestion.options"
                                :key="option.id"
                                @click="selectOptionP1(option.id)"
                                :class="[
                                    'w-full flex items-center p-3 rounded-lg border-2 text-left transition-all',
                                    player1SelectedOption === option.id
                                        ? 'border-[#54b0af] bg-[#54b0af]/10'
                                        : 'border-gray-200 bg-gray-50 hover:border-[#54b0af]/50'
                                ]"
                            >
                                <span :class="[
                                    'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3',
                                    player1SelectedOption === option.id
                                        ? 'bg-[#54b0af] text-white'
                                        : 'bg-gray-200 text-gray-600'
                                ]">
                                    {{ getOptionLabel(index) }}
                                </span>
                                <span class="text-sm">{{ option.option_text }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Player 2 Options -->
                    <div 
                        :class="[
                            'bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border-4 border-[#54b0af] transition-all duration-150',
                            shakePlayer2 ? 'shake-animation' : ''
                        ]"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-bold text-[#002b44]">{{ attempt2.participant_name }}</span>
                            <span class="bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm font-bold">PLAYER 2</span>
                        </div>
                        <div class="space-y-2">
                            <button
                                v-for="(option, index) in currentQuestion.options"
                                :key="option.id"
                                @click="selectOptionP2(option.id)"
                                :class="[
                                    'w-full flex items-center p-3 rounded-lg border-2 text-left transition-all',
                                    player2SelectedOption === option.id
                                        ? 'border-[#54b0af] bg-[#54b0af]/10'
                                        : 'border-gray-200 bg-gray-50 hover:border-[#54b0af]/50'
                                ]"
                            >
                                <span :class="[
                                    'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3',
                                    player2SelectedOption === option.id
                                        ? 'bg-[#54b0af] text-white'
                                        : 'bg-gray-200 text-gray-600'
                                ]">
                                    {{ getOptionLabel(index) }}
                                </span>
                                <span class="text-sm">{{ option.option_text }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Answer Section: Fill Blank -->
                <div v-if="isFillBlank">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div 
                            :class="[
                                'bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border-4 border-[#54b0af] transition-all duration-150',
                                shakePlayer1 ? 'shake-animation' : ''
                            ]"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <span class="bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm font-bold">PLAYER 1</span>
                                <span class="font-bold text-[#002b44]">{{ attempt1.participant_name }}</span>
                            </div>
                            <div class="bg-gray-100 rounded-xl p-4 min-h-[60px] flex items-center justify-center">
                                <span class="text-2xl font-bold text-[#54b0af] tracking-widest">
                                    {{ player1Answer || '___' }}
                                </span>
                            </div>
                        </div>

                        <div 
                            :class="[
                                'bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border-4 border-[#54b0af] transition-all duration-150',
                                shakePlayer2 ? 'shake-animation' : ''
                            ]"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <span class="font-bold text-[#002b44]">{{ attempt2.participant_name }}</span>
                                <span class="bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm font-bold">PLAYER 2</span>
                            </div>
                            <div class="bg-gray-100 rounded-xl p-4 min-h-[60px] flex items-center justify-center">
                                <span class="text-2xl font-bold text-[#54b0af] tracking-widest">
                                    {{ player2Answer || '___' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- QWERTY Keyboard Layout dengan tombol Enter -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <!-- Player 1 Keyboard -->
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6">
                            <div class="space-y-2">
                                <div v-for="(row, idx) in keyboard" :key="idx" class="flex gap-2 justify-center">
                                    <button
                                        v-for="key in row"
                                        :key="key"
                                        @click="typeKey(key, 1)"
                                        class="flex-1 bg-[#54b0af] hover:bg-[#459a99] text-white font-bold py-3 rounded-lg transition-colors duration-150 text-sm md:text-base"
                                    >
                                        {{ key }}
                                    </button>
                                </div>
                                <!-- Enter Button untuk Player 1 -->
                                <button
                                    @click="submitPlayer1"
                                    :disabled="!canSubmitP1"
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-4 rounded-lg transition-colors duration-150 flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    ENTER
                                </button>
                            </div>
                        </div>

                        <!-- Player 2 Keyboard -->
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6">
                            <div class="space-y-2">
                                <div v-for="(row, idx) in keyboard" :key="idx" class="flex gap-2 justify-center">
                                    <button
                                        v-for="key in row"
                                        :key="key"
                                        @click="typeKey(key, 2)"
                                        class="flex-1 bg-[#54b0af] hover:bg-[#459a99] text-white font-bold py-3 rounded-lg transition-colors duration-150 text-sm md:text-base"
                                    >
                                        {{ key }}
                                    </button>
                                </div>
                                <!-- Enter Button untuk Player 2 -->
                                <button
                                    @click="submitPlayer2"
                                    :disabled="!canSubmitP2"
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-4 rounded-lg transition-colors duration-150 flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    ENTER
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.animate-bounce {
    animation: bounce 1s ease-in-out;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.05); }
}

.animate-pulse {
    animation: pulse 0.5s ease-in-out;
}

/* Shake Animation */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
    20%, 40%, 60%, 80% { transform: translateX(10px); }
}

.shake-animation {
    animation: shake 0.6s ease-in-out;
    border-color: #ef4444 !important;
}

:deep(.prose) { color: inherit; }
:deep(.prose p) { margin-bottom: 0.5em; }
:deep(.prose strong) { font-weight: 600; }
:deep(.prose em) { font-style: italic; }
:deep(.prose ul), :deep(.prose ol) { margin-left: 1.5em; margin-bottom: 0.5em; }
:deep(.prose img) { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5em 0; }
</style>