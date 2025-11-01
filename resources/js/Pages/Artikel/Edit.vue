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
});

// Form state - initialize with existing article data
const form = useForm({
  judul_indonesia: props.artikel.judul_indonesia || "",
  judul_melayu: props.artikel.judul_melayu || "",
  judul_english: props.artikel.judul_english || "",
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
});

// UI state
const activeTab = ref("indonesia");
const thumbnailPreview = ref(null);
const existingThumbnail = ref(props.artikel.gambar_thumbnail || null);
const removeThumbnailFlag = ref(false);
const hasApprovalPermission = computed(() => can("approve artikel"));
const canEdit = computed(() => props.artikel.can_edit || hasApprovalPermission.value);

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

const submit = () => {
  form.put(route("artikel.update", props.artikel.id), {
    // Hapus forceFormData jika tidak upload file baru
    forceFormData: form.gambar_thumbnail ? true : false,
    onSuccess: () => {
      removeThumbnailFlag.value = false;
      if (form.gambar_thumbnail) {
        existingThumbnail.value = null;
      }
    },
  });
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
  if (existingThumbnail.value) {
    thumbnailPreview.value = `/storage/${existingThumbnail.value}`;
  }
});
</script>
<template>
  <Head
    :title="`Edit Artikel: ${
      artikel.judul_indonesia ||
      artikel.judul_melayu ||
      artikel.judul_english ||
      'Untitled'
    }`"
  />

  <AdminLayout>
    <template #title>Edit Artikel</template>

    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Artikel</h2>
          <p class="text-slate-600 dark:text-slate-400">
            Edit artikel dengan dukungan multi-bahasa
          </p>
          <!-- Status Badge -->
          <div class="mt-2">
            <span
              :class="{
                'bg-yellow-100 text-yellow-800': artikel.status === 'draft',
                'bg-blue-100 text-blue-800': artikel.status === 'pending',
                'bg-green-100 text-green-800': artikel.status === 'published',
                'bg-gray-100 text-gray-800': artikel.status === 'archived',
              }"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            >
              {{ artikel.status.charAt(0).toUpperCase() + artikel.status.slice(1) }}
            </span>
            <span
              v-if="artikel.is_featured"
              class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
            >
              Featured
            </span>
          </div>
        </div>
        <Link
          :href="route('artikel.index')"
          class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700"
        >
          Kembali
        </Link>
      </div>
    </div>

    <!-- Permission Check -->
    <div
      v-if="!canEdit"
      class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"
    >
      <p>Anda tidak memiliki izin untuk mengedit artikel ini.</p>
    </div>

    <form v-else @submit.prevent="submit" class="space-y-6">
      <!-- Basic Info -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Informasi Dasar</h3>

        <div class="grid gap-4">
          <!-- Kategori -->
          <div>
            <label class="block text-sm font-medium mb-2">Kategori *</label>
            <select
              v-model="form.kategori_id"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
              :class="{ 'border-red-500': form.errors.kategori_id }"
            >
              <option :value="null">Pilih Kategori</option>
              <option
                v-for="kategori in kategoriList"
                :key="kategori.id"
                :value="kategori.id"
              >
                {{ kategori.nama_kategori }}
              </option>
            </select>
            <div v-if="form.errors.kategori_id" class="text-red-500 text-sm mt-1">
              {{ form.errors.kategori_id }}
            </div>
          </div>

          <!-- Slug -->
          <div>
            <label class="block text-sm font-medium mb-2">URL Slug</label>
            <input
              v-model="form.slug"
              type="text"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
              :class="{ 'border-red-500': form.errors.slug }"
              placeholder="artikel-url-slug"
            />
            <div v-if="form.errors.slug" class="text-red-500 text-sm mt-1">
              {{ form.errors.slug }}
            </div>
          </div>

          <!-- Keywords -->
          <div>
            <label class="block text-sm font-medium mb-2">Meta Keywords</label>
            <input
              v-model="form.meta_keywords"
              type="text"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
              placeholder="keyword1, keyword2, keyword3"
            />
          </div>
        </div>
      </div>

      <!-- Thumbnail -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Gambar Thumbnail</h3>

        <div class="space-y-4">
          <!-- File Input -->
          <div>
            <input
              @change="handleThumbnailChange"
              type="file"
              accept="image/*"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
              :class="{ 'border-red-500': form.errors.gambar_thumbnail }"
            />
            <div v-if="form.errors.gambar_thumbnail" class="text-red-500 text-sm mt-1">
              {{ form.errors.gambar_thumbnail }}
            </div>
          </div>

          <!-- Thumbnail Preview -->
          <div
            v-if="thumbnailPreview || existingThumbnail"
            class="flex gap-4 items-start"
          >
            <div class="relative">
              <img
                :src="thumbnailPreview || `/storage/${existingThumbnail}`"
                class="w-32 h-32 object-cover rounded-lg border"
                alt="Thumbnail preview"
              />
              <button
                @click="removeThumbnail"
                type="button"
                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600"
                title="Hapus thumbnail"
              >
                ×
              </button>
            </div>
            <div class="text-sm text-slate-600 dark:text-slate-400">
              <p v-if="form.gambar_thumbnail">Thumbnail baru akan mengganti yang lama</p>
              <p v-else-if="existingThumbnail && !removeThumbnailFlag">
                Thumbnail saat ini
              </p>
              <p v-else-if="removeThumbnailFlag" class="text-red-600">
                Thumbnail akan dihapus
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow">
        <div class="p-6 border-b">
          <h3 class="text-lg font-semibold">Konten Multi-Bahasa</h3>
        </div>

        <!-- Tabs -->
        <div class="border-b">
          <nav class="flex px-6">
            <button
              @click="activeTab = 'indonesia'"
              type="button"
              :class="[
                'py-4 px-4 border-b-2 font-medium text-sm',
                activeTab === 'indonesia'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-slate-500 hover:text-slate-700',
              ]"
            >
              🇮🇩 Indonesia
            </button>
            <button
              @click="activeTab = 'melayu'"
              type="button"
              :class="[
                'py-4 px-4 border-b-2 font-medium text-sm',
                activeTab === 'melayu'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-slate-500 hover:text-slate-700',
              ]"
            >
              🇲🇾 Melayu
            </button>
            <button
              @click="activeTab = 'english'"
              type="button"
              :class="[
                'py-4 px-4 border-b-2 font-medium text-sm',
                activeTab === 'english'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-slate-500 hover:text-slate-700',
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
              <label class="block text-sm font-medium mb-2">Judul Indonesia</label>
              <input
                v-model="form.judul_indonesia"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                :class="{ 'border-red-500': form.errors.judul_indonesia }"
                placeholder="Masukkan judul dalam bahasa Indonesia"
              />
              <div v-if="form.errors.judul_indonesia" class="text-red-500 text-sm mt-1">
                {{ form.errors.judul_indonesia }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Konten Indonesia</label>
              <QuillEditor
                v-model="form.konten_indonesia"
                placeholder="Tulis konten dalam bahasa Indonesia..."
              />
            </div>
          </div>

          <!-- Melayu -->
          <div v-show="activeTab === 'melayu'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-2">Judul Melayu</label>
              <input
                v-model="form.judul_melayu"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                placeholder="Masukkan judul dalam bahasa Melayu"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Konten Melayu</label>
              <QuillEditor
                v-model="form.konten_melayu"
                placeholder="Tulis konten dalam bahasa Melayu..."
              />
            </div>
          </div>

          <!-- English -->
          <div v-show="activeTab === 'english'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-2">English Title</label>
              <input
                v-model="form.judul_english"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                placeholder="Enter title in English"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">English Content</label>
              <QuillEditor
                v-model="form.konten_english"
                placeholder="Write content in English..."
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Settings -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Pengaturan</h3>

        <div class="space-y-4">
          <!-- Status -->
          <div>
            <label class="block text-sm font-medium mb-2">Status</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input v-model="form.status" value="draft" type="radio" class="mr-2" />
                <span>Draft</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.status" value="pending" type="radio" class="mr-2" />
                <span>Kirim untuk Review</span>
              </label>
              <label v-if="hasApprovalPermission" class="flex items-center">
                <input
                  v-model="form.status"
                  value="published"
                  type="radio"
                  class="mr-2"
                />
                <span>Publikasikan Langsung</span>
              </label>
              <label v-if="hasApprovalPermission" class="flex items-center">
                <input v-model="form.status" value="archived" type="radio" class="mr-2" />
                <span>Arsipkan</span>
              </label>
            </div>

            <!-- Status change warning -->
            <div
              v-if="!hasApprovalPermission && artikel.status === 'published'"
              class="mt-2 p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded text-sm"
            >
              <p>
                <strong>Perhatian:</strong> Karena artikel ini sudah dipublikasikan,
                perubahan yang Anda buat akan mengubah status menjadi "Pending" dan
                memerlukan persetujuan admin kembali.
              </p>
            </div>
          </div>

          <!-- Featured -->
          <div>
            <label class="flex items-center">
              <input v-model="form.is_featured" type="checkbox" class="mr-2" />
              <span>Artikel Unggulan</span>
            </label>
          </div>

          <!-- Publish Date (for published articles) -->
          <div v-if="form.status === 'published' || artikel.tanggal_publish">
            <label class="block text-sm font-medium mb-2">Tanggal Publikasi</label>
            <input
              v-model="form.tanggal_publish"
              type="datetime-local"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
            />
            <p class="text-sm text-slate-500 mt-1">
              Kosongkan untuk menggunakan waktu saat ini
            </p>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-4">
        <Link
          :href="route('artikel.index')"
          class="px-6 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700"
        >
          Batal
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Menyimpan...</span>
          <span v-else>Update Artikel</span>
        </button>
      </div>

      <!-- Error Messages -->
      <div
        v-if="form.errors.error"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
      >
        {{ form.errors.error }}
      </div>
    </form>
  </AdminLayout>
</template>
