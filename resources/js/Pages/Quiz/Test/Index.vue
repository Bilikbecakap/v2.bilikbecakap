<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
  quizzes: Object,
  type: String,
});

const selectedType = ref(props.type || "");
const showEmptyQuizModal = ref(false);
const selectedQuizTitle = ref("");

// Watch for type changes
watch(selectedType, (newType) => {
  router.get(
    route("quiz.attempt.index"),
    { type: newType },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
});

const clearFilter = () => {
  selectedType.value = "";
};

// 👇 TAMBAH: Check if quiz has questions
const checkQuizQuestions = (quiz) => {
  if (!quiz.total_questions || quiz.total_questions === 0) {
    selectedQuizTitle.value = quiz.title;
    showEmptyQuizModal.value = true;
    return false;
  }
  // Jika ada soal, redirect ke start page
  router.visit(route('quiz.attempt.start', quiz.id));
  return true;
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
  <Head title="Test Quiz" />

  <AdminLayout>
    <template #title>Test Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Pilih Quiz untuk Dikerjakan
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Pilih quiz yang ingin Anda kerjakan dari daftar di bawah
          </p>
        </div>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6">
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4"
      >
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Type Filter -->
          <div class="flex-1">
            <label
              for="type-filter"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Filter Tipe Quiz
            </label>
            <select
              id="type-filter"
              v-model="selectedType"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
            >
              <option value="">Semua Tipe</option>
              <option value="umum">Quiz Umum</option>
              <option value="modul">Quiz Modul</option>
            </select>
          </div>

          <!-- Clear Filter Button -->
          <div class="flex items-end">
            <button
              v-if="selectedType"
              @click="clearFilter"
              class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              Reset Filter
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quiz List -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
            Daftar Quiz Tersedia
          </h3>
          <span class="text-sm text-slate-500 dark:text-slate-400">
            {{ quizzes.total }} quiz tersedia
          </span>
        </div>
      </div>

      <!-- Quiz Cards -->
      <div class="p-6">
        <div v-if="quizzes.data.length === 0" class="text-center py-12">
          <svg
            class="w-16 h-16 text-slate-400 dark:text-slate-500 mx-auto mb-4"
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
          <p class="text-slate-500 dark:text-slate-400 text-sm mb-2">
            Tidak ada quiz tersedia
          </p>
          <p class="text-xs text-slate-400 dark:text-slate-500">
            Belum ada quiz aktif yang bisa dikerjakan saat ini
          </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="quiz in quizzes.data"
            :key="quiz.id"
            class="group bg-slate-50 dark:bg-slate-900/50 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-200 overflow-hidden"
          >
            <!-- Card Header with Gradient -->
            <div class="bg-gradient-to-r from-blue-500 to-cyan-500 p-6 text-white">
              <div class="flex items-start justify-between mb-3">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm',
                  ]"
                >
                  {{ getTypeText(quiz.type) }}
                </span>
                <svg
                  class="w-8 h-8 opacity-50"
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
              <h3 class="text-lg font-bold mb-2 line-clamp-2">
                {{ quiz.title }}
              </h3>
            </div>

            <!-- Card Body -->
            <div class="p-6">
              <!-- Description -->
              <p
                v-if="quiz.description"
                class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-2"
              >
                {{ quiz.description }}
              </p>

              <!-- Modul Info atau Quiz Umum -->
              <div
                v-if="quiz.type === 'modul' && quiz.modul_pembelajaran"
                class="flex items-center text-xs text-slate-500 dark:text-slate-400 mb-4 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
              >
                <svg
                  class="w-4 h-4 mr-2 text-blue-500"
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
                <span class="font-medium">📚 {{ quiz.modul_pembelajaran.title }}</span>
              </div>
              <div
                v-else-if="quiz.type === 'umum'"
                class="flex items-center text-xs text-slate-500 dark:text-slate-400 mb-4 p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg"
              >
                <svg
                  class="w-4 h-4 mr-2 text-purple-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <span class="font-medium">🌐 Quiz Umum</span>
              </div>

              <!-- Quiz Info -->
              <div class="space-y-2 mb-4">
                <div class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                  <svg
                    class="w-4 h-4 mr-2 text-slate-400"
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
                  <span>Durasi: {{ quiz.duration }} menit</span>
                </div>
                <div class="flex items-center text-sm">
                  <svg
                    class="w-4 h-4 mr-2"
                    :class="quiz.total_questions > 0 ? 'text-green-500' : 'text-red-500'"
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
                  <span :class="quiz.total_questions > 0 ? 'text-slate-600 dark:text-slate-400' : 'text-red-600 dark:text-red-400'">
                    {{ quiz.total_questions || 0 }} soal
                  </span>
                </div>
              </div>

              <!-- 👇 UBAH: Action Button dengan validasi -->
              <button
                @click="checkQuizQuestions(quiz)"
                :disabled="!quiz.total_questions || quiz.total_questions === 0"
                :class="[
                  'w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
                  quiz.total_questions > 0
                    ? 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white hover:shadow-lg group-hover:from-blue-600 group-hover:to-cyan-600'
                    : 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-500 cursor-not-allowed'
                ]"
              >
                <svg
                  class="w-4 h-4 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    v-if="quiz.total_questions > 0"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                {{ quiz.total_questions > 0 ? 'Mulai Quiz' : 'Soal Kosong' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div
        v-if="quizzes.data.length > 0"
        class="px-6 py-4 border-t border-slate-200 dark:border-slate-700"
      >
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-600 dark:text-slate-400">
            Menampilkan {{ quizzes.from }} - {{ quizzes.to }} dari {{ quizzes.total }} quiz
          </div>

          <div class="flex items-center gap-2">
            <!-- Previous Button -->
            <Link
              v-if="quizzes.prev_page_url"
              :href="quizzes.prev_page_url"
              class="px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              Previous
            </Link>

            <!-- Page Numbers -->
            <div class="hidden sm:flex items-center gap-1">
              <template v-for="(link, index) in quizzes.links" :key="index">
                <Link
                  v-if="link.url && index !== 0 && index !== quizzes.links.length - 1"
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
              v-if="quizzes.next_page_url"
              :href="quizzes.next_page_url"
              class="px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- 👇 TAMBAH: Empty Quiz Modal -->
    <div
      v-if="showEmptyQuizModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div
          class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="showEmptyQuizModal = false"
        ></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
          class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
        >
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/20 sm:mx-0 sm:h-10 sm:w-10"
            >
              <svg
                class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
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
                Quiz Belum Tersedia
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Quiz <strong class="text-slate-700 dark:text-slate-300">{{ selectedQuizTitle }}</strong> belum memiliki soal. Silakan hubungi admin untuk menambahkan soal terlebih dahulu.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              @click="showEmptyQuizModal = false"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
            >
              Mengerti
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>