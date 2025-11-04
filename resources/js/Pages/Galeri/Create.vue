<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  kategoris: Array,
});

const form = useForm({
  gambar: null,
  master_gambar_id: "",
  keterangan: "",
  is_active: true,
});

const imagePreview = ref(null);

const handleImageChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.gambar = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeImage = () => {
  form.gambar = null;
  imagePreview.value = null;
  document.getElementById("gambar").value = "";
};

const submit = () => {
  form.post(route("galeri.store"), {
    forceFormData: true,
  });
};
</script>

<template>
  <Head title="Tambah Gambar Galeri" />

  <AdminLayout>
    <template #title>Tambah Gambar Galeri</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Tambah Gambar Galeri
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Tambahkan gambar baru ke galeri
          </p>
        </div>

        <Link
          :href="route('galeri.index')"
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
      </div>
    </div>

    <!-- Form -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <form @submit.prevent="submit" class="p-6 space-y-6">
        <!-- Upload Gambar -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Gambar <span class="text-red-500">*</span>
          </label>

          <!-- Image Preview -->
          <div
            v-if="imagePreview"
            class="mb-4 relative inline-block"
          >
            <img
              :src="imagePreview"
              alt="Preview"
              class="w-full max-w-md h-64 object-cover rounded-lg border-2 border-slate-200 dark:border-slate-600"
            />
            <button
              type="button"
              @click="removeImage"
              class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <!-- Upload Button -->
          <div
            v-if="!imagePreview"
            class="flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-lg hover:border-purple-400 dark:hover:border-purple-500 transition-colors"
          >
            <div class="space-y-2 text-center">
              <svg
                class="mx-auto h-12 w-12 text-slate-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              <div class="flex text-sm text-slate-600 dark:text-slate-400">
                <label
                  for="gambar"
                  class="relative cursor-pointer rounded-md font-medium text-purple-600 dark:text-purple-400 hover:text-purple-500 focus-within:outline-none"
                >
                  <span>Upload gambar</span>
                  <input
                    id="gambar"
                    type="file"
                    class="sr-only"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    @change="handleImageChange"
                  />
                </label>
                <p class="pl-1">atau drag and drop</p>
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                PNG, JPG, WEBP hingga 5MB
              </p>
            </div>
          </div>

          <p v-if="form.errors.gambar" class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.gambar }}
          </p>
        </div>

        <!-- Kategori -->
        <div>
          <label
            for="master_gambar_id"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            Kategori <span class="text-red-500">*</span>
          </label>
          <select
            id="master_gambar_id"
            v-model="form.master_gambar_id"
            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
            :class="{ 'border-red-500': form.errors.master_gambar_id }"
          >
            <option value="">Pilih Kategori</option>
            <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">
              {{ kat.nama_kategori }}
            </option>
          </select>
          <p
            v-if="form.errors.master_gambar_id"
            class="mt-2 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.master_gambar_id }}
          </p>
        </div>

        <!-- Keterangan -->
        <div>
          <label
            for="keterangan"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            Keterangan
          </label>
          <textarea
            id="keterangan"
            v-model="form.keterangan"
            rows="4"
            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors resize-none"
            :class="{ 'border-red-500': form.errors.keterangan }"
            placeholder="Masukkan keterangan gambar..."
          ></textarea>
          <p
            v-if="form.errors.keterangan"
            class="mt-2 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.keterangan }}
          </p>
        </div>

        <!-- Status -->
        <div>
          <label class="flex items-center">
            <input
              type="checkbox"
              v-model="form.is_active"
              class="w-4 h-4 text-purple-600 bg-slate-100 border-slate-300 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600"
            />
            <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">
              Aktif
            </span>
          </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
          <Link
            :href="route('galeri.index')"
            class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium rounded-lg hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>