<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { usePermissions } from "@/composables/usePermissions";
import QuillEditor from "@/Components/QuillEditor.vue";

const { can } = usePermissions();

const props = defineProps({
  kategoriList: Array,
});

// Form state
const form = useForm({
  judul_indonesia: "",
  judul_melayu: "",
  judul_english: "",
  konten_indonesia: "",
  konten_melayu: "",
  konten_english: "",
  slug: "",
  kategori_id: "",
  gambar_thumbnail: null,
  meta_keywords: "",
  status: "draft",
  is_featured: false,
  tanggal_publish: null,
});

// UI state
const activeTab = ref("indonesia");
const thumbnailPreview = ref(null);
const isTranslating = ref(false);

// Notification state
const notification = ref({
  show: false,
  type: "success", // success, error, warning, info
  title: "",
  message: "",
  timeout: null,
});

// Methods
const generateSlug = (title) => {
  const slug = title
    .toLowerCase()
    .replace(/[^\w\s-]/g, "")
    .replace(/\s+/g, "-")
    .replace(/--+/g, "-")
    .trim("-");
  form.slug = slug;
};

const handleThumbnailChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.gambar_thumbnail = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      thumbnailPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeThumbnail = () => {
  form.gambar_thumbnail = null;
  thumbnailPreview.value = null;
  // Reset input file
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) fileInput.value = '';
};

// Notification functions
const showNotification = (type, title, message, duration = 5000) => {
  if (notification.value.timeout) {
    clearTimeout(notification.value.timeout);
  }

  notification.value = {
    show: true,
    type,
    title,
    message,
    timeout: null,
  };

  notification.value.timeout = setTimeout(() => {
    hideNotification();
  }, duration);
};

const hideNotification = () => {
  notification.value.show = false;
};

// Auto translate to Melayu
const autoTranslateToMelayu = async () => {
  if (!form.konten_indonesia.trim()) {
    showNotification(
      "warning",
      "Peringatan",
      "Konten Indonesia harus diisi terlebih dahulu",
      3000
    );
    return;
  }

  isTranslating.value = true;

  try {
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = form.konten_indonesia;
    const plainText = tempDiv.textContent || tempDiv.innerText || "";

    const response = await fetch(route("translate.process"), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
      },
      body: JSON.stringify({
        text: plainText,
        direction: "indonesia_to_belitung",
        method: "hybrid", // Ubah ke hybrid
      }),
    });

    const data = await response.json();

    if (data.success) {
      form.konten_melayu = `<p>${data.data.translation}</p>`;

      if (!form.judul_melayu && form.judul_indonesia) {
        const judulResponse = await fetch(route("translate.process"), {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN":
              document.querySelector('meta[name="csrf-token"]')?.content || "",
          },
          body: JSON.stringify({
            text: form.judul_indonesia,
            direction: "indonesia_to_belitung",
            method: "hybrid",
          }),
        });

        const judulData = await judulResponse.json();
        if (judulData.success) {
          form.judul_melayu = judulData.data.translation;
        }
      }

      showNotification(
        "success",
        "🇲🇾 Berhasil Diterjemahkan ke Bahasa Melayu!",
        `Judul: ${
          form.judul_melayu ? "✅ Diterjemahkan" : "➖ Tidak ada"
        }\nKonten: ✅ Berhasil diterjemahkan\n\n⚠️ Hasil AI mungkin tidak 100% akurat, silakan periksa kembali.`,
        8000
      );
    } else {
      showNotification("error", "Gagal Menerjemahkan", data.message, 5000);
    }
  } catch (error) {
    showNotification("error", "Error", "Terjadi kesalahan saat menerjemahkan", 5000);
  } finally {
    isTranslating.value = false;
  }
};

// Auto translate to English
const autoTranslateToEnglish = async () => {
  if (!form.konten_indonesia.trim()) {
    showNotification(
      "warning",
      "Peringatan",
      "Konten Indonesia harus diisi terlebih dahulu",
      3000
    );
    return;
  }

  isTranslating.value = true;

  try {
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = form.konten_indonesia;
    const plainText = tempDiv.textContent || tempDiv.innerText || "";

    const response = await fetch(route("translate.to-english"), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
      },
      body: JSON.stringify({
        text: plainText,
      }),
    });

    const data = await response.json();

    if (data.success) {
      form.konten_english = `<p>${data.data.translation}</p>`;

      if (!form.judul_english && form.judul_indonesia) {
        const judulResponse = await fetch(route("translate.to-english"), {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN":
              document.querySelector('meta[name="csrf-token"]')?.content || "",
          },
          body: JSON.stringify({
            text: form.judul_indonesia,
          }),
        });

        const judulData = await judulResponse.json();
        if (judulData.success) {
          form.judul_english = judulData.data.translation;
        }
      }

      showNotification(
        "success",
        "🇺🇸 Berhasil Diterjemahkan ke Bahasa Inggris!",
        `Judul: ${
          form.judul_english ? "✅ Diterjemahkan" : "➖ Tidak ada"
        }\nKonten: ✅ Berhasil diterjemahkan\n\n💡 Periksa grammar dan konteks sebelum publikasi.`,
        8000
      );
    } else {
      showNotification("error", "Gagal Menerjemahkan", data.message, 5000);
    }
  } catch (error) {
    showNotification("error", "Error", "Terjadi kesalahan saat menerjemahkan", 5000);
  } finally {
    isTranslating.value = false;
  }
};

