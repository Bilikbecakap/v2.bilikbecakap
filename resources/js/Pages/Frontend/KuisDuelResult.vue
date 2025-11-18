<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    quiz: Object,
    attempt1: Object,
    attempt2: Object,
});

const winner = computed(() => {
    if (props.attempt1.winner === 'player1') {
        return {
            name: props.attempt1.player1_name || 'Player 1',
            score: props.attempt1.player1_score || 0,
            emoji: '🏆',
            color: 'text-[#54b0af]'
        };
    } else if (props.attempt1.winner === 'player2') {
        return {
            name: props.attempt1.player2_name || 'Player 2',
            score: props.attempt1.player2_score || 0,
            emoji: '🏆',
            color: 'text-[#54b0af]'
        };
    } else {
        return {
            name: 'SERI',
            score: props.attempt1.player1_score || 0,
            emoji: '🤝',
            color: 'text-gray-600'
        };
    }
});

const player1Score = computed(() => props.attempt1.player1_score || 0);
const player2Score = computed(() => props.attempt1.player2_score || 0);
const totalQuestions = computed(() => props.quiz.total_questions || 1);
</script>

<template>
    <Head :title="`Hasil Duel - ${quiz.title}`" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <div class="container mx-auto px-6 relative z-20">
                <!-- Winner Card -->
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl border-4 border-[#54b0af] p-12 text-center mb-8">
                        <div class="text-8xl mb-6">{{ winner.emoji }}</div>
                        <h1 class="text-5xl font-bold mb-4" :class="winner.color">
                            {{ winner.name }}
                        </h1>
                        <p v-if="attempt1.winner !== 'draw'" class="text-2xl text-gray-600 mb-8">
                            PEMENANGNYA!
                        </p>
                        <p v-else class="text-2xl text-gray-600 mb-8">
                            Hasil SERI!
                        </p>

                        <!-- Score Board -->
                        <div class="bg-gradient-to-r from-[#54b0af]/20 to-[#459a99]/20 rounded-2xl p-8 mb-8">
                            <div class="grid grid-cols-3 gap-4 items-center">
                                <!-- Player 1 -->
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-[#54b0af]">{{ player1Score }}</div>
                                    <div class="text-lg font-semibold text-gray-700 mt-2">{{ attempt1.player1_name || 'Player 1' }}</div>
                                </div>

                                <!-- VS -->
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-400">VS</div>
                                    <div class="text-sm text-gray-500 mt-2">{{ totalQuestions }} Soal</div>
                                </div>

                                <!-- Player 2 -->
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-[#54b0af]">{{ player2Score }}</div>
                                    <div class="text-lg font-semibold text-gray-700 mt-2">{{ attempt1.player2_name || 'Player 2' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                :href="`/kuis/${quiz.slug}/tantangan`"
                                class="inline-flex items-center justify-center px-8 py-4 bg-[#54b0af] hover:bg-[#459a99] text-white font-bold rounded-xl transition-all duration-300 transform hover:scale-105"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Main Lagi
                            </Link>

                            <Link
                                href="/kuis"
                                class="inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-50 text-[#54b0af] font-bold rounded-xl border-2 border-[#54b0af] transition-all duration-300"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Kembali ke Kuis
                            </Link>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Player 1 Stats -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm">P1</span>
                                {{ attempt1.player1_name || 'Player 1' }}
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Jawaban Benar</span>
                                    <span class="font-bold text-[#54b0af]">{{ player1Score }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Persentase</span>
                                    <span class="font-bold text-[#54b0af]">{{ Math.round((player1Score / totalQuestions) * 100) }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Player 2 Stats -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm">P2</span>
                                {{ attempt1.player2_name || 'Player 2' }}
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Jawaban Benar</span>
                                    <span class="font-bold text-[#54b0af]">{{ player2Score }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Persentase</span>
                                    <span class="font-bold text-[#54b0af]">{{ Math.round((player2Score / totalQuestions) * 100) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>