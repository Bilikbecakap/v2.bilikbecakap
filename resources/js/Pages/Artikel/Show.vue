<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { usePermissions } from "@/composables/usePermissions";

const { can } = usePermissions();

const props = defineProps({
  artikel: Object,
});

// Computed
const formatDate = (date) => {
  return new Date(date).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getStatusBadge = (status) => {
  const badges = {
    draft: "bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200",
    pending: "bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200",
    published: "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200",
    archived: "bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200",
  };
  return badges[status] || badges["draft"];
};

const getStatusText = (status) => {
  const texts = {
    draft: "Draft",
    pending: "Menunggu Persetujuan",
    published: "Dipublikasikan",
    archived: "Diarsipkan",
  };
  return texts[status] || status;
};

const getThumbnailUrl = (thumbnail) => {
  return thumbnail ? `/storage/${thumbnail}` : "/images/default-article.jpg";
};

const getKeywordsArray = computed(() => {
  if (!props.artikel.meta_keywords) return [];
  return props.artikel.meta_keywords
    .split(",")
    .map((keyword) => keyword.trim())
    .filter(Boolean);
});

const availableLanguages = computed(() => {
  const languages = [];
  if (props.artikel.judul_indonesia && props.artikel.konten_indonesia) {
    languages.push({ code: "id", name: "Indonesia", flag: "🇮🇩" });
  }
  if (props.artikel.judul_melayu && props.artikel.konten_melayu) {
    languages.push({ code: "ms", name: "Melayu", flag: "🇲🇾" });
  }
  if (props.artikel.judul_english && props.artikel.konten_english) {
    languages.push({ code: "en", name: "English", flag: "🇺🇸" });
  }
  return languages;
});

const activeLanguage = ref(availableLanguages.value[0]?.code || "id");

const currentTitle = computed(() => {
  switch (activeLanguage.value) {
    case "id":
      return props.artikel.judul_indonesia;
    case "ms":
      return props.artikel.judul_melayu;
    case "en":
      return props.artikel.judul_english;
    default:
      return (
        props.artikel.judul_indonesia ||
        props.artikel.judul_melayu ||
        props.artikel.judul_english
      );
  }
});

const currentContent = computed(() => {
  switch (activeLanguage.value) {
    case "id":
      return props.artikel.konten_indonesia;
    case "ms":
      return props.artikel.konten_melayu;
    case "en":
      return props.artikel.konten_english;
    default:
      return (
        props.artikel.konten_indonesia ||
        props.artikel.konten_melayu ||
        props.artikel.konten_english
      );
  }
});
</script>

<template>
  <Head :title="`${currentTitle || 'Artikel'} - Detail`" />

  <AdminLayout>
    <template #title>Detail Artikel</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Detail Artikel
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Informasi lengkap artikel
          </p>
        </div>

        <div class="flex gap-3">
          <Link
            href="/admin/artikel"
            class="inline-flex items-center px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors duration-150"
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

          <Link
            v-if="can('edit artikel')"
            :href="route('artikel.edit', artikel.id)"
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
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
              />
            </svg>
            Edit
          </Link>
        </div>
      </div>
    </div>

    <!-- Article Info -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6"
    >
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
          Informasi Artikel
        </h3>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Thumbnail -->
          <div class="lg:col-span-1">
            <div
              class="aspect-video w-full rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600"
            >
              <img
                :src="getThumbnailUrl(artikel.gambar_thumbnail)"
                :alt="currentTitle"
                class="w-full h-full object-cover"
              />
            </div>
          </div>

          <!-- Details -->
          <div class="lg:col-span-2 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Status -->
              <div>
                <label
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Status</label
                >
                <span
                  :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                    getStatusBadge(artikel.status),
                  ]"
                >
                  {{ getStatusText(artikel.status) }}
                </span>
              </div>

              <!-- Featured -->
              <div>
                <label
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Featured</label
                >
                <span
                  v-if="artikel.is_featured"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200"
                >
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                    />
                  </svg>
                  Featured
                </span>
                <span v-else class="text-sm text-slate-500 dark:text-slate-400"
                  >Tidak</span
                >
              </div>

              <!-- Kategori -->
              <div>
                <label
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Kategori</label
                >
                <p class="text-sm text-slate-900 dark:text-slate-100">
                  {{ artikel.kategori?.nama_kategori || "-" }}
                </p>
              </div>

              <!-- Views -->
              <div>
                <label
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Views</label
                >
                <div class="flex items-center text-sm text-slate-900 dark:text-slate-100">
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
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                  {{ artikel.views_count || 0 }} views
                </div>
              </div>

              <!-- Dibuat -->
              <div>
                <label
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Dibuat</label
                >
                <p class="text-sm text-slate-900 dark:text-slate-100">
                  {{ formatDate(artikel.created_at) }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  oleh {{ artikel.creator?.name }}
                </p>
              </div>

              <!-- Diupdate -->
              <div>
                <label
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                  >Terakhir Diupdate</label
                >
                <p class="text-sm text-slate-900 dark:text-slate-100">
                  {{ formatDate(artikel.updated_at) }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  oleh {{ artikel.updater?.name }}
                </p>
              </div>
            </div>

            <!-- Slug -->
            <div>
              <label
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                >URL Slug</label
              >
              <code
                class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 rounded text-sm"
              >
                /artikel/{{ artikel.slug }}
              </code>
            </div>

            <!-- Meta Keywords -->
            <div v-if="getKeywordsArray.length > 0">
              <label
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                >Meta Keywords</label
              >
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="keyword in getKeywordsArray"
                  :key="keyword"
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300"
                >
                  {{ keyword }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Section -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
            Konten Artikel
          </h3>

          <!-- Language Switcher -->
          <div v-if="availableLanguages.length > 1" class="flex gap-2">
            <button
              v-for="lang in availableLanguages"
              :key="lang.code"
              @click="activeLanguage = lang.code"
              :class="[
                'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-150',
                activeLanguage === lang.code
                  ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300'
                  : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
              ]"
            >
              {{ lang.flag }} {{ lang.name }}
            </button>
          </div>
        </div>
      </div>

      <div class="p-6">
        <!-- Title -->
        <h1
          class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100 mb-6"
        >
          {{ currentTitle }}
        </h1>

        <!-- Content -->
        <div
          class="quill-content prose prose-slate dark:prose-invert max-w-none"
          v-html="currentContent"
        ></div>

        <!-- No Content Message -->
        <div v-if="!currentContent" class="text-center py-12">
          <svg
            class="w-12 h-12 text-slate-400 dark:text-slate-500 mx-auto mb-4"
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
          <p class="text-slate-500 dark:text-slate-400">
            Konten tidak tersedia dalam bahasa ini
          </p>
        </div>
      </div>
    </div>

    <!-- Language Summary -->
    <div
      v-if="availableLanguages.length > 0"
      class="mt-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
          Bahasa Tersedia
        </h3>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-if="artikel.judul_indonesia"
            class="p-4 border border-red-200 dark:border-red-800 rounded-lg"
          >
            <div class="flex items-center mb-2">
              <span class="text-lg mr-2">🇮🇩</span>
              <span class="font-medium text-slate-900 dark:text-slate-100"
                >Bahasa Indonesia</span
              >
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 font-medium truncate">
              {{ artikel.judul_indonesia }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
              {{ artikel.konten_indonesia ? "Konten tersedia" : "Konten kosong" }}
            </p>
          </div>

          <div
            v-if="artikel.judul_melayu"
            class="p-4 border border-green-200 dark:border-green-800 rounded-lg"
          >
            <div class="flex items-center mb-2">
              <span class="text-lg mr-2">🇲🇾</span>
              <span class="font-medium text-slate-900 dark:text-slate-100"
                >Bahasa Melayu</span
              >
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 font-medium truncate">
              {{ artikel.judul_melayu }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
              {{ artikel.konten_melayu ? "Konten tersedia" : "Konten kosong" }}
            </p>
          </div>

          <div
            v-if="artikel.judul_english"
            class="p-4 border border-blue-200 dark:border-blue-800 rounded-lg"
          >
            <div class="flex items-center mb-2">
              <span class="text-lg mr-2">🇺🇸</span>
              <span class="font-medium text-slate-900 dark:text-slate-100">English</span>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 font-medium truncate">
              {{ artikel.judul_english }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
              {{ artikel.konten_english ? "Konten tersedia" : "Konten kosong" }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style>
/* Custom styles for prose content */
.prose {
  color: inherit;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
  color: inherit;
}

.prose strong {
  color: inherit;
}

.prose code {
  color: inherit;
}

.prose blockquote {
  border-left-color: rgb(59 130 246);
}

.dark .prose blockquote {
  border-left-color: rgb(96 165 250);
}

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