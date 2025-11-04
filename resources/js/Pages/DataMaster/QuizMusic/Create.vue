<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const form = useForm({
  audio: null,
  keterangan: "",
  is_active: true,
});

const audioFileName = ref("");
const audioPreview = ref(null);

const handleAudioChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.audio = file;
    audioFileName.value = file.name;
    
    // Create preview URL
    if (audioPreview.value) {
      URL.revokeObjectURL(audioPreview.value);
    }
    audioPreview.value = URL.createObjectURL(file);
  }
};

const removeAudio = () => {
  form.audio = null;
  audioFileName.value = "";
  if (audioPreview.value) {
    URL.revokeObjectURL(audioPreview.value);
    audioPreview.value = null;
  }
  // Reset input file
  const fileInput = document.getElementById('audio');
  if (fileInput) {
    fileInput.value = '';
  }
};

const submit = () => {
  form.post(route("data-master.quiz-music.store"), {
    onSuccess: () => {
      // Handle success
    },
  });
};
</script>

<template>
  <Head title="Tambah Media Music Quiz" />

  <AdminLayout>
    <template #title>Tambah Media Music Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Tambah Media Audio Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Upload file audio baru untuk quiz musik
          </p>
        </div>

        <div class="flex gap-3">
          <Link
            :href="route('data-master.quiz-music.index')"
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
    </div>

    <!-- Form -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
          Form Media Audio Quiz
        </h3>
      </div>

      <form @submit.prevent="submit" class="p-6 space-y-6">
        <!-- Audio File -->
        <div>
          <label
            for="audio"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            File Audio <span class="text-red-500">*</span>
          </label>
          
          <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-lg hover:border-slate-400 dark:hover:border-slate-500 transition-colors">
            <div class="space-y-1 text-center">
              <svg
                class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500"
                stroke="currentColor"
                fill="none"
                viewBox="0 0 48 48"
                aria-hidden="true"
              >
                <path
                  d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              <div class="flex text-sm text-slate-600 dark:text-slate-400">
                <label
                  for="audio"
                  class="relative cursor-pointer bg-white dark:bg-slate-800 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                >
                  <span>Upload file audio</span>
                  <input
                    id="audio"
                    name="audio"
                    type="file"
                    accept="audio/mp3,audio/wav,audio/ogg,audio/m4a"
                    class="sr-only"
                    @change="handleAudioChange"
                  />
                </label>
                <p class="pl-1">atau drag and drop</p>
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                MP3, WAV, OGG, M4A sampai 10MB
              </p>
            </div>
          </div>

          <!-- Preview Audio -->
          <div v-if="audioFileName" class="mt-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                    {{ audioFileName }}
                  </p>
                  <audio
                    v-if="audioPreview"
                    :src="audioPreview"
                    controls
                    class="mt-2 w-full max-w-md"
                  ></audio>
                </div>
              </div>
              <button
                type="button"
                @click="removeAudio"
                class="ml-4 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <div
            v-if="form.errors.audio"
            class="mt-1 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.audio }}
          </div>
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
            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
            placeholder="Masukkan keterangan audio (opsional)"
          ></textarea>
          <div
            v-if="form.errors.keterangan"
            class="mt-1 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.keterangan }}
          </div>
        </div>

        <!-- Status -->
        <div>
          <label
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            Status
          </label>
          <div class="flex items-center">
            <input
              id="is_active"
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded"
            />
            <label
              for="is_active"
              class="ml-2 block text-sm text-slate-700 dark:text-slate-300"
            >
              Aktif
            </label>
          </div>
          <div
            v-if="form.errors.is_active"
            class="mt-1 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.is_active }}
          </div>
        </div>

        <!-- Submit Button -->
        <div
          class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700"
        >
          <Link
            :href="route('data-master.quiz-music.index')"
            class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>