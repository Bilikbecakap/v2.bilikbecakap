<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  quiz: Object,
  attempt: Object,
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getScoreColor = (score) => {
  if (score >= 80) return "text-green-600 dark:text-green-400";
  if (score >= 60) return "text-blue-600 dark:text-blue-400";
  if (score >= 40) return "text-yellow-600 dark:text-yellow-400";
  return "text-red-600 dark:text-red-400";
};

const getScoreBadge = (score) => {
  if (score >= 80)
    return "bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200";
  if (score >= 60)
    return "bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200";
  if (score >= 40)
    return "bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200";
  return "bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200";
};

const getScoreGrade = (score) => {
  if (score >= 80) return "Sangat Baik";
  if (score >= 60) return "Baik";
  if (score >= 40) return "Cukup";
  return "Kurang";
};

const duration = computed(() => {
  if (!props.attempt.started_at || !props.attempt.completed_at) return "-";
  const start = new Date(props.attempt.started_at);
  const end = new Date(props.attempt.completed_at);
  const diff = Math.floor((end - start) / 1000); // in seconds
  const minutes = Math.floor(diff / 60);
  const seconds = diff % 60;
  return `${minutes} menit ${seconds} detik`;
});

const percentage = computed(() => {
  return props.attempt.score;
});

const wrongAnswers = computed(() => {
  return props.attempt.total_questions - props.attempt.correct_answers;
});

const getOptionLabel = (index) => {
  return String.fromCharCode(65 + index); // A, B, C, D, ...
};
</script>

