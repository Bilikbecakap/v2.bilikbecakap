<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { usePermissions } from "@/composables/usePermissions";

const { can } = usePermissions();

const props = defineProps({
  komentars: Object,
  filters: Object,
});

// Ref
const filters = ref({
  status: props.filters.status || "",
});

const showDeleteModal = ref(false);
const selectedKomentar = ref(null);

// Methods
const applyFilters = () => {
  router.get(route("komentar.index"), filters.value, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filters.value.status = "";
  applyFilters();
};

const deleteKomentar = (komentar) => {
  selectedKomentar.value = komentar;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (selectedKomentar.value) {
    router.delete(route("komentar.destroy", selectedKomentar.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false;
        selectedKomentar.value = null;
      },
    });
  }
};

const approveKomentar = (id) => {
  router.patch(route("komentar.approve", id));
};

const rejectKomentar = (id) => {
  router.patch(route("komentar.reject", id));
};

// Computed
const getStatusBadge = (status) => {
  const badges = {
    pending: "bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200",
    approved: "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200",
    rejected: "bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200",
  };
  return badges[status] || badges.pending;
};

const getStatusText = (status) => {
  const texts = {
    pending: "Menunggu",
    approved: "Disetujui",
    rejected: "Ditolak",
  };
  return texts[status] || status;
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getCommentableTypeLabel = (type) => {
  if (type === "App\\Models\\ModulPembelajaran") return "Modul Pembelajaran";
  if (type === "App\\Models\\Artikel") return "Artikel";
  return "Lainnya";
};
</script>

<template>
  <Head title="Komentar Masuk" />

  <AdminLayout>
    <template #title>Komentar Masuk</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Manajemen Komentar
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Tinjau dan kelola komentar pengunjung
          </p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6"
    >
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
              Status
            </label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
            >
              <option value="">Semua Status</option>
              <option value="pending">Menunggu</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex gap-3 mt-4">
          <button
            @click="applyFilters"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-150"
          >
            Filter
          </button>
          <button
            @click="resetFilters"
            class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
          Daftar Komentar
        </h3>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
          <thead class="bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Pengirim
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Isi Komentar
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Untuk
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Tanggal
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
            <tr v-if="komentars.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <svg
                    class="w-12 h-12 text-slate-400 dark:text-slate-500 mb-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1"
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-2 2v-2z"
                    />
                  </svg>
                  <p class="text-slate-500 dark:text-slate-400 text-sm">
                    Belum ada komentar
                  </p>
                </div>
              </td>
            </tr>
            <tr
              v-for="komentar in komentars.data"
              :key="komentar.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150"
            >
              <!-- Pengirim -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-slate-900 dark:text-slate-100">
                  {{ komentar.nama }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                  {{ komentar.kontak || '-' }}
                </div>
              </td>

              <!-- Isi Komentar -->
              <td class="px-6 py-4 max-w-xs">
                <p class="text-sm text-slate-700 dark:text-slate-300 line-clamp-2">
                  {{ komentar.isi_komentar }}
                </p>
              </td>

              <!-- Untuk (Artikel/Modul) -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                <div>{{ getCommentableTypeLabel(komentar.commentable_type) }}</div>
                <div v-if="komentar.commentable" class="text-xs mt-1 text-slate-500 dark:text-slate-500">
                  {{ komentar.commentable.title || komentar.commentable.judul_indonesia || '-' }}
                </div>
              </td>

              <!-- Status -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getStatusBadge(komentar.status),
                  ]"
                >
                  {{ getStatusText(komentar.status) }}
                </span>
              </td>

              <!-- Tanggal -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                {{ formatDateTime(komentar.created_at) }}
              </td>

              <!-- Aksi -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <!-- Setujui -->
                  <button
                    v-if="komentar.status !== 'approved' && can('manage komentar')"
                    @click="approveKomentar(komentar.id)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 border border-green-200 dark:border-green-700 rounded-md hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors duration-150"
                    title="Setujui"
                  >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>

                  <!-- Tolak -->
                  <button
                    v-if="komentar.status !== 'rejected' && can('manage komentar')"
                    @click="rejectKomentar(komentar.id)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 border border-red-200 dark:border-red-700 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-150"
                    title="Tolak"
                  >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>

                  <!-- Hapus -->
                  <button
                    v-if="can('delete komentar')"
                    @click="deleteKomentar(komentar)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
                    title="Hapus"
                  >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="komentars.data && komentars.data.length > 0"
        class="mt-8 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 px-6 py-4"
      >
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-600 dark:text-slate-400">
            Menampilkan {{ komentars.from }} sampai {{ komentars.to }} dari
            {{ komentars.total }} komentar
          </div>

          <div class="flex items-center space-x-2">
            <Link
              v-if="komentars.prev_page_url"
              :href="komentars.prev_page_url"
              class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Previous
            </Link>

            <div class="hidden sm:flex items-center space-x-1">
              <template v-for="(link, index) in komentars.links" :key="index">
                <Link
                  v-if="link.url && !isNaN(link.label)"
                  :href="link.url"
                  :class="[
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                    link.active
                      ? 'bg-blue-600 text-white border border-blue-600'
                      : 'text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700',
                  ]"
                >
                  {{ link.label }}
                </Link>
                <span
                  v-else-if="link.label === '...'"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400"
                >
                  ...
                </span>
              </template>
            </div>

            <Link
              v-if="komentars.next_page_url"
              :href="komentars.next_page_url"
              class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              Next
              <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div
          class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"
          @click="showDeleteModal = false"
        ></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
          class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
        >
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10"
            >
              <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                Hapus Komentar
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Yakin hapus komentar dari <strong>{{ selectedKomentar?.nama }}</strong>? Tindakan ini tidak bisa dibatalkan.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              @click="confirmDelete"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
            >
              Hapus
            </button>
            <button
              @click="showDeleteModal = false"
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