<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { usePermissions } from "@/composables/usePermissions";
import QuillEditor from "@/Components/QuillEditor.vue"; // Import component yang sudah kamu buat!

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

    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-800 dark:text-white">
            Buat Artikel Baru
          </h2>
          <p class="text-slate-600 dark:text-slate-400">
            Buat artikel dengan dukungan multi-bahasa
          </p>
        </div>
        <Link
          :href="route('artikel.index')"
          class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700"
        >
          Kembali
        </Link>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Basic Info -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Informasi Dasar</h3>

        <div class="grid gap-4">
          <!-- Kategori -->
          <div>
            <label class="block text-sm font-medium mb-2">Kategori *</label>
            <select
              v-model="form.kategori_id"
              required
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
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
          </div>

          <!-- Slug -->
          <div>
            <label class="block text-sm font-medium mb-2">URL Slug</label>
            <input
              v-model="form.slug"
              type="text"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
              placeholder="artikel-url-slug"
            />
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

        <div class="flex gap-4">
          <div class="flex-1">
            <input
              @change="handleThumbnailChange"
              type="file"
              accept="image/*"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
            />
          </div>

          <div v-if="thumbnailPreview" class="relative">
            <img :src="thumbnailPreview" class="w-20 h-20 object-cover rounded-lg" />
            <button
              @click="removeThumbnail"
              type="button"
              class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
            >
              ×
            </button>
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
                placeholder="Masukkan judul dalam bahasa Indonesia"
              />
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
              <label v-if="can('approve artikel')" class="flex items-center">
                <input
                  v-model="form.status"
                  value="published"
                  type="radio"
                  class="mr-2"
                />
                <span>Publikasikan Langsung</span>
              </label>
            </div>
          </div>

          <!-- Featured -->
          <div>
            <label class="flex items-center">
              <input v-model="form.is_featured" type="checkbox" class="mr-2" />
              <span>Artikel Unggulan</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-4">
        <Link
          :href="route('artikel.index')"
          class="px-6 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50"
        >
          Batal
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
        >
          <span v-if="form.processing">Menyimpan...</span>
          <span v-else>Simpan Artikel</span>
        </button>
      </div>
    </form>
  </AdminLayout>
</template>
