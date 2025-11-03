<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  quiz: Object,
  attempts: Object,
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

const getDuration = (attempt) => {
  if (!attempt.started_at || !attempt.completed_at) return "-";
  const start = new Date(attempt.started_at);
  const end = new Date(attempt.completed_at);
  const diff = Math.floor((end - start) / 1000); // in seconds
  const minutes = Math.floor(diff / 60);
  const seconds = diff % 60;
  return `${minutes}m ${seconds}s`;
};

// Calculate statistics
const totalAttempts = props.attempts.total;
const averageScore = props.attempts.data.length > 0
  ? (props.attempts.data.reduce((sum, attempt) => sum + attempt.score, 0) / props.attempts.data.length).toFixed(2)
  : 0;
const highestScore = props.attempts.data.length > 0
  ? Math.max(...props.attempts.data.map((attempt) => attempt.score))
  : 0;
const lowestScore = props.attempts.data.length > 0
  ? Math.min(...props.attempts.data.map((attempt) => attempt.score))
  : 0;
</script>

<template>
  <Head :title="`Riwayat Pengerjaan - ${quiz.title}`" />

  <AdminLayout>
    <template #title>Riwayat Pengerjaan Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Riwayat Pengerjaan Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ quiz.title }}
          </p>
        </div>

        <div>
          <Link
            :href="route('quiz.show', quiz.id)"
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
            Kembali ke Quiz
          </Link>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <!-- Total Attempts -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
              Total Pengerjaan
            </p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">
              {{ totalAttempts }}
            </p>
          </div>
          <div
            class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center"
          >
            <svg
              class="w-6 h-6 text-blue-600 dark:text-blue-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Average Score -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Rata-rata Nilai</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
              {{ averageScore }}
            </p>
          </div>
          <div
            class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center"
          >
            <svg
              class="w-6 h-6 text-purple-600 dark:text-purple-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Highest Score -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Nilai Tertinggi</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
              {{ highestScore }}
            </p>
          </div>
          <div
            class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center"
          >
            <svg
              class="w-6 h-6 text-green-600 dark:text-green-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Lowest Score -->
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Nilai Terendah</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
              {{ lowestScore }}
            </p>
          </div>
          <div
            class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center"
          >
            <svg
              class="w-6 h-6 text-red-600 dark:text-red-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Attempts Table -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
            Daftar Pengerjaan
          </h3>
          <span class="text-sm text-slate-500 dark:text-slate-400">
            {{ attempts.total }} pengerjaan
          </span>
        </div>
      </div>

      <!-- Table Content -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                #
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Nama Peserta
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Waktu Pengerjaan
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Durasi
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Hasil
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Nilai
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <tr
              v-if="attempts.data.length === 0"
              class="hover:bg-slate-50 dark:hover:bg-slate-700/50"
            >
              <td colspan="7" class="px-6 py-12 text-center">
                <svg
                  class="w-12 h-12 text-slate-400 dark:text-slate-500 mx-auto mb-3"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Belum ada riwayat pengerjaan untuk quiz ini
                </p>
              </td>
            </tr>

            <tr
              v-else
              v-for="(attempt, index) in attempts.data"
              :key="attempt.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150"
            >
              <!-- Number -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                  {{ attempts.from + index }}
                </span>
              </td>

              <!-- Participant Name -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div
                    class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center"
                  >
                    <span class="text-sm font-medium text-blue-600 dark:text-blue-300">
                      {{ attempt.participant_name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                      {{ attempt.participant_name }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Date -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900 dark:text-slate-100">
                  {{ formatDate(attempt.completed_at) }}
                </div>
              </td>

              <!-- Duration -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200"
                >
                  <svg
                    class="w-3 h-3 mr-1"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  {{ getDuration(attempt) }}
                </span>
              </td>

              <!-- Result -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                  <span class="font-medium text-green-600 dark:text-green-400">{{
                    attempt.correct_answers
                  }}</span>
                  /
                  <span class="font-medium">{{ attempt.total_questions }}</span>
                  benar
                </div>
              </td>

              <!-- Score -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span :class="['text-2xl font-bold mr-2', getScoreColor(attempt.score)]">
                    {{ attempt.score }}
                  </span>
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                      getScoreBadge(attempt.score),
                    ]"
                  >
                    {{ getScoreGrade(attempt.score) }}
                  </span>
                </div>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Link
                  :href="route('quiz.attempts.show', [quiz.id, attempt.id])"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150"
                >
                  <svg
                    class="w-3 h-3 mr-1"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                  Lihat Detail
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="attempts.data.length > 0"
        class="px-6 py-4 border-t border-slate-200 dark:border-slate-700"
      >
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-600 dark:text-slate-400">
            Menampilkan {{ attempts.from }} - {{ attempts.to }} dari
            {{ attempts.total }} pengerjaan
          </div>

          <div class="flex items-center gap-2">
            <!-- Previous Button -->
            <Link
              v-if="attempts.prev_page_url"
              :href="attempts.prev_page_url"
              class="px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              Previous
            </Link>

            <!-- Page Numbers -->
            <div class="hidden sm:flex items-center gap-1">
              <template v-for="(link, index) in attempts.links" :key="index">
                <Link
                  v-if="link.url && index !== 0 && index !== attempts.links.length - 1"
                  :href="link.url"
                  :class="[
                    'px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-150',
                    link.active
                      ? 'bg-blue-500 text-white'
                      : 'text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700',
                  ]"
                  v-html="link.label"
                >
                </Link>
              </template>
            </div>

            <!-- Next Button -->
            <Link
              v-if="attempts.next_page_url"
              :href="attempts.next_page_url"
              class="px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>