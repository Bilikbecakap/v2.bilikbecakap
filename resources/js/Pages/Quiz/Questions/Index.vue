<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { usePermissions } from "@/composables/usePermissions";

const { can } = usePermissions();

const props = defineProps({
  quiz: Object,
  questions: Array,
});

const showDeleteModal = ref(false);
const selectedQuestion = ref(null);

const deleteQuestion = (question) => {
  selectedQuestion.value = question;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (selectedQuestion.value) {
    router.delete(
      route("quiz.questions.destroy", [props.quiz.id, selectedQuestion.value.id]),
      {
        onSuccess: () => {
          showDeleteModal.value = false;
          selectedQuestion.value = null;
        },
      }
    );
  }
};

const getCorrectOption = (options) => {
  return options.find((opt) => opt.is_correct);
};
</script>

<template>
  <Head :title="`Kelola Soal - ${quiz.title}`" />

  <AdminLayout>
    <template #title>Kelola Soal Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Kelola Soal Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ quiz.title }}
          </p>
        </div>

        <div class="flex gap-3">
          <Link
            v-if="can('create quiz')"
            :href="route('quiz.questions.create', quiz.id)"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-md transition-all duration-200"
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

    <!-- Quiz Info Card -->
    <div
      class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl shadow-sm mb-6 p-6 text-white"
    >
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold mb-2">{{ quiz.title }}</h3>
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
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              {{ quiz.duration }} menit
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
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              {{ questions.length }} soal
            </div>
          </div>
        </div>
        <div class="text-right">
          <span
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm"
          >
            {{ quiz.type === "modul" ? "Quiz Modul" : "Quiz Umum" }}
          </span>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
            Daftar Soal
          </h3>
          <span class="text-sm text-slate-500 dark:text-slate-400">
            Total: {{ questions.length }} soal
          </span>
        </div>
      </div>

      <!-- Questions List -->
      <div class="divide-y divide-slate-200 dark:divide-slate-700">
        <div v-if="questions.length === 0" class="px-6 py-12 text-center">
          <div class="flex flex-col items-center">
            <svg
              class="w-16 h-16 text-slate-400 dark:text-slate-500 mb-4"
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
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">
              Belum ada soal untuk quiz ini
            </p>
            <Link
              v-if="can('create quiz')"
              :href="route('quiz.questions.create', quiz.id)"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-150"
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
              Tambah Soal Pertama
            </Link>
          </div>
        </div>

        <div
          v-for="(question, index) in questions"
          :key="question.id"
          class="px-6 py-5 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150"
        >
          <div class="flex items-start gap-4">
            <!-- Question Number -->
            <div
              class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center"
            >
              <span class="text-sm font-bold text-blue-600 dark:text-blue-300">
                {{ index + 1 }}
              </span>
            </div>

            <!-- Question Content -->
            <div class="flex-1 min-w-0">
              <!-- Gunakan v-html untuk render HTML dari Quill Editor -->
              <div 
                class="text-base font-medium text-slate-900 dark:text-slate-100 mb-3 prose prose-sm dark:prose-invert max-w-none"
                v-html="question.question"
              ></div>

              <!-- Options -->
              <div class="space-y-2 mb-3">
                <div
                  v-for="(option, optIndex) in question.options"
                  :key="option.id"
                  :class="[
                    'flex items-start p-3 rounded-lg border text-sm',
                    option.is_correct
                      ? 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700'
                      : 'bg-slate-50 dark:bg-slate-900/50 border-slate-200 dark:border-slate-700',
                  ]"
                >
                  <span
                    :class="[
                      'flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium mr-3',
                      option.is_correct
                        ? 'bg-green-200 dark:bg-green-800 text-green-700 dark:text-green-200'
                        : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
                    ]"
                  >
                    {{ String.fromCharCode(65 + optIndex) }}
                  </span>
                  <span
                    :class="[
                      'flex-1',
                      option.is_correct
                        ? 'text-green-800 dark:text-green-200 font-medium'
                        : 'text-slate-700 dark:text-slate-300',
                    ]"
                  >
                    {{ option.option_text }}
                  </span>
                  <svg
                    v-if="option.is_correct"
                    class="flex-shrink-0 w-5 h-5 text-green-600 dark:text-green-400 ml-2"
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
              </div>

              <!-- Meta Info -->
              <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                <span>Urutan: {{ question.order }}</span>
                <span>•</span>
                <span>{{ question.options?.length || 0 }} pilihan</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex-shrink-0 flex items-center gap-2">

              <!-- Edit -->
              <Link
                v-if="can('edit quiz')"
                :href="route('quiz.questions.edit', [quiz.id, question.id])"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150"
              >
                <svg
                  class="w-3 h-3"
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
              </Link>

              <!-- Delete -->
              <button
                v-if="can('delete quiz')"
                @click="deleteQuestion(question)"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 border border-red-200 dark:border-red-700 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-150"
              >
                <svg
                  class="w-3 h-3"
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
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
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
                Hapus Soal
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Apakah Anda yakin ingin menghapus soal ini? Semua pilihan jawaban akan
                  ikut terhapus. Tindakan ini tidak dapat dibatalkan.
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
</style>