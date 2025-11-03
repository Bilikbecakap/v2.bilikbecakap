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
  if (score >= 80) return "bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200";
  if (score >= 60) return "bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200";
  if (score >= 40) return "bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200";
  return "bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200";
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

const getOptionLabel = (index) => {
  return String.fromCharCode(65 + index); // A, B, C, D, ...
};
</script>

<template>
  <Head :title="`Detail Hasil - ${attempt.participant_name}`" />

  <AdminLayout>
    <template #title>Detail Hasil Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Detail Hasil Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Review jawaban peserta
          </p>
        </div>

        <div>
          <Link
            :href="route('quiz.attempts.history', quiz.id)"
            class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
          >
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            Kembali ke Riwayat
          </Link>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Answer Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Quiz Info -->
        <div
          class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl shadow-sm p-6 text-white"
        >
          <h3 class="text-xl font-bold mb-2">{{ quiz.title }}</h3>
          <div class="flex items-center gap-4 text-sm">
            <div class="flex items-center">
              <svg
                class="w-4 h-4 mr-1"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              {{ attempt.participant_name }}
            </div>
            <div class="flex items-center">
              <svg
                class="w-4 h-4 mr-1"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              {{ formatDate(attempt.completed_at) }}
            </div>
          </div>
        </div>

        <!-- Questions & Answers -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Review Jawaban
            </h3>
          </div>

          <div class="p-6 space-y-6">
            <div
              v-for="(answer, index) in attempt.answers"
              :key="answer.id"
              class="border-b border-slate-200 dark:border-slate-700 last:border-0 pb-6 last:pb-0"
            >
              <!-- Question Header -->
              <div class="flex items-start gap-3 mb-4">
                <div
                  :class="[
                    'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                    answer.is_correct
                      ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300'
                      : 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300',
                  ]"
                >
                  {{ index + 1 }}
                </div>
                <div class="flex-1">
                  <!-- Question Text dengan v-html -->
                  <div
                    class="text-base font-medium text-slate-900 dark:text-slate-100 prose prose-sm dark:prose-invert max-w-none mb-3"
                    v-html="answer.question.question"
                  ></div>
                  
                  <!-- Correct/Incorrect Badge -->
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      answer.is_correct
                        ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200'
                        : 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200',
                    ]"
                  >
                    <svg
                      class="w-3 h-3 mr-1"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        v-if="answer.is_correct"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                      />
                      <path
                        v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                    {{ answer.is_correct ? "Benar" : "Salah" }}
                  </span>
                </div>
              </div>

              <!-- Options -->
              <div class="ml-11 space-y-2">
                <div
                  v-for="(option, optIndex) in answer.question.options"
                  :key="option.id"
                  :class="[
                    'flex items-start p-3 rounded-lg border-2 text-sm transition-all',
                    option.id === answer.selected_option.id && answer.is_correct
                      ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                      : option.id === answer.selected_option.id && !answer.is_correct
                      ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                      : option.is_correct
                      ? 'border-green-300 bg-green-50/50 dark:bg-green-900/10'
                      : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50',
                  ]"
                >
                  <span
                    :class="[
                      'flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold mr-3',
                      option.id === answer.selected_option.id && answer.is_correct
                        ? 'bg-green-500 text-white'
                        : option.id === answer.selected_option.id && !answer.is_correct
                        ? 'bg-red-500 text-white'
                        : option.is_correct
                        ? 'bg-green-200 dark:bg-green-800 text-green-700 dark:text-green-200'
                        : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
                    ]"
                  >
                    {{ getOptionLabel(optIndex) }}
                  </span>
                  <span
                    :class="[
                      'flex-1',
                      option.id === answer.selected_option.id
                        ? 'font-medium'
                        : '',
                      option.is_correct || option.id === answer.selected_option.id
                        ? 'text-slate-900 dark:text-slate-100'
                        : 'text-slate-700 dark:text-slate-300',
                    ]"
                  >
                    {{ option.option_text }}
                  </span>
                  
                  <!-- Icons -->
                  <div class="flex-shrink-0 flex items-center gap-1 ml-2">
                    <!-- Selected Icon -->
                    <svg
                      v-if="option.id === answer.selected_option.id"
                      class="w-5 h-5 text-blue-600 dark:text-blue-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      title="Jawaban Peserta"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                      />
                    </svg>
                    
                    <!-- Correct Answer Icon -->
                    <svg
                      v-if="option.is_correct"
                      :class="[
                        'w-5 h-5',
                        option.id === answer.selected_option.id
                          ? 'text-green-600 dark:text-green-400'
                          : 'text-green-500 dark:text-green-500',
                      ]"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      title="Jawaban Benar"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Summary -->
      <div class="space-y-6">
        <!-- Score Card -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Skor & Statistik
            </h3>
          </div>

          <div class="p-6 space-y-4">
            <!-- Score -->
            <div class="text-center p-6 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
              <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Nilai Akhir</p>
              <p :class="['text-5xl font-bold mb-1', getScoreColor(attempt.score)]">
                {{ attempt.score }}
              </p>
              <span
                :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                  getScoreBadge(attempt.score),
                ]"
              >
                {{
                  attempt.score >= 80
                    ? "Sangat Baik"
                    : attempt.score >= 60
                    ? "Baik"
                    : attempt.score >= 40
                    ? "Cukup"
                    : "Kurang"
                }}
              </span>
            </div>

            <!-- Statistics -->
            <div class="space-y-3">
              <!-- Correct Answers -->
              <div
                class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"
              >
                <div class="flex items-center">
                  <div
                    class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center mr-3"
                  >
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Benar</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-white">
                      {{ attempt.correct_answers }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Wrong Answers -->
              <div
                class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg"
              >
                <div class="flex items-center">
                  <div
                    class="w-10 h-10 rounded-lg bg-red-500 flex items-center justify-center mr-3"
                  >
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Salah</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-white">
                      {{ attempt.total_questions - attempt.correct_answers }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Total Questions -->
              <div
                class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
              >
                <div class="flex items-center">
                  <div
                    class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center mr-3"
                  >
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Soal</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-white">
                      {{ attempt.total_questions }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Participant Info -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Info Peserta
            </h3>
          </div>

          <div class="p-6 space-y-3">
            <div>
              <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                Nama Peserta
              </label>
              <p class="text-sm font-medium text-slate-800 dark:text-white">
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
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
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