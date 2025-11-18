<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { usePermissions } from "@/composables/usePermissions";
import { useTranslations } from "@/composables/useTranslations";

const { can } = usePermissions();
const { t } = useTranslations();

const props = defineProps({
  quizzes: Object,
  search: String,
  type: String,
  status: String,
  is_duel_enabled: String, // TAMBAH
  sort: String,
  direction: String,
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

// Refs untuk modal dan filter
const showDeleteModal = ref(false);
const selectedQuiz = ref(null);
const filters = ref({
  search: props.search || "",
  type: props.type || "",
  status: props.status || "",
  is_duel_enabled: props.is_duel_enabled || "", // TAMBAH
});

// Methods
const deleteQuiz = (quiz) => {
  selectedQuiz.value = quiz;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (selectedQuiz.value) {
    router.delete(route("quiz.destroy", selectedQuiz.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false;
        selectedQuiz.value = null;
      },
    });
  }
};

const applyFilters = () => {
  router.get(route("quiz.index"), filters.value, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filters.value = {
    search: "",
    type: "",
    status: "",
    is_duel_enabled: "", // TAMBAH
  };
  applyFilters();
};

const sortBy = (field) => {
  const direction = props.sort === field && props.direction === "asc" ? "desc" : "asc";
  router.get(
    route("quiz.index"),
    {
      ...filters.value,
      sort: field,
      direction: direction,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

// Computed
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

  <Head title="Quiz Management" />

  <AdminLayout>
    <template #title>Quiz Management</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Manajemen Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Kelola quiz dan soal-soal latihan sistem
          </p>
        </div>

        <div v-if="can('create quiz')" class="flex gap-3">
          <Link :href="route('quiz.create')"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-md transition-all duration-200">
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Buat Quiz
          </Link>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pencarian</label>
            <input v-model="filters.search" type="text" placeholder="Cari judul atau deskripsi quiz..."
              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              @keyup.enter="applyFilters" />
          </div>

          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipe Quiz</label>
            <select v-model="filters.type"
              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white">
              <option value="">Semua Tipe</option>
              <option value="umum">Quiz Umum</option>
              <option value="modul">Quiz Modul</option>
            </select>
          </div>

          <!-- TAMBAH: Duel Filter -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mode Duel</label>
            <select v-model="filters.is_duel_enabled"
              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white">
              <option value="">Semua Mode</option>
              <option value="1">Duel Aktif</option>
              <option value="0">Duel Tidak Aktif</option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
            <select v-model="filters.status"
              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white">
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="inactive">Tidak Aktif</option>
            </select>
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex gap-3 mt-4">
          <button @click="applyFilters"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-150">
            Filter
          </button>
          <button @click="resetFilters"
            class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
          Daftar Quiz
        </h3>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                Quiz
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                Tipe
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                Durasi
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                Jumlah Soal
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                Status
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800"
                @click="sortBy('created_at')">
                <div class="flex items-center">
                  Tanggal
                  <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                </div>
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
            <tr v-if="quizzes.data.length === 0">
              <td colspan="7" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <svg class="w-12 h-12 text-slate-400 dark:text-slate-500 mb-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="text-slate-500 dark:text-slate-400 text-sm">
                    Belum ada quiz
                  </p>
                </div>
              </td>
            </tr>
            <tr v-for="quiz in quizzes.data" :key="quiz.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
              <!-- Quiz Info -->
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100">
                    {{ quiz.title }}
                  </h3>
                  <p v-if="quiz.description" class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                    {{ quiz.description }}
                  </p>
                  <p v-if="quiz.modul_pembelajaran" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    📚 {{ quiz.modul_pembelajaran.title }}
                  </p>
                  <!-- TAMBAH: Duel Badge -->
                  <div v-if="quiz.is_duel_enabled" class="flex items-center gap-1 mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-700">
                      <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      Mode Duel
                    </span>
                  </div>
                </div>
              </td>

              <!-- Tipe -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  getTypeBadge(quiz.type),
                ]">
                  {{ getTypeText(quiz.type) }}
                </span>
              </td>

              <!-- Durasi -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                  <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ quiz.duration }} menit
                </div>
              </td>

              <!-- Jumlah Soal -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                  <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  {{ quiz.total_questions || 0 }} soal
                </div>
              </td>

              <!-- Status -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  getStatusBadge(quiz.status),
                ]">
                  {{ getStatusText(quiz.status) }}
                </span>
              </td>

              <!-- Tanggal -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                {{ formatDate(quiz.created_at) }}
              </td>

              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <!-- Manage Questions -->
                  <Link v-if="can('edit quiz')" :href="route('quiz.questions.index', quiz.id)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 border border-purple-200 dark:border-purple-700 rounded-md hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors duration-150"
                    title="Kelola Soal">
                  <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Soal
                  </Link>

                  <!-- View -->
                  <Link v-if="can('edit quiz')" :href="route('quiz.show', quiz.id)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                    title="Lihat Detail">
                  <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  </Link>

                  <!-- Edit -->
                  <Link v-if="can('edit quiz')" :href="route('quiz.edit', quiz.id)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150">
                  <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  </Link>

                  <!-- Delete -->
                  <button v-if="can('delete quiz')" @click="deleteQuiz(quiz)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 border border-red-200 dark:border-red-700 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-150">
                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination (sama seperti sebelumnya) -->
      <div v-if="quizzes.data && quizzes.data.length > 0"
        class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-600 dark:text-slate-400">
            Menampilkan {{ quizzes.from }} sampai {{ quizzes.to }} dari
            {{ quizzes.total }} data
          </div>

          <div class="flex items-center space-x-2">
            <!-- Previous Button -->
            <Link v-if="quizzes.prev_page_url" :href="quizzes.prev_page_url"
              class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Previous
            </Link>

            <!-- Page Numbers -->
            <div class="hidden sm:flex items-center space-x-1">
              <template v-for="(link, index) in quizzes.links" :key="index">
                <Link v-if="link.url && !isNaN(link.label)" :href="link.url" :class="[
                  'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                  link.active
                    ? 'bg-blue-600 text-white border border-blue-600'
                    : 'text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700',
                ]">
                {{ link.label }}
                </Link>
                <span v-else-if="link.label === '...'"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400">
                  ...
                </span>
              </template>
            </div>

            <!-- Next Button -->
            <Link v-if="quizzes.next_page_url" :href="quizzes.next_page_url"
              class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
            Next
            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal (sama seperti sebelumnya) -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
      aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true"
          @click="showDeleteModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
          class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100" id="modal-title">
                Hapus Quiz
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Apakah Anda yakin ingin menghapus quiz
                  <strong>{{ selectedQuiz?.title }}</strong>? Semua soal dan data terkait akan ikut terhapus. Tindakan
                  ini
                  tidak dapat dibatalkan.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button @click="confirmDelete" type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150">
              Hapus
            </button>
            <button @click="showDeleteModal = false" type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-150">
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>