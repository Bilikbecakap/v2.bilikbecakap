<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted } from "vue";
import { usePermissions } from "@/composables/usePermissions";
import QuillEditor from "@/Components/QuillEditor.vue";

const { can } = usePermissions();

const props = defineProps({
  artikel: Object,
  kategoriList: Array,
  userList: Array,
});

// Form state - initialize with existing article data
const form = useForm({
  judul_indonesia: props.artikel?.judul_indonesia ?? "",
  judul_melayu: props.artikel?.judul_melayu ?? "",
  judul_english: props.artikel?.judul_english ?? "",
  konten_indonesia: props.artikel.konten_indonesia || "",
  konten_melayu: props.artikel.konten_melayu || "",
  konten_english: props.artikel.konten_english || "",
  slug: props.artikel.slug || "",
  kategori_id: props.artikel.kategori_id || null,
  gambar_thumbnail: null, // File input - always start null
  meta_keywords: props.artikel.meta_keywords || "",
  status: props.artikel.status || "draft",
  is_featured: props.artikel.is_featured || false,
  tanggal_publish: props.artikel.tanggal_publish || null,
  created_by: props.artikel?.created_by ?? null,
});

// UI state
const activeTab = ref("indonesia");
const thumbnailPreview = ref(null);
const existingThumbnail = ref(props.artikel.gambar_thumbnail || null);
const removeThumbnailFlag = ref(false);
const isTranslating = ref(false);
const hasApprovalPermission = computed(() => can("approve artikel"));
const canEdit = computed(() => props.artikel.can_edit || hasApprovalPermission.value);

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
    removeThumbnailFlag.value = false;
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
  removeThumbnailFlag.value = true;
  // Clear file input
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) fileInput.value = "";
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
        method: "hybrid",
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
        `Judul: ${form.judul_melayu ? "✅ Diterjemahkan" : "➖ Tidak ada"
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
        `Judul: ${form.judul_english ? "✅ Diterjemahkan" : "➖ Tidak ada"
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

const submit = async () => {
  try {
    form.processing = true;
    form.clearErrors();

    const formData = new FormData();

    // Add all text fields
    formData.append('judul_indonesia', form.judul_indonesia || '');
    formData.append('judul_melayu', form.judul_melayu || '');
    formData.append('judul_english', form.judul_english || '');
    formData.append('konten_indonesia', form.konten_indonesia || '');
    formData.append('konten_melayu', form.konten_melayu || '');
    formData.append('konten_english', form.konten_english || '');
    formData.append('slug', form.slug || '');
    formData.append('kategori_id', form.kategori_id || '');
    formData.append('meta_keywords', form.meta_keywords || '');
    formData.append('status', form.status || '');
    formData.append('is_featured', form.is_featured ? '1' : '0');
    formData.append('tanggal_publish', form.tanggal_publish || '');
    formData.append('created_by', form.created_by || '');
    formData.append('_method', 'PUT');

    // ADD INI: Kirim flag remove thumbnail
    if (removeThumbnailFlag.value) {
      formData.append('remove_thumbnail', '1');
    }

    // Add file if exists
    if (form.gambar_thumbnail) {
      formData.append('gambar_thumbnail', form.gambar_thumbnail);
    }

    // Send with fetch
    const response = await fetch(route("admin.artikel.update", props.artikel.id), {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (response.ok) {
      window.location.href = '/admin/artikel';
    } else {
      const errorData = await response.json();
      if (errorData.errors) {
        Object.assign(form.errors, errorData.errors);
      }
    }

  } catch (error) {
    console.error('Error:', error);
  } finally {
    form.processing = false;
  }
};

// Watch for title changes to generate slug (only if slug is empty or auto-generated)
watch(
  () => form.judul_indonesia,
  (newVal) => {
    if (newVal && (!form.slug || form.slug === props.artikel.slug)) {
      generateSlug(newVal);
    }
  }
);

// Initialize thumbnail preview if existing thumbnail
onMounted(() => {
  // Pastikan form values ter-sync dengan data yang ada
  if (props.artikel.judul_indonesia) {
    form.judul_indonesia = props.artikel.judul_indonesia;
  }
  if (props.artikel.judul_melayu) {
    form.judul_melayu = props.artikel.judul_melayu;
  }
  if (props.artikel.judul_english) {
    form.judul_english = props.artikel.judul_english;
  }

  if (props.artikel.tanggal_publish) {
    form.tanggal_publish = props.artikel.tanggal_publish.slice(0, 16);
  }

  // Thumbnail preview
  if (existingThumbnail.value) {
    thumbnailPreview.value = `/storage/${existingThumbnail.value}`;
  }
});
</script>

<template>

  <Head :title="`Edit Artikel: ${artikel.judul_indonesia ||
    artikel.judul_melayu ||
    artikel.judul_english ||
    'Untitled'
    }`" />

  <AdminLayout>
    <template #title>Edit Artikel</template>

    <!-- Header Section - Updated Styling -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Edit Artikel
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Edit artikel dengan dukungan multi-bahasa dan AI translation
          </p>
          <!-- Status Badge -->
          <div class="mt-2 flex items-center gap-2">
            <span :class="{
              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': artikel.status === 'draft',
              'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400': artikel.status === 'pending',
              'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': artikel.status === 'published',
              'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400': artikel.status === 'archived',
            }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
              {{ artikel.status.charAt(0).toUpperCase() + artikel.status.slice(1) }}
            </span>
            <span v-if="artikel.is_featured"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400">
              ⭐ Featured
            </span>
          </div>
        </div>
        <Link href="/admin/artikel"
          class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali
        </Link>
      </div>
    </div>

    <!-- Permission Check -->
    <div v-if="!canEdit"
      class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.382 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <p class="font-medium">Anda tidak memiliki izin untuk mengedit artikel ini.</p>
      </div>
    </div>

    <form v-else @submit.prevent="submit" class="space-y-6">
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
              <select v-model="form.kategori_id" required
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.kategori_id }">
                <option :value="null">Pilih Kategori</option>
                <option v-for="kategori in kategoriList" :key="kategori.id" :value="kategori.id">
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
              <input v-model="form.slug" type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="artikel-url-slug-otomatis"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.slug }" />
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
              <input v-model="form.meta_keywords" type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="budaya belitung, wisata belitung, bahasa melayu, tradisi lokal, kuliner khas"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.meta_keywords }" />
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
          <div class="space-y-4">
            <!-- File Input -->
            <div>
              <input @change="handleThumbnailChange" type="file" accept="image/*"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.gambar_thumbnail }" />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Format: JPEG, PNG, JPG, GIF, WEBP. Maksimal 2MB
              </p>
              <p v-if="form.errors?.gambar_thumbnail" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.gambar_thumbnail }}
              </p>
            </div>

            <!-- Thumbnail Preview -->
            <div v-if="thumbnailPreview || existingThumbnail" class="flex gap-4 items-start">
              <div class="relative">
                <img :src="thumbnailPreview || `/storage/${existingThumbnail}`"
                  class="w-32 h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-600"
                  alt="Thumbnail preview" />
                <button @click="removeThumbnail" type="button"
                  class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-150"
                  title="Hapus thumbnail">
                  ×
                </button>
              </div>
              <div class="text-sm text-slate-600 dark:text-slate-400">
                <p v-if="form.gambar_thumbnail" class="font-medium text-blue-600 dark:text-blue-400">
                  📄 Thumbnail baru akan mengganti yang lama
                </p>
                <p v-else-if="existingThumbnail && !removeThumbnailFlag"
                  class="font-medium text-green-600 dark:text-green-400">
                  ✅ Thumbnail saat ini
                </p>
                <p v-else-if="removeThumbnailFlag" class="font-medium text-red-600 dark:text-red-400">
                  🗑️ Thumbnail akan dihapus
                </p>
              </div>
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
            <button @click="activeTab = 'indonesia'" type="button" :class="[
              'py-4 px-4 border-b-2 font-medium text-sm transition-colors duration-150',
              activeTab === 'indonesia'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
            ]">
              🇮🇩 Indonesia
            </button>
            <button @click="activeTab = 'melayu'" type="button" :class="[
              'py-4 px-4 border-b-2 font-medium text-sm transition-colors duration-150',
              activeTab === 'melayu'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
            ]">
              🇲🇾 Melayu
            </button>
            <button @click="activeTab = 'english'" type="button" :class="[
              'py-4 px-4 border-b-2 font-medium text-sm transition-colors duration-150',
              activeTab === 'english'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
            ]">
              🇺🇸 English
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Indonesia -->
          <div v-show="activeTab === 'indonesia'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Judul Indonesia</label>
              <input v-model="form.judul_indonesia" type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Masukkan judul dalam bahasa Indonesia"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.judul_indonesia }" />
              <p v-if="form.errors?.judul_indonesia" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.judul_indonesia }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Konten Indonesia</label>
              <QuillEditor v-model="form.konten_indonesia" placeholder="Tulis konten dalam bahasa Indonesia..." />
              <p v-if="form.errors?.konten_indonesia" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.konten_indonesia }}
              </p>
            </div>
          </div>

          <!-- Melayu -->
          <div v-show="activeTab === 'melayu'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Judul Melayu</label>
              <input v-model="form.judul_melayu" type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Masukkan judul dalam bahasa Melayu"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.judul_melayu }" />
              <p v-if="form.errors?.judul_melayu" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.judul_melayu }}
              </p>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Konten Melayu</label>
                <button @click="autoTranslateToMelayu" :disabled="isTranslating || !form.konten_indonesia.trim()"
                  type="button"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800">
                  <svg v-if="isTranslating" class="animate-spin w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg v-else class="w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V8m10 0H7" />
                  </svg>
                  {{ isTranslating ? "Menerjemahkan..." : "🤖 AI Translate" }}
                </button>
              </div>
              <QuillEditor v-model="form.konten_melayu" placeholder="Tulis konten dalam bahasa Melayu..." />
              <p v-if="form.errors?.konten_melayu" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.konten_melayu }}
              </p>
            </div>
          </div>

          <!-- English -->
          <div v-show="activeTab === 'english'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">English Title</label>
              <input v-model="form.judul_english" type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Enter title in English"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.judul_english }" />
              <p v-if="form.errors?.judul_english" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.judul_english }}
              </p>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">English Content</label>
                <button @click="autoTranslateToEnglish" :disabled="isTranslating || !form.konten_indonesia.trim()"
                  type="button"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-green-900/20 dark:text-green-400 dark:border-green-800">
                  <svg v-if="isTranslating" class="animate-spin w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg v-else class="w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V8m10 0H7" />
                  </svg>
                  {{ isTranslating ? "Translating..." : "🤖 AI Translate" }}
                </button>
              </div>
              <QuillEditor v-model="form.konten_english" placeholder="Write content in English..." />
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

            <div v-if="can('super-admin')" class="mb-4">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pembuat Artikel</label>
              <select v-model="form.created_by"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                <option v-for="user in userList" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
              <div class="space-y-2">
                <label
                  class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="draft" type="radio"
                    class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <span class="text-slate-900 dark:text-white">Draft</span>
                </label>
                <label
                  class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="pending" type="radio"
                    class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <span class="text-slate-900 dark:text-white">Kirim untuk Review</span>
                </label>
                <label v-if="hasApprovalPermission"
                  class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="published" type="radio"
                    class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <span class="text-slate-900 dark:text-white">Publikasikan Langsung</span>
                </label>
                <label v-if="hasApprovalPermission"
                  class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="archived" type="radio"
                    class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <span class="text-slate-900 dark:text-white">Arsipkan</span>
                </label>
              </div>

              <!-- Status change warning -->
              <div v-if="!hasApprovalPermission && artikel.status === 'published'"
                class="mt-3 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg text-sm dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-400">
                <div class="flex items-start">
                  <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.382 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                  <div>
                    <p class="font-medium">Perhatian:</p>
                    <p class="mt-1">Karena artikel ini sudah dipublikasikan, perubahan yang Anda buat akan mengubah
                      status
                      menjadi "Pending" dan memerlukan persetujuan admin kembali.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Featured -->
            <div>
              <label
                class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                <input v-model="form.is_featured" type="checkbox"
                  class="mr-3 text-blue-600 focus:ring-blue-500 rounded" />
                <span class="text-slate-900 dark:text-white">Artikel Unggulan</span>
              </label>
            </div>

            <!-- Publish Date (for published articles) -->
            <div v-if="form.status === 'published' || artikel.tanggal_publish">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal Publikasi</label>
              <input v-model="form.tanggal_publish" type="datetime-local"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white" />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Kosongkan untuk menggunakan waktu saat ini
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-4">
        <Link href="/admin/artikel"
          class="px-6 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
        Batal
        </Link>
        <button type="submit" :disabled="form.processing"
          class="px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg hover:shadow-md transition-all duration-200 disabled:opacity-50">
          <span v-if="form.processing" class="flex items-center">
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            Menyimpan...
          </span>
          <span v-else>Update Artikel</span>
        </button>
      </div>

      <!-- Error Messages -->
      <div v-if="form.errors.error"
        class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ form.errors.error }}
        </div>
      </div>
    </form>

    <!-- Custom Notification -->
    <Teleport to="body">
      <Transition enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="notification.show"
          class="fixed top-4 right-4 z-50 w-full max-w-sm bg-white dark:bg-slate-800 shadow-lg rounded-lg border border-slate-200 dark:border-slate-700">
          <div class="p-4">
            <div class="flex items-start">
              <!-- Icon -->
              <div class="flex-shrink-0">
                <svg v-if="notification.type === 'success'" class="h-6 w-6 text-green-500" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="notification.type === 'error'" class="h-6 w-6 text-red-500" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="notification.type === 'warning'" class="h-6 w-6 text-yellow-500" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.382 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <svg v-else class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>

              <!-- Content -->
              <div class="ml-3 w-0 flex-1">
                <p class="text-sm font-medium text-slate-900 dark:text-white">
                  {{ notification.title }}
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 whitespace-pre-line">
                  {{ notification.message }}
                </p>
              </div>

              <!-- Close button -->
              <div class="ml-4 flex-shrink-0 flex">
                <button @click="hideNotification"
                  class="bg-white dark:bg-slate-800 rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <span class="sr-only">Close</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
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
