<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    quiz: Object,
    attempt: Object,
    questions: Array,
});

const expandedAnswers = ref([]);

const scorePercentage = computed(() => {
    return Math.round((props.attempt.correct_answers / props.attempt.total_questions) * 100);
});

const getScoreColor = computed(() => {
    const score = props.attempt.score;
    if (score >= 90) return 'green';
    if (score >= 80) return 'blue';
    if (score >= 70) return 'yellow';
    if (score >= 60) return 'orange';
    return 'red';
});

const toggleAnswer = (index) => {
    if (expandedAnswers.value.includes(index)) {
        expandedAnswers.value = expandedAnswers.value.filter(i => i !== index);
    } else {
        expandedAnswers.value.push(index);
    }
};

const getAnswerStatus = (answer) => {
    if (answer.quiz_option_id === null) {
        return { status: 'Tidak dijawab', color: 'gray', icon: '⊘' };
    }
    return answer.is_correct 
        ? { status: 'Benar', color: 'green', icon: '✓' }
        : { status: 'Salah', color: 'red', icon: '✗' };
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getDuration = () => {
    if (!props.attempt.started_at || !props.attempt.completed_at) return '—';
    const start = new Date(props.attempt.started_at);
    const end = new Date(props.attempt.completed_at);
    const minutes = Math.round((end - start) / 60000);
    return `${minutes} menit`;
};
</script>

<template>
    <Head :title="`Hasil Kuis - ${quiz.title}`" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <!-- Decorative -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <div class="container mx-auto px-6 relative z-20">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-[#54b0af] mb-2">
                        🎉 Selesai!
                    </h1>
                    <p class="text-lg text-[#002b44]/80">
                        Hasil kuis {{ quiz.title }} untuk <b>{{ attempt.participant_name }}</b>
                    </p>
                </div>

                <!-- Score Card -->
                <div class="max-w-2xl mx-auto mb-12">
                    <div class="bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                        <div :class="{
                            'bg-gradient-to-r from-green-500 to-green-600': getScoreColor === 'green',
                            'bg-gradient-to-r from-blue-500 to-blue-600': getScoreColor === 'blue',
                            'bg-gradient-to-r from-yellow-500 to-yellow-600': getScoreColor === 'yellow',
                            'bg-gradient-to-r from-orange-500 to-orange-600': getScoreColor === 'orange',
                            'bg-gradient-to-r from-red-500 to-red-600': getScoreColor === 'red'
                        }" class="p-12 text-white text-center">
                            <div class="mb-6">
                                <div class="text-8xl font-bold mb-2">{{ attempt.score }}</div>
                                <div class="text-2xl font-semibold">Nilai Anda</div>
                            </div>
                        </div>

                        <!-- Score Details -->
                        <div class="p-8 space-y-6">
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <div class="text-3xl font-bold text-[#54b0af]">{{ attempt.correct_answers }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Jawaban Benar</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <div class="text-3xl font-bold text-red-500">{{ attempt.total_questions - attempt.correct_answers }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Jawaban Salah</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <div class="text-3xl font-bold text-[#002b44]">{{ attempt.total_questions }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Total Soal</div>
                                </div>
                            </div>

                            <!-- Additional Info -->
                            <div class="border-t border-gray-200 pt-6 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Tanggal & Waktu</span>
                                    <span class="font-semibold text-[#002b44]">{{ formatDate(attempt.completed_at) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Durasi Pengerjaan</span>
                                    <span class="font-semibold text-[#002b44]">{{ getDuration() }}</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                <Link href="/kuis"
                                    class="flex-1 bg-white border-2 border-[#54b0af] hover:bg-[#54b0af]/5 text-[#54b0af] font-bold py-3 px-6 rounded-xl text-center transition-all">
                                    Kembali ke Kuis
                                </Link>
                                <button @click="window.print()"
                                    class="flex-1 bg-[#54b0af] hover:bg-[#459a99] text-white font-bold py-3 px-6 rounded-xl transition-all flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4H7a2 2 0 01-2-2v-4a2 2 0 012-2h10a2 2 0 012 2v4a2 2 0 01-2 2zm2-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Cetak Hasil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pembahasan Jawaban -->
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-3xl font-bold text-[#002b44] mb-8 text-center">
                        Pembahasan Jawaban
                    </h2>

                    <div class="space-y-4">
                        <div v-for="(question, idx) in questions" :key="question.id"
                            class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden hover:shadow-xl transition-shadow">
                            
                            <!-- Question Header -->
                            <button @click="toggleAnswer(idx)"
                                class="w-full px-6 py-5 flex items-start justify-between hover:bg-gray-50 transition-colors">
                                <div class="flex-1 text-left space-y-2">
                                    <!-- Question Number & Status -->
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-[#54b0af] text-white font-bold text-sm">
                                            {{ idx + 1 }}
                                        </div>
                                        <h3 class="font-semibold text-[#002b44] text-lg prose prose-sm max-w-none" v-html="question.question"></h3>
                                    </div>

                                    <!-- Answer Status -->
                                    <div v-if="attempt.answers && attempt.answers[idx]" class="ml-11">
                                        <span :class="{
                                            'bg-green-100 text-green-700': getAnswerStatus(attempt.answers[idx]).color === 'green',
                                            'bg-red-100 text-red-700': getAnswerStatus(attempt.answers[idx]).color === 'red',
                                            'bg-gray-100 text-gray-700': getAnswerStatus(attempt.answers[idx]).color === 'gray'
                                        }" class="text-xs font-bold px-3 py-1 rounded-full inline-block">
                                            {{ getAnswerStatus(attempt.answers[idx]).icon }} {{ getAnswerStatus(attempt.answers[idx]).status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Expand Icon -->
                                <svg :class="{ 'rotate-180': expandedAnswers.includes(idx) }"
                                    class="w-6 h-6 text-gray-400 flex-shrink-0 ml-4 transition-transform"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Answer Details (Expanded) -->
                            <div v-if="expandedAnswers.includes(idx)" class="border-t border-gray-200 px-6 py-6 space-y-4">
                                <!-- Options dengan highlight -->
                                <div class="space-y-3">
                                    <div class="font-semibold text-gray-600 text-sm mb-2">Pilihan Jawaban:</div>
                                    <div v-for="(option, optIdx) in question.options" :key="option.id"
                                        class="p-4 rounded-xl border-2 transition-all"
                                        :class="{
                                            'border-green-500 bg-green-50': option.is_correct,
                                            'border-red-500 bg-red-50': !option.is_correct && attempt.answers && attempt.answers[idx] && attempt.answers[idx].quiz_option_id === option.id,
                                            'border-gray-200 bg-gray-50': option.id !== attempt.answers?.[idx]?.quiz_option_id && !option.is_correct
                                        }">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 pt-0.5">
                                                <div v-if="option.is_correct" class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-sm">
                                                    ✓
                                                </div>
                                                <div v-else-if="attempt.answers && attempt.answers[idx] && attempt.answers[idx].quiz_option_id === option.id"
                                                    class="w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center text-sm">
                                                    ✗
                                                </div>
                                                <div v-else class="text-gray-400">
                                                    {{ String.fromCharCode(65 + optIdx) }}.
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-[#002b44]">{{ option.option_text }}</p>
                                                <p v-if="option.is_correct" class="text-xs text-green-600 font-semibold mt-1">
                                                    ✓ Jawaban Benar
                                                </p>
                                                <p v-if="attempt.answers && attempt.answers[idx] && attempt.answers[idx].quiz_option_id === option.id && !option.is_correct"
                                                    class="text-xs text-red-600 font-semibold mt-1">
                                                    ✗ Jawaban Anda
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Your Answer Summary -->
                                <div v-if="attempt.answers && attempt.answers[idx]" class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                    <p class="text-sm text-blue-900">
                                        <strong>Jawaban Anda:</strong>
                                        <span v-if="attempt.answers[idx].quiz_option_id === null" class="text-blue-600">Tidak dijawab</span>
                                        <span v-else-if="attempt.answers[idx].is_correct" class="text-green-600">Benar ✓</span>
                                        <span v-else class="text-red-600">Salah ✗</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action -->
                <div class="max-w-4xl mx-auto mt-12 text-center">
                    <Link href="/kuis"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-[#54b0af] to-[#459a99] hover:shadow-lg text-white font-bold py-4 px-8 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Daftar Kuis
                    </Link>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none;
    }
    
    body {
        background: white;
    }
}

:deep(.prose) { color: inherit; }
:deep(.prose p) { margin-bottom: 0.5em; }
:deep(.prose strong) { font-weight: 600; }
:deep(.prose em) { font-style: italic; }
:deep(.prose ul), :deep(.prose ol) { margin-left: 1.5em; margin-bottom: 0.5em; }
:deep(.prose img) { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5em 0; }
</style>