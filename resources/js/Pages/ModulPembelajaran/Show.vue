<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { usePermissions } from "@/composables/usePermissions";

const { can } = usePermissions();

const props = defineProps({
  modul: Object,
});

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

const deleteModul = () => {
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(route("modul-pembelajaran.destroy", props.modul.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
    },
  });
};

// Computed
const getStatusBadge = (status) => {
  const badges = {
    draft: "bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200",
    published: "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200",
    archived: "bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200",
  };
  return badges[status] || badges["draft"];
};

const getStatusText = (status) => {
  const texts = {
    draft: "Draft",
    published: "Dipublikasi",
    archived: "Diarsipkan",
  };
  return texts[status] || status;
};

const getThumbnailUrl = (thumbnail) => {
  return thumbnail ? `/storage/${thumbnail}` : "/images/default-modul.jpg";
};

const getPdfUrl = (pdfFile) => {
  return pdfFile ? `/storage/${pdfFile}` : null;
};

const getVideoEmbedId = (videoUrl) => {
  if (!videoUrl) return null;
  
  // Extract YouTube video ID from various URL formats
  const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i;
  const match = videoUrl.match(regExp);
  
  return match ? match[1] : null;
};

const getYouTubeEmbedUrl = computed(() => {
  const videoId = getVideoEmbedId(props.modul.video_embed);
  return videoId ? `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1&showinfo=0` : null;
});

const readingTime = computed(() => {
  if (!props.modul.content) return 0;
  
  const wordCount = props.modul.content.replace(/<[^>]*>/g, '').split(/\s+/).length;
  const readingSpeed = 10; // words per minute
  
  return Math.max(1, Math.ceil(wordCount / readingSpeed));
});

const pdfLoadError = ref(false);

</script>

