<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { usePermissions } from "@/composables/usePermissions";
import QuillEditor from "@/Components/QuillEditor.vue";

const { can } = usePermissions();

const props = defineProps({
  modul: Object,
  categoryList: Array,
});

// Form state
const form = useForm({
  category_id: props.modul.category_id,
  title: props.modul.title,
  slug: props.modul.slug,
  deskripsi: props.modul.deskripsi,
  content: props.modul.content,
  thumbnail: null,
  pdf_file: null,
  video_embed: props.modul.video_embed,
  status: props.modul.status,
  tanggal_publish: props.modul.tanggal_publish ? props.modul.tanggal_publish.slice(0, 16) : null,
  remove_thumbnail: '0',
  remove_pdf: '0',
});

// UI state
const thumbnailPreview = ref(props.modul.thumbnail ? `/storage/${props.modul.thumbnail}` : null);
const pdfPreview = ref(props.modul.pdf_file ? props.modul.pdf_file.split('/').pop() : null);

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
    form.thumbnail = file;
    form.remove_thumbnail = '0';
    const reader = new FileReader();
    reader.onload = (e) => {
      thumbnailPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handlePdfChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.pdf_file = file;
    form.remove_pdf = '0';
    pdfPreview.value = file.name;
  }
};

const removeThumbnail = () => {
  form.thumbnail = null;
  form.remove_thumbnail = '1';
  thumbnailPreview.value = null;
  // Reset input file
  const thumbnailInput = document.querySelector('input[name="thumbnail"]');
  if (thumbnailInput) thumbnailInput.value = '';
};

const removePdf = () => {
  form.pdf_file = null;
  form.remove_pdf = '1';
  pdfPreview.value = null;
  // Reset input file
  const pdfInput = document.querySelector('input[name="pdf_file"]');
  if (pdfInput) pdfInput.value = '';
};

const submit = () => {
  form.put(route("modul-pembelajaran.update", props.modul.id));
};

// Watch for title changes to generate slug
watch(
  () => form.title,
  (newVal) => {
    if (newVal && !form.isDirty('slug')) {
      generateSlug(newVal);
    }
  }
);

// Computed
const isPublishable = computed(() => {
  return form.title && form.category_id && (form.deskripsi || form.content);
});

const currentThumbnailUrl = computed(() => {
  if (form.remove_thumbnail === '1') return null;
  if (thumbnailPreview.value) return thumbnailPreview.value;
  return props.modul.thumbnail ? `/storage/${props.modul.thumbnail}` : null;
});

const currentPdfName = computed(() => {
  if (form.remove_pdf === '1') return null;
  if (pdfPreview.value) return pdfPreview.value;
  return props.modul.pdf_file ? props.modul.pdf_file.split('/').pop() : null;
});
</script>

