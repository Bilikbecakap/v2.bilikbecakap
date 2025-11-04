<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  quiz: Object,
  totalAttempts: Number,
  averageScore: Number,
});

// DEBUG
console.log('Quiz data:', props.quiz);

const showDeleteModal = ref(false);

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const deleteQuiz = () => {
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(route("quiz.destroy", props.quiz.id), {
    onSuccess: () => {
      // Redirect handled by controller
    },
  });
};

const getStatusBadge = (status) => {
  const badges = {
    active: "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200",
    inactive: "bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200",
  };
  return badges[status] || badges["inactive"];
};

const getStatusText = (status) => {
  const texts = {
    active: "Aktif",
    inactive: "Tidak Aktif",
  };
  return texts[status] || status;
};

const getTypeBadge = (type) => {
  const badges = {
    modul: "bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200",
    umum: "bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200",
  };
  return badges[type] || badges["umum"];
};

const getTypeText = (type) => {
  const texts = {
    modul: "Quiz Modul",
    umum: "Quiz Umum",
  };
  return texts[type] || type;
};
</script>

<template>
  <Head :title="quiz.title" />

  <AdminLayout>
    <template #title>Detail Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Detail Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Informasi lengkap tentang quiz
          </p>
        </div>

        <div>
          <Link
            :href="route('quiz.index')"
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
            Kembali
          </Link>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Quiz Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- UBAH: Thumbnail & Media Section -->
        <div
          v-if="quiz.thumbnail_url || quiz.master_media_music_quiz"
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Media Quiz
            </h3>
          </div>

          <div class="p-6 space-y-4">
            <!-- Thumbnail -->
            <div v-if="quiz.thumbnail_url">
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">
                Thumbnail
              </label>
              <div class="relative w-full h-64 rounded-lg overflow-hidden border-2 border-slate-200 dark:border-slate-700">
                <img
                  :src="quiz.thumbnail_url"
                  :alt="quiz.title"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>

            <!-- UBAH: Background Music dari Master Media Music Quiz -->
            <div v-if="quiz.master_media_music_quiz">
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">
                Background Music
              </label>
              <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-500 rounded-full">
                      <svg
                        class="w-6 h-6 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                        />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-800 dark:text-white">
                        {{ quiz.master_media_music_quiz.audio.split('/').pop() }}
                      </p>
                      <p v-if="quiz.master_media_music_quiz.keterangan" class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        {{ quiz.master_media_music_quiz.keterangan }}
                      </p>
                      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Audio akan diputar saat quiz dikerjakan
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Audio Player dengan URL yang benar -->
                <audio
                  v-if="quiz.master_media_music_quiz.audio_url"
                  controls
                  preload="metadata"
                  class="w-full h-10"
                  :src="quiz.master_media_music_quiz.audio_url"
                  @error="(e) => console.error('Audio error:', e)"
                >
                  Browser Anda tidak mendukung audio player.
                </audio>

                <!-- Warning jika audio_url tidak ada -->
                <div v-else class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-700">
                  <p class="text-xs text-yellow-800 dark:text-yellow-200">
                    ⚠️ File audio tidak ditemukan
                  </p>
                </div>
              </div>
            </div>

            <!-- Info jika tidak ada music -->
            <div v-else class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  Quiz ini tidak memiliki background music
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quiz Information -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Informasi Quiz
            </h3>
          </div>

          <div class="p-6 space-y-4">
            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                Judul Quiz
              </label>
              <p class="text-base font-semibold text-slate-800 dark:text-white">
                {{ quiz.title }}
              </p>
            </div>

            <!-- Description -->
            <div v-if="quiz.description">
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                Deskripsi
              </label>
              <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                {{ quiz.description }}
              </p>
            </div>

            <!-- Type & Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                  Tipe Quiz
                </label>
                <span
                  :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                    getTypeBadge(quiz.type),
                  ]"
                >
                  {{ getTypeText(quiz.type) }}
                </span>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                  Status
                </label>
                <span
                  :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                    getStatusBadge(quiz.status),
                  ]"
                >
                  {{ getStatusText(quiz.status) }}
                </span>
              </div>
            </div>

            <!-- Modul Pembelajaran -->
            <div v-if="quiz.modul_pembelajaran">
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                Modul Pembelajaran
              </label>
              <div class="flex items-center">
                <svg
                  class="w-5 h-5 text-blue-500 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                  />
                </svg>
                <p class="text-sm font-medium text-slate-800 dark:text-white">
                  {{ quiz.modul_pembelajaran.title }}
                </p>
              </div>
            </div>

            <!-- Duration -->
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                Durasi Quiz
              </label>
              <div class="flex items-center text-slate-700 dark:text-slate-300">
                <svg
                  class="w-5 h-5 text-blue-500 mr-2"
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
                <span class="text-sm font-medium">{{ quiz.duration }} menit</span>
              </div>
            </div>

            <!-- Timestamps -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-500 dark:text-slate-400">
                <div>
                  <span class="font-medium">Dibuat:</span>
                  {{ formatDate(quiz.created_at) }}
                </div>
                <div>
                  <span class="font-medium">Diupdate:</span>
                  {{ formatDate(quiz.updated_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Questions List -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Daftar Soal
            </h3>
            <Link
              :href="route('quiz.questions.index', quiz.id)"
              class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150"
            >
              Kelola Soal
              <svg
                class="w-4 h-4 ml-1"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </Link>
          </div>

          <div class="p-6">
            <div v-if="quiz.questions && quiz.questions.length > 0" class="space-y-3">
              <div
                v-for="(question, index) in quiz.questions"
                :key="question.id"
                class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700"
              >
                <div class="flex items-start">
                  <span
                    class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 text-xs font-bold mr-3"
                  >
                    {{ index + 1 }}
                  </span>
                  <div class="flex-1">
                    <div 
                      class="text-sm font-medium text-slate-800 dark:text-white prose prose-sm dark:prose-invert max-w-none"
                      v-html="question.question"
                    ></div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                      {{ question.options?.length || 0 }} pilihan jawaban
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8">
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
                Belum ada soal untuk quiz ini
              </p>
              <Link
                :href="route('quiz.questions.create', quiz.id)"
                class="inline-flex items-center mt-3 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-150"
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
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                Tambah Soal
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Statistics & Actions (tetap sama) -->
      <div class="space-y-6">
        <!-- Statistics -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Statistik
            </h3>
          </div>

          <div class="p-6 space-y-4">
            <!-- Total Questions -->
            <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center mr-3">
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
                  <p class="text-xl font-bold text-slate-800 dark:text-white">
                    {{ quiz.total_questions || 0 }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Total Attempts -->
            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center mr-3">
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
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Pengerjaan</p>
                  <p class="text-xl font-bold text-slate-800 dark:text-white">
                    {{ totalAttempts }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Average Score -->
            <div class="flex items-center justify-between p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
              <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center mr-3">
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
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Rata-rata Nilai</p>
                  <p class="text-xl font-bold text-slate-800 dark:text-white">
                    {{ averageScore }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div
          class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
        >
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
              Aksi
            </h3>
          </div>

          <div class="p-6 space-y-3">
            <!-- Manage Questions -->
            <Link
              :href="route('quiz.questions.index', quiz.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-sm font-medium rounded-lg hover:shadow-md transition-all duration-200"
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
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              Kelola Soal
            </Link>

            <!-- View Attempts -->
            <Link
              :href="route('quiz.attempts.history', quiz.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
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
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                />
              </svg>
              Lihat Riwayat Pengerjaan
            </Link>

            <!-- Edit Quiz -->
            <Link
              :href="route('quiz.edit', quiz.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400 text-sm font-medium rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150"
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
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                />
              </svg>
              Edit Quiz
            </Link>

            <!-- Delete Quiz -->
            <button
              @click="deleteQuiz"
              class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 text-sm font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-150"
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
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
              Hapus Quiz
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal (tetap sama) -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
      >
        <div
          class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="showDeleteModal = false"
        ></div>
        <span
          class="hidden sm:inline-block sm:align-middle sm:h-screen"
          aria-hidden="true"
          >&#8203;</span
        >
        <div
          class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
        >
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10"
            >
              <svg
                class="h-6 w-6 text-red-600 dark:text-red-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3
                class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100"
                id="modal-title"
              >
                Hapus Quiz
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Apakah Anda yakin ingin menghapus quiz
                  <strong>{{ quiz.title }}</strong
                  >? Semua soal dan data terkait akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              @click="confirmDelete"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
            >
              Hapus
            </button>
            <button
              @click="showDeleteModal = false"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-150"
            >
              Batal
            </button>
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

/* Custom audio player styling */
audio {
  filter: sepia(20%) saturate(70%) grayscale(1) contrast(99%) invert(12%);
}

audio::-webkit-media-controls-panel {
  background-color: #f8fafc;
}

.dark audio {
  filter: sepia(20%) saturate(70%) grayscale(1) contrast(99%) invert(88%);
}

.dark audio::-webkit-media-controls-panel {
  background-color: #1e293b;
}
</style>