<template>
  <Head :title="modul.title" />

  <AdminLayout>
    <template #title>{{ modul.title }}</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <Link
              :href="route('modul-pembelajaran.index')"
              class="inline-flex items-center text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors duration-150"
            >
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Kembali ke Daftar
            </Link>
            <span class="text-slate-400 dark:text-slate-500">/</span>
            <span class="text-slate-600 dark:text-slate-400 text-sm">Detail Modul</span>
          </div>
          
          <h1 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-white mb-2">
            {{ modul.title }}
          </h1>
          
          <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a.997.997 0 01-1.414 0l-7-7A1.997 1.997 0 013 12V7a4 4 0 014-4z" />
              </svg>
              {{ modul.category?.nama_kategori }}
            </span>
            
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              {{ modul.creator?.name }}
            </span>
            
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              {{ modul.view_count || 0 }} views
            </span>
            
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ readingTime }} menit baca
            </span>
            
            <span
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                getStatusBadge(modul.status),
              ]"
            >
              {{ getStatusText(modul.status) }}
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
          <Link
            v-if="can('edit modul pembelajaran')"
            :href="route('modul-pembelajaran.edit', modul.id)"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </Link>
          
          <button
            v-if="can('delete modul pembelajaran')"
            @click="deleteModul"
            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus
          </button>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Thumbnail -->
        <div v-if="modul.thumbnail" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
          <img
            :src="getThumbnailUrl(modul.thumbnail)"
            :alt="modul.title"
            class="w-full h-64 object-cover"
          />
        </div>

        <!-- Description -->
        <div v-if="modul.deskripsi" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Deskripsi</h3>
          </div>
          <div class="p-6">
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ modul.deskripsi }}</p>
          </div>
        </div>

        <!-- Video -->
        <div v-if="getYouTubeEmbedUrl" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Video Pembelajaran</h3>
          </div>
          <div class="p-6">
            <div class="relative w-full" style="padding-bottom: 56.25%; /* 16:9 aspect ratio */">
              <iframe
                :src="getYouTubeEmbedUrl"
                class="absolute top-0 left-0 w-full h-full rounded-lg"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                loading="lazy"
                title="Video Pembelajaran"
              ></iframe>
            </div>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
              Video URL: {{ modul.video_embed }}
            </p>
          </div>
        </div>

        <!-- Content -->
        <div v-if="modul.content" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Konten Pembelajaran</h3>
          </div>
          <div class="p-6">
            <div 
              class="quill-content prose prose-slate dark:prose-invert max-w-none"
              v-html="modul.content"
            ></div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Info Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Informasi Modul</h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Kategori</label>
              <p class="text-slate-900 dark:text-white">{{ modul.category?.nama_kategori || '-' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  getStatusBadge(modul.status),
                ]"
              >
                {{ getStatusText(modul.status) }}
              </span>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Dibuat oleh</label>
              <p class="text-slate-900 dark:text-white">{{ modul.creator?.name || '-' }}</p>
            </div>
            
            <div v-if="modul.updater">
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Diupdate oleh</label>
              <p class="text-slate-900 dark:text-white">{{ modul.updater?.name }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Tanggal Dibuat</label>
              <p class="text-slate-900 dark:text-white">{{ formatDate(modul.created_at) }}</p>
            </div>
            
            <div v-if="modul.tanggal_publish">
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Tanggal Publikasi</label>
              <p class="text-slate-900 dark:text-white">{{ formatDate(modul.tanggal_publish) }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Views</label>
              <p class="text-slate-900 dark:text-white font-semibold">{{ modul.view_count || 0 }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">URL Slug</label>
              <p class="text-slate-900 dark:text-white font-mono text-sm bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">{{ modul.slug }}</p>
            </div>
          </div>
        </div>

        <!-- Download PDF -->
        <div v-if="getPdfUrl(modul.pdf_file)" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Materi PDF</h3>
          </div>
          <div class="p-6 space-y-4">
            <!-- PDF Viewer: Lebih kompatibel dan efisien -->
            <div class="border border-slate-200 dark:border-slate-600 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-900">
              <embed
                :src="`${getPdfUrl(modul.pdf_file)}#toolbar=0&navpanes=0&scrollbar=1&view=FitH`"
                type="application/pdf"
                class="w-full h-96"
                :alt="`PDF: ${modul.title}`"
                @error="pdfLoadError = true"
              />
              <div v-if="pdfLoadError" class="p-4 text-center text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20">
                Gagal memuat PDF. 
                <a :href="getPdfUrl(modul.pdf_file)" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                  Klik di sini untuk membuka di tab baru
                </a>
              </div>
            </div>

            <!-- Download Button -->
            <a
              :href="getPdfUrl(modul.pdf_file)"
              target="_blank"
              download
              class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors duration-150"
            >
              <div class="flex items-center">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4 18h12V6l-4-4H4a2 2 0 00-2 2v12a2 2 0 002 2zM9 13a.75.75 0 01-.75-.75v-2.5a.75.75 0 011.5 0v2.5A.75.75 0 019 13zm2-3a.75.75 0 01.75.75v2.5a.75.75 0 01-1.5 0v-2.5A.75.75 0 0111 10z"/>
                </svg>
                <div>
                  <p class="text-sm font-medium text-red-700 dark:text-red-300">Download Materi PDF</p>
                  <p class="text-xs text-red-600 dark:text-red-400">File pembelajaran lengkap</p>
                </div>
              </div>
              <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Aksi Cepat</h3>
          </div>
          <div class="p-6 space-y-3">
            <Link
              v-if="can('edit modul pembelajaran')"
              :href="route('modul-pembelajaran.edit', modul.id)"
              class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-150"
            >
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit Modul
            </Link>
            
            <Link
              :href="route('modul-pembelajaran.index')"
              class="w-full flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
            >
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
              Lihat Semua Modul
            </Link>
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
                Hapus Modul Pembelajaran
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Apakah Anda yakin ingin menghapus modul
                  <strong>{{ modul.title }}</strong
                  >? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua file terkait.
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

<style>
/* ========== QUILL EDITOR CONTENT STYLES ========== */
.quill-content {
  line-height: 1.6;
  font-size: 16px;
}

/* Lists - Ordered and Unordered */
.quill-content ol,
.quill-content ul {
  padding-left: 1.5rem;
  margin: 1rem 0;
}

.quill-content ol {
  list-style: decimal;
}

.quill-content ul {
  list-style: disc;
}

.quill-content li {
  margin: 0.5rem 0;
  display: list-item;
}

/* Handle Quill's specific list structure */
.quill-content [data-list="ordered"] {
  list-style: decimal;
  display: list-item;
  margin-left: 1.5rem;
}

.quill-content [data-list="bullet"] {
  list-style: disc;
  display: list-item;
  margin-left: 1.5rem;
}

/* Hide Quill's UI elements that aren't needed for display */
.quill-content .ql-ui {
  display: none;
}

/* Blockquotes */
.quill-content blockquote {
  border-left: 4px solid #3b82f6;
  margin: 1.5rem 0;
  padding: 1rem 1.5rem;
  background-color: #f8fafc;
  font-style: italic;
  color: #64748b;
  border-radius: 0 8px 8px 0;
}

.dark .quill-content blockquote {
  background-color: #1e293b;
  color: #94a3b8;
  border-left-color: #60a5fa;
}

/* Code blocks */
.quill-content .ql-code-block-container,
.quill-content pre {
  background-color: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  margin: 1rem 0;
  overflow-x: auto;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 14px;
  line-height: 1.4;
}

.dark .quill-content .ql-code-block-container,
.dark .quill-content pre {
  background-color: #1e293b;
  border-color: #374151;
  color: #e2e8f0;
}

/* Inline code */
.quill-content code {
  background-color: #f1f5f9;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.875em;
  border: 1px solid #e2e8f0;
}

.dark .quill-content code {
  background-color: #374151;
  border-color: #4b5563;
  color: #e2e8f0;
}

/* Images */
.quill-content img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 1rem 0;
}

.quill-content img.img-left {
  float: left;
  margin: 0 1rem 1rem 0;
  clear: left;
}

.quill-content img.img-right {
  float: right;
  margin: 0 0 1rem 1rem;
  clear: right;
}

.quill-content img.img-center {
  display: block;
  margin: 1rem auto;
  float: none;
}

/* Links */
.quill-content a {
  color: #3b82f6;
  text-decoration: underline;
  text-decoration-color: #3b82f6;
  text-underline-offset: 2px;
}

.quill-content a:hover {
  color: #2563eb;
  text-decoration-color: #2563eb;
}

.dark .quill-content a {
  color: #60a5fa;
  text-decoration-color: #60a5fa;
}

.dark .quill-content a:hover {
  color: #93c5fd;
  text-decoration-color: #93c5fd;
}

/* Text formatting */
.quill-content strong {
  font-weight: 600;
}

.quill-content em {
  font-style: italic;
}

.quill-content u {
  text-decoration: underline;
}

.quill-content s {
  text-decoration: line-through;
}

/* Paragraphs */
.quill-content p {
  margin: 1rem 0;
  line-height: 1.7;
}

.quill-content p:first-child {
  margin-top: 0;
}

.quill-content p:last-child {
  margin-bottom: 0;
}

/* Headers */
.quill-content h1,
.quill-content h2,
.quill-content h3,
.quill-content h4,
.quill-content h5,
.quill-content h6 {
  font-weight: bold;
  margin: 1.5rem 0 1rem 0;
  line-height: 1.3;
  color: inherit;
}

.quill-content h1 {
  font-size: 2rem;
}

.quill-content h2 {
  font-size: 1.75rem;
}

.quill-content h3 {
  font-size: 1.5rem;
}

.quill-content h4 {
  font-size: 1.25rem;
}

.quill-content h5 {
  font-size: 1.125rem;
}

.quill-content h6 {
  font-size: 1rem;
}

/* Text alignment */
.quill-content .ql-align-center,
.quill-content [style*="text-align: center"] {
  text-align: center;
}

.quill-content .ql-align-right,
.quill-content [style*="text-align: right"] {
  text-align: right;
}

.quill-content .ql-align-justify,
.quill-content [style*="text-align: justify"] {
  text-align: justify;
}

/* Text indentation */
.quill-content .ql-indent-1 {
  padding-left: 3rem;
}

.quill-content .ql-indent-2 {
  padding-left: 6rem;
}

.quill-content .ql-indent-3 {
  padding-left: 9rem;
}

/* Clear floats */
.quill-content::after {
  content: "";
  display: table;
  clear: both;
}

/* Videos */
.quill-content video,
.quill-content iframe {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 1rem 0;
}

/* Tables (if any) */
.quill-content table {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.quill-content th,
.quill-content td {
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  text-align: left;
}

.quill-content th {
  background-color: #f8fafc;
  font-weight: 600;
}

.dark .quill-content table {
  border-color: #374151;
}

.dark .quill-content th,
.dark .quill-content td {
  border-color: #374151;
}

.dark .quill-content th {
  background-color: #374151;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .quill-content {
    font-size: 14px;
  }
  
  .quill-content img.img-left,
  .quill-content img.img-right {
    float: none;
    display: block;
    margin: 1rem auto;
    max-width: 100%;
  }
  
  .quill-content .ql-indent-1,
  .quill-content .ql-indent-2,
  .quill-content .ql-indent-3 {
    padding-left: 1.5rem;
  }
  
  .quill-content h1 {
    font-size: 1.75rem;
  }
  
  .quill-content h2 {
    font-size: 1.5rem;
  }
  
  .quill-content h3 {
    font-size: 1.25rem;
  }
}
</style>