<template>

  <Head :title="`Hasil Quiz - ${quiz.title}`" />

  <AdminLayout>
    <template #title>Hasil Quiz</template>

    <!-- Celebration Banner -->
    <div
      class="mb-6 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-xl shadow-lg p-8 text-white text-center overflow-hidden relative">
      <!-- Decorative elements -->
      <div class="absolute top-0 left-0 w-full h-full opacity-10">
        <div class="absolute top-4 left-4 w-16 h-16 bg-white rounded-full animate-pulse"></div>
        <div class="absolute bottom-4 right-4 w-20 h-20 bg-white rounded-full animate-pulse delay-75"></div>
        <div class="absolute top-1/2 left-1/4 w-12 h-12 bg-white rounded-full animate-pulse delay-150"></div>
      </div>

      <div class="relative z-10">
        <svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2 class="text-3xl font-bold mb-2">Selamat, {{ attempt.participant_name }}!</h2>
        <p class="text-lg opacity-90">Quiz Anda telah selesai dikerjakan</p>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Detailed Review -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Score Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Skor Anda
            </h3>
          </div>

          <div class="p-6">
            <div class="text-center mb-6">
              <p :class="['text-7xl font-bold mb-2', getScoreColor(attempt.score)]">
                {{ attempt.score }}
              </p>
              <span :class="[
                'inline-flex items-center px-4 py-2 rounded-full text-base font-medium',
                getScoreBadge(attempt.score),
              ]">
                {{ getScoreGrade(attempt.score) }}
              </span>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-3 gap-4">
              <!-- Correct Answers -->
              <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-green-500 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                  {{ attempt.correct_answers }}
                </p>
                <p class="text-xs text-slate-600 dark:text-slate-400">Benar</p>
              </div>

              <!-- Wrong Answers -->
              <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-red-500 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </div>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                  {{ wrongAnswers }}
                </p>
                <p class="text-xs text-slate-600 dark:text-slate-400">Salah</p>
              </div>

              <!-- Total Questions -->
              <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-blue-500 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                  {{ attempt.total_questions }}
                </p>
                <p class="text-xs text-slate-600 dark:text-slate-400">Total Soal</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Answer Review -->
        <!-- Answer Review -->
        <div class="p-6 space-y-6">
          <div v-for="(answer, index) in attempt.answers" :key="answer.id"
            class="border-b border-slate-200 dark:border-slate-700 last:border-0 pb-6 last:pb-0">
            <!-- Question Header -->
            <div class="flex items-start gap-3 mb-4">
              <div :class="[
                'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                // 👇 UBAH: Tambah kondisi untuk tidak dijawab
                !answer.selected_option
                  ? 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400'
                  : answer.is_correct
                    ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300'
                    : 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300',
              ]">
                {{ index + 1 }}
              </div>
              <div class="flex-1">
                <!-- Question Text -->
                <div
                  class="text-base font-medium text-slate-900 dark:text-slate-100 prose prose-sm dark:prose-invert max-w-none mb-3"
                  v-html="answer.question.question"></div>

                <!-- 👇 UBAH: Badge untuk status jawaban -->
                <span v-if="!answer.selected_option"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-900/20 text-slate-800 dark:text-slate-200">
                  <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                  Tidak Dijawab
                </span>
                <span v-else :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  answer.is_correct
                    ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200'
                    : 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200',
                ]">
                  <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="answer.is_correct" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 13l4 4L19 7" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  {{ answer.is_correct ? "Jawaban Benar" : "Jawaban Salah" }}
                </span>
              </div>
            </div>

            <!-- Options -->
            <div class="ml-11 space-y-2">
              <div v-for="(option, optIndex) in answer.question.options" :key="option.id" :class="[
                'flex items-start p-3 rounded-lg border-2 text-sm transition-all',
                // 👇 UBAH: Styling untuk berbagai kondisi
                answer.selected_option && option.id === answer.selected_option.id && answer.is_correct
                  ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                  : answer.selected_option && option.id === answer.selected_option.id && !answer.is_correct
                    ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                    : option.is_correct
                      ? 'border-green-300 bg-green-50/50 dark:bg-green-900/10'
                      : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50',
              ]">
                <span :class="[
                  'flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold mr-3',
                  answer.selected_option && option.id === answer.selected_option.id && answer.is_correct
                    ? 'bg-green-500 text-white'
                    : answer.selected_option && option.id === answer.selected_option.id && !answer.is_correct
                      ? 'bg-red-500 text-white'
                      : option.is_correct
                        ? 'bg-green-200 dark:bg-green-800 text-green-700 dark:text-green-200'
                        : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
                ]">
                  {{ getOptionLabel(optIndex) }}
                </span>
                <span :class="[
                  'flex-1',
                  answer.selected_option && option.id === answer.selected_option.id ? 'font-medium' : '',
                  option.is_correct || (answer.selected_option && option.id === answer.selected_option.id)
                    ? 'text-slate-900 dark:text-slate-100'
                    : 'text-slate-700 dark:text-slate-300',
                ]">
                  {{ option.option_text }}
                </span>

                <!-- Icons -->
                <div class="flex-shrink-0 flex items-center gap-1 ml-2">
                  <!-- Your Answer Icon - 👇 UBAH: Cek null -->
                  <div v-if="answer.selected_option && option.id === answer.selected_option.id" class="relative group">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span
                      class="absolute hidden group-hover:block bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-slate-900 rounded whitespace-nowrap">
                      Jawaban Anda
                    </span>
                  </div>

                  <!-- Correct Answer Icon -->
                  <div v-if="option.is_correct" class="relative group">
                    <svg :class="[
                      'w-5 h-5',
                      answer.selected_option && option.id === answer.selected_option.id
                        ? 'text-green-600 dark:text-green-400'
                        : 'text-green-500 dark:text-green-500',
                    ]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span
                      class="absolute hidden group-hover:block bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-slate-900 rounded whitespace-nowrap">
                      Jawaban Benar
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Summary & Actions -->
      <div class="space-y-6">
        <!-- Quiz Info -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Informasi Quiz
            </h3>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                Judul Quiz
              </label>
              <p class="text-sm font-medium text-slate-800 dark:text-white">
                {{ quiz.title }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                Nama Peserta
              </label>
              <p class="text-sm text-slate-700 dark:text-slate-300">
                {{ attempt.participant_name }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                Waktu Mulai
              </label>
              <p class="text-sm text-slate-700 dark:text-slate-300">
                {{ formatDate(attempt.started_at) }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                Waktu Selesai
              </label>
              <p class="text-sm text-slate-700 dark:text-slate-300">
                {{ formatDate(attempt.completed_at) }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                Durasi Pengerjaan
              </label>
              <p class="text-sm font-medium text-slate-800 dark:text-white">
                {{ duration }}
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Aksi</h3>
          </div>

          <div class="p-6 space-y-3">
            <!-- Back to Quiz List -->
            <Link :href="route('quiz.attempt.index')"
              class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Kembali ke Daftar Quiz
            </Link>
          </div>
        </div>

        <!-- Motivation Card -->
        <div :class="[
          'rounded-xl shadow-sm border-2 p-6 text-center',
          attempt.score >= 80
            ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
            : attempt.score >= 60
              ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800'
              : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
        ]">
          <svg :class="[
            'w-12 h-12 mx-auto mb-3',
            attempt.score >= 80
              ? 'text-green-500'
              : attempt.score >= 60
                ? 'text-blue-500'
                : 'text-yellow-500',
          ]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p :class="[
            'text-sm font-medium mb-1',
            attempt.score >= 80
              ? 'text-green-800 dark:text-green-200'
              : attempt.score >= 60
                ? 'text-blue-800 dark:text-blue-200'
                : 'text-yellow-800 dark:text-yellow-200',
          ]">
            {{
              attempt.score >= 80
                ? "Luar Biasa!"
                : attempt.score >= 60
                  ? "Kerja Bagus!"
                  : "Tetap Semangat!"
            }}
          </p>
          <p :class="[
            'text-xs',
            attempt.score >= 80
              ? 'text-green-700 dark:text-green-300'
              : attempt.score >= 60
                ? 'text-blue-700 dark:text-blue-300'
                : 'text-yellow-700 dark:text-yellow-300',
          ]">
            {{
              attempt.score >= 80
                ? "Pertahankan prestasi Anda yang gemilang!"
                : attempt.score >= 60
                  ? "Anda sudah melakukan pekerjaan yang baik!"
                  : "Terus belajar dan tingkatkan lagi!"
            }}
          </p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
/* Animation for decorative elements */
@keyframes pulse {

  0%,
  100% {
    opacity: 0.1;
  }

  50% {
    opacity: 0.2;
  }
}

.animate-pulse {
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-75 {
  animation-delay: 0.75s;
}

.delay-150 {
  animation-delay: 1.5s;
}

/* Custom styling untuk konten HTML dari Quill Editor */
:deep(.prose) {
  color: inherit;
}

:deep(.prose p) {
  margin-bottom: 0.5em;
}

:deep(.prose strong) {
  font-weight: 600;
}

:deep(.prose em) {
  font-style: italic;
}

:deep(.prose ul),
:deep(.prose ol) {
  margin-left: 1.5em;
  margin-bottom: 0.5em;
}

:deep(.prose img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 0.5em 0;
}
</style>