<template>
  <Head title="Edit Modul Pembelajaran" />

  <AdminLayout>
    <template #title>Edit Modul Pembelajaran</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Edit Modul Pembelajaran
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Perbarui informasi dan konten modul pembelajaran
          </p>
        </div>
        <Link
          :href="route('modul-pembelajaran.show', modul.id)"
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
      <!-- Basic Info -->
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
                v-model="form.category_id"
                required
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.category_id }"
              >
                <option value="">Pilih Kategori</option>
                <option
                  v-for="category in categoryList"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.nama_kategori }}
                </option>
              </select>
              <p v-if="form.errors?.category_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.category_id }}
              </p>
            </div>

            <!-- Judul -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Judul Modul *</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Masukkan judul modul pembelajaran"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.title }"
              />
              <p v-if="form.errors?.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.title }}
              </p>
            </div>

            <!-- Slug -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">URL Slug</label>
              <input
                v-model="form.slug"
                type="text"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="modul-url-slug-otomatis"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.slug }"
              />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                URL slug akan dibuat otomatis dari judul modul jika tidak diubah manual
              </p>
              <p v-if="form.errors?.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.slug }}
              </p>
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Deskripsi Singkat</label>
              <textarea
                v-model="form.deskripsi"
                rows="3"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                placeholder="Deskripsi singkat tentang modul pembelajaran ini..."
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.deskripsi }"
              ></textarea>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Deskripsi singkat yang akan ditampilkan di halaman daftar modul
              </p>
              <p v-if="form.errors?.deskripsi" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.deskripsi }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Media Files -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Media Pendukung</h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Thumbnail -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Gambar Thumbnail</label>
            <div class="flex gap-4">
              <div class="flex-1">
                <input
                  @change="handleThumbnailChange"
                  type="file"
                  name="thumbnail"
                  accept="image/*"
                  class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                  :class="{ 'border-red-300 dark:border-red-600': form.errors?.thumbnail }"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Format: JPEG, PNG, JPG, GIF, WEBP. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.
                </p>
                <p v-if="form.errors?.thumbnail" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.thumbnail }}
                </p>
              </div>

              <div v-if="currentThumbnailUrl" class="relative">
                <img :src="currentThumbnailUrl" class="w-20 h-20 object-cover rounded-lg border border-slate-200 dark:border-slate-600" />
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

          <!-- PDF File -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">File PDF Materi</label>
            <div class="flex gap-4">
              <div class="flex-1">
                <input
                  @change="handlePdfChange"
                  type="file"
                  name="pdf_file"
                  accept=".pdf"
                  class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100"
                  :class="{ 'border-red-300 dark:border-red-600': form.errors?.pdf_file }"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Format: PDF. Maksimal 10MB. Kosongkan jika tidak ingin mengubah.
                </p>
                <p v-if="form.errors?.pdf_file" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.pdf_file }}
                </p>
              </div>

              <div v-if="currentPdfName" class="flex items-center gap-2">
                <div class="flex items-center px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                  <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 18h12V6l-4-4H4a2 2 0 00-2 2v12a2 2 0 002 2zM9 13a.75.75 0 01-.75-.75v-2.5a.75.75 0 011.5 0v2.5A.75.75 0 019 13zm2-3a.75.75 0 01.75.75v2.5a.75.75 0 01-1.5 0v-2.5A.75.75 0 0111 10z"/>
                  </svg>
                  <span class="text-sm text-red-700 dark:text-red-300 truncate max-w-32">{{ currentPdfName }}</span>
                </div>
                <button
                  @click="removePdf"
                  type="button"
                  class="w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-150"
                >
                  ×
                </button>
              </div>
            </div>
          </div>

          <!-- Video Embed -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Video YouTube (Embed URL)</label>
            <input
              v-model="form.video_embed"
              type="url"
              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              placeholder="https://www.youtube.com/watch?v=VIDEO_ID atau https://youtu.be/VIDEO_ID"
              :class="{ 'border-red-300 dark:border-red-600': form.errors?.video_embed }"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Masukkan URL video YouTube yang akan ditampilkan dalam modul
            </p>
            <p v-if="form.errors?.video_embed" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.video_embed }}
            </p>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Konten Pembelajaran</h3>
        </div>

        <div class="p-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Konten Detail</label>
            <QuillEditor
              v-model="form.content"
              placeholder="Tulis konten pembelajaran yang detail di sini..."
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Konten detail modul pembelajaran dengan formatting lengkap
            </p>
            <p v-if="form.errors?.content" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.content }}
            </p>
          </div>
        </div>
      </div>

      <!-- Settings -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Pengaturan Publikasi</h3>
        </div>

        <div class="p-6">
          <div class="space-y-4">
            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
              <div class="space-y-2">
                <label class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="draft" type="radio" class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <div>
                    <span class="text-slate-900 dark:text-white font-medium">Draft</span>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Simpan sebagai draft untuk dikerjakan nanti</p>
                  </div>
                </label>
                <label 
                  :class="[
                    'flex items-center p-3 border rounded-lg cursor-pointer transition-colors duration-150',
                    isPublishable 
                      ? 'border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700'
                      : 'border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 cursor-not-allowed opacity-60'
                  ]"
                >
                  <input 
                    v-model="form.status" 
                    value="published" 
                    type="radio" 
                    class="mr-3 text-blue-600 focus:ring-blue-500" 
                    :disabled="!isPublishable"
                  />
                  <div>
                    <span class="text-slate-900 dark:text-white font-medium">Publikasikan</span>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                      {{ isPublishable ? 'Publikasikan modul agar dapat diakses pengguna' : 'Lengkapi judul, kategori, dan konten untuk publikasi' }}
                    </p>
                  </div>
                </label>
                <label class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors duration-150">
                  <input v-model="form.status" value="archived" type="radio" class="mr-3 text-blue-600 focus:ring-blue-500" />
                  <div>
                    <span class="text-slate-900 dark:text-white font-medium">Arsipkan</span>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Simpan sebagai arsip (tidak ditampilkan)</p>
                  </div>
                </label>
              </div>
            </div>

            <!-- Tanggal Publish -->
            <div v-if="form.status === 'published'">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal Publikasi</label>
              <input
                v-model="form.tanggal_publish"
                type="datetime-local"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                :class="{ 'border-red-300 dark:border-red-600': form.errors?.tanggal_publish }"
              />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Kosongkan untuk publikasi sekarang
              </p>
              <p v-if="form.errors?.tanggal_publish" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.tanggal_publish }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-4">
        <Link
          :href="route('modul-pembelajaran.show', modul.id)"
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
          <span v-else>Update Modul</span>
        </button>
      </div>
    </form>
  </AdminLayout>
</template>