const submit = () => {
  form.post(route("artikel.store"));
};

// Watch for title changes to generate slug
watch(
  () => form.judul_indonesia,
  (newVal) => {
    if (newVal) generateSlug(newVal);
  }
);
</script>

<template>
  <Head title="Buat Artikel" />

  <AdminLayout>
    <template #title>Buat Artikel</template>

    <!-- Header Section - Updated Styling -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Buat Artikel Baru
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Buat artikel dengan dukungan multi-bahasa dan AI translation
          </p>
        </div>
        <Link
          href="/admin/artikel"
          class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali
        </Link>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Basic Info - Updated Styling -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Informasi Dasar</h3>
        </div>
        
        <div class="p-6">
          <div class="grid gap-4">
            <!-- Kategori -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kategori *</label>
              <select
                v-model="form.kategori_id"
                required
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.kategori_id }"
              >
                <option value="">Pilih Kategori</option>
                <option
                  v-for="kategori in kategoriList"
                  :key="kategori.id"
                  :value="kategori.id"
                >
                  {{ kategori.nama_kategori }}
                </option>
              </select>
              <p v-if="form.errors?.kategori_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.kategori_id }}
              </p>
            </div>

            <!-- Slug -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">URL Slug</label>
              <input
                v-model="form.slug"
                type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="artikel-url-slug-otomatis"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.slug }"
              />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                URL slug akan dibuat otomatis dari judul Indonesia
              </p>
              <p v-if="form.errors?.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.slug }}
              </p>
            </div>

            <!-- Keywords -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Meta Keywords</label>
              <input
                v-model="form.meta_keywords"
                type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="budaya belitung, wisata belitung, bahasa melayu, tradisi lokal, kuliner khas"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.meta_keywords }"
              />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Pisahkan dengan koma. Contoh: budaya belitung, wisata pantai, kuliner tradisional
              </p>
              <p v-if="form.errors?.meta_keywords" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.meta_keywords }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Thumbnail - Updated Styling -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Gambar Thumbnail</h3>
        </div>

        <div class="p-6">
          <div class="flex gap-4">
            <div class="flex-1">
              <input
                @change="handleThumbnailChange"
                type="file"
                accept="image/*"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.gambar_thumbnail }"
              />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Format: JPEG, PNG, JPG, GIF, WEBP. Maksimal 2MB
              </p>
              <p v-if="form.errors?.gambar_thumbnail" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.gambar_thumbnail }}
              </p>
            </div>

            <div v-if="thumbnailPreview" class="relative">
              <img :src="thumbnailPreview" class="w-20 h-20 object-cover rounded-lg border border-slate-200 dark:border-slate-600" />
              <button
                @click="removeThumbnail"
                type="button"
                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-150"
              >
                ×
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content - Updated Styling -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Konten Multi-Bahasa</h3>
        </div>

        <!-- Tabs -->
        <div class="border-b border-slate-200 dark:border-slate-700">
          <nav class="flex px-6">
            <button
              @click="activeTab = 'indonesia'"
              type="button"
              :class="[
                'py-4 px-4 border-b-2 font-medium text-sm transition-colors duration-150',
                activeTab === 'indonesia'
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
              ]"
            >
              🇮🇩 Indonesia
            </button>
            <button
              @click="activeTab = 'melayu'"
              type="button"
              :class="[
                'py-4 px-4 border-b-2 font-medium text-sm transition-colors duration-150',
                activeTab === 'melayu'
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
              ]"
            >
              🇲🇾 Melayu
            </button>
            <button
              @click="activeTab = 'english'"
              type="button"
              :class="[
                'py-4 px-4 border-b-2 font-medium text-sm transition-colors duration-150',
                activeTab === 'english'
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
              ]"
            >
              🇺🇸 English
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Indonesia -->
          <div v-show="activeTab === 'indonesia'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Judul Indonesia</label>
              <input
                v-model="form.judul_indonesia"
                type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Masukkan judul dalam bahasa Indonesia"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.judul_indonesia }"
              />
              <p v-if="form.errors?.judul_indonesia" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.judul_indonesia }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Konten Indonesia</label>
              <QuillEditor
                v-model="form.konten_indonesia"
                placeholder="Tulis konten dalam bahasa Indonesia..."
              />
              <p v-if="form.errors?.konten_indonesia" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.konten_indonesia }}
              </p>
            </div>
          </div>

          <!-- Melayu -->
          <div v-show="activeTab === 'melayu'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Judul Melayu</label>
              <input
                v-model="form.judul_melayu"
                type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Masukkan judul dalam bahasa Melayu"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.judul_melayu }"
              />
              <p v-if="form.errors?.judul_melayu" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.judul_melayu }}
              </p>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Konten Melayu</label>
                <button
                  @click="autoTranslateToMelayu"
                  :disabled="isTranslating || !form.konten_indonesia.trim()"
                  type="button"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800"
                >
                  <svg
                    v-if="isTranslating"
                    class="animate-spin w-3 h-3 mr-1.5"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  <svg
                    v-else
                    class="w-3 h-3 mr-1.5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V8m10 0H7"
                    />
                  </svg>
                  {{ isTranslating ? "Menerjemahkan..." : "🤖 AI Translate" }}
                </button>
              </div>
              <QuillEditor
                v-model="form.konten_melayu"
                placeholder="Tulis konten dalam bahasa Melayu..."
              />
              <p v-if="form.errors?.konten_melayu" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.konten_melayu }}
              </p>
            </div>
          </div>

          <!-- English -->
          <div v-show="activeTab === 'english'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">English Title</label>
              <input
                v-model="form.judul_english"
                type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Enter title in English"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.judul_english }"
              />
              <p v-if="form.errors?.judul_english" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.judul_english }}
              </p>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">English Content</label>
                <button
                  @click="autoTranslateToEnglish"
                  :disabled="isTranslating || !form.konten_indonesia.trim()"
                  type="button"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-green-900/20 dark:text-green-400 dark:border-green-800"
                >
                  <svg
                    v-if="isTranslating"
                    class="animate-spin w-3 h-3 mr-1.5"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  <svg
                    v-else
                    class="w-3 h-3 mr-1.5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V8m10 0H7"
                    />
                  </svg>
                  {{ isTranslating ? "Translating..." : "🤖 AI Translate" }}
                </button>
              </div>
              <QuillEditor
                v-model="form.konten_english"
                placeholder="Write content in English..."
              />
              <p v-if="form.errors?.konten_english" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.konten_english }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Settings - Updated Styling -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Pengaturan</h3>
        </div>

        <div class="p-6">
          <div class="space-y-4">
            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
              <div class="space-y-2">
                <label class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="draft" type="radio" class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <span class="text-slate-900 dark:text-white">Draft</span>
                </label>
                <label class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="pending" type="radio" class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <span class="text-slate-900 dark:text-white">Kirim untuk Review</span>
                </label>
                <label v-if="can('approve artikel')" class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input
                    v-model="form.status"
                    value="published"
                    type="radio"
                    class="mr-3 text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-slate-900 dark:text-white">Publikasikan Langsung</span>
                </label>
              </div>
            </div>

            <!-- Featured -->
            <div>
              <label class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                <input v-model="form.is_featured" type="checkbox" class="mr-3 text-blue-600 focus:ring-blue-500 rounded" />
                <span class="text-slate-900 dark:text-white">Artikel Unggulan</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-4">
        <Link
          href="/admin/artikel"
          class="px-6 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
        >
          Batal
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg hover:shadow-md transition-all duration-200 disabled:opacity-50"
        >
          <span v-if="form.processing" class="flex items-center">
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
          </span>
          <span v-else>Simpan Artikel</span>
        </button>
      </div>
    </form>

    <!-- Custom Notification -->
    <Teleport to="body">
      <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="notification.show"
          class="fixed top-4 right-4 z-50 w-full max-w-sm bg-white dark:bg-slate-800 shadow-lg rounded-lg border border-slate-200 dark:border-slate-700"
        >
          <div class="p-4">
            <div class="flex items-start">
              <!-- Icon -->
              <div class="flex-shrink-0">
                <svg
                  v-if="notification.type === 'success'"
                  class="h-6 w-6 text-green-500"
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
                <svg
                  v-else-if="notification.type === 'error'"
                  class="h-6 w-6 text-red-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <svg
                  v-else-if="notification.type === 'warning'"
                  class="h-6 w-6 text-yellow-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.382 16.5c-.77.833.192 2.5 1.732 2.5z"
                  />
                </svg>
                <svg
                  v-else
                  class="h-6 w-6 text-blue-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>

              <!-- Content -->
              <div class="ml-3 w-0 flex-1">
                <p class="text-sm font-medium text-slate-900 dark:text-white">
                  {{ notification.title }}
                </p>
                <p
                  class="mt-1 text-sm text-slate-500 dark:text-slate-400 whitespace-pre-line"
                >
                  {{ notification.message }}
                </p>
              </div>

              <!-- Close button -->
              <div class="ml-4 flex-shrink-0 flex">
                <button
                  @click="hideNotification"
                  class="bg-white dark:bg-slate-800 rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="sr-only">Close</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                      fill-rule="evenodd"
                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>