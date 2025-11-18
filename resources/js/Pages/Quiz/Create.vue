<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const props = defineProps({
  moduls: Array,
  musicQuizzes: Array,
});

const form = useForm({
  title: "",
  description: "",
  thumbnail: null,
  master_media_music_quiz_id: null,
  duration: 30,
  type: "umum",
  modul_pembelajaran_id: null,
  is_duel_enabled: false, // TAMBAH
  status: "active",
});

// Preview states
const thumbnailPreview = ref(null);
const selectedMusic = ref(null);

// Watch type changes to reset modul_pembelajaran_id
watch(
  () => form.type,
  (newType) => {
    if (newType === "umum") {
      form.modul_pembelajaran_id = null;
    }
  }
);

// Watch music selection changes
watch(
  () => form.master_media_music_quiz_id,
  (newMusicId) => {
    if (newMusicId) {
      selectedMusic.value = props.musicQuizzes.find(m => m.id === newMusicId);
    } else {
      selectedMusic.value = null;
    }
  }
);

// Handle thumbnail upload
const handleThumbnailChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.thumbnail = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
      thumbnailPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Remove thumbnail
const removeThumbnail = () => {
  form.thumbnail = null;
  thumbnailPreview.value = null;
  document.getElementById('thumbnail').value = '';
};

const submit = () => {
  form.post(route("quiz.store"), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      // Redirect handled by controller
    },
  });
};
</script>

<template>
  <Head title="Buat Quiz Baru" />

  <AdminLayout>
    <template #title>Buat Quiz Baru</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Buat Quiz Baru
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Tambahkan quiz baru ke sistem
          </p>
        </div>

        <div>
          <Link
            :href="route('quiz.index')"
            class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
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
    <form @submit.prevent="submit">
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
      >
        <div class="p-6 space-y-6">
          <!-- Judul Quiz -->
          <div>
            <label
              for="title"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Judul Quiz <span class="text-red-500">*</span>
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              :class="{
                'border-red-500 dark:border-red-500': form.errors.title,
              }"
              placeholder="Masukkan judul quiz"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.title }}
            </p>
          </div>

          <!-- Deskripsi -->
          <div>
            <label
              for="description"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Deskripsi Quiz
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white resize-none"
              :class="{
                'border-red-500 dark:border-red-500': form.errors.description,
              }"
              placeholder="Masukkan deskripsi quiz (opsional)"
            ></textarea>
            <p
              v-if="form.errors.description"
              class="mt-1 text-sm text-red-600 dark:text-red-400"
            >
              {{ form.errors.description }}
            </p>
          </div>

          <!-- Thumbnail Upload -->
          <div>
            <label
              for="thumbnail"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Thumbnail Quiz
            </label>
            
            <!-- Upload Button / Preview -->
            <div v-if="!thumbnailPreview" class="flex items-center justify-center w-full">
              <label
                for="thumbnail"
                class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                :class="{
                  'border-red-500 dark:border-red-500': form.errors.thumbnail,
                }"
              >
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                  <svg
                    class="w-10 h-10 mb-3 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <p class="mb-2 text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-semibold">Klik untuk upload</span> atau drag & drop
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    PNG, JPG, WEBP (MAX. 2MB)
                  </p>
                </div>
                <input
                  id="thumbnail"
                  type="file"
                  accept="image/jpeg,image/jpg,image/png,image/webp"
                  class="hidden"
                  @change="handleThumbnailChange"
                />
              </label>
            </div>

            <!-- Preview Image -->
            <div v-else class="relative">
              <img
                :src="thumbnailPreview"
                alt="Thumbnail Preview"
                class="w-full h-64 object-cover rounded-lg border-2 border-slate-200 dark:border-slate-700"
              />
              <button
                type="button"
                @click="removeThumbnail"
                class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors shadow-lg"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>

            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Gambar thumbnail akan ditampilkan di daftar quiz
            </p>
            <p v-if="form.errors.thumbnail" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.thumbnail }}
            </p>
          </div>

          <!-- Music Selector -->
          <div>
            <label
              for="master_media_music_quiz_id"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Background Music
            </label>
            <select
              id="master_media_music_quiz_id"
              v-model="form.master_media_music_quiz_id"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              :class="{
                'border-red-500 dark:border-red-500': form.errors.master_media_music_quiz_id,
              }"
            >
              <option :value="null">-- Tidak Ada Music --</option>
              <option 
                v-for="music in musicQuizzes" 
                :key="music.id" 
                :value="music.id"
              >
                {{ music.audio.split('/').pop() }}
                <span v-if="music.keterangan"> - {{ music.keterangan }}</span>
              </option>
            </select>

            <!-- Preview Audio yang dipilih -->
            <div v-if="selectedMusic" class="mt-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
              <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                  <svg
                    class="w-5 h-5 text-blue-600 dark:text-blue-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                    />
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                    {{ selectedMusic.audio.split('/').pop() }}
                  </p>
                  <p v-if="selectedMusic.keterangan" class="text-xs text-blue-700 dark:text-blue-300">
                    {{ selectedMusic.keterangan }}
                  </p>
                </div>
              </div>
              <audio
                :src="selectedMusic.audio_url"
                controls
                class="w-full"
              ></audio>
            </div>

            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Music akan diputar sebagai background saat quiz dikerjakan
            </p>
            <p v-if="form.errors.master_media_music_quiz_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.master_media_music_quiz_id }}
            </p>
          </div>

          <!-- Durasi -->
          <div>
            <label
              for="duration"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Durasi Quiz (menit) <span class="text-red-500">*</span>
            </label>
            <input
              id="duration"
              v-model.number="form.duration"
              type="number"
              min="1"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              :class="{
                'border-red-500 dark:border-red-500': form.errors.duration,
              }"
              placeholder="30"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Total waktu yang diberikan untuk menyelesaikan quiz
            </p>
            <p v-if="form.errors.duration" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.duration }}
            </p>
          </div>

          <!-- Tipe Quiz -->
          <div>
            <label
              for="type"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Tipe Quiz <span class="text-red-500">*</span>
            </label>
            <select
              id="type"
              v-model="form.type"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              :class="{
                'border-red-500 dark:border-red-500': form.errors.type,
              }"
            >
              <option value="umum">Quiz Umum</option>
              <option value="modul">Quiz Modul</option>
            </select>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Quiz umum dapat diakses secara mandiri, quiz modul terikat dengan modul pembelajaran
            </p>
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.type }}
            </p>
          </div>

          <!-- Modul Pembelajaran (conditional) -->
          <div v-if="form.type === 'modul'">
            <label
              for="modul_pembelajaran_id"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Pilih Modul Pembelajaran <span class="text-red-500">*</span>
            </label>
            <select
              id="modul_pembelajaran_id"
              v-model="form.modul_pembelajaran_id"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              :class="{
                'border-red-500 dark:border-red-500': form.errors.modul_pembelajaran_id,
              }"
            >
              <option :value="null">-- Pilih Modul --</option>
              <option v-for="modul in moduls" :key="modul.id" :value="modul.id">
                {{ modul.title }}
              </option>
            </select>
            <p
              v-if="form.errors.modul_pembelajaran_id"
              class="mt-1 text-sm text-red-600 dark:text-red-400"
            >
              {{ form.errors.modul_pembelajaran_id }}
            </p>
          </div>

          <!-- TAMBAH: Duel Mode Enabled -->
          <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4 border-2 border-purple-200 dark:border-purple-700">
            <div class="flex items-start gap-4">
              <div class="flex items-center h-5 mt-1">
                <input
                  id="is_duel_enabled"
                  v-model="form.is_duel_enabled"
                  type="checkbox"
                  class="w-5 h-5 text-purple-600 bg-white border-purple-300 rounded focus:ring-purple-500 focus:ring-2 dark:bg-slate-700 dark:border-purple-600 cursor-pointer"
                />
              </div>
              <div class="flex-1">
                <label
                  for="is_duel_enabled"
                  class="text-sm font-semibold text-purple-900 dark:text-purple-100 cursor-pointer flex items-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  Aktifkan Mode Duel (Tarik Tambang)
                </label>
                <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">
                  Mode duel memungkinkan 2 player bermain bersama dalam format tarik tambang!
                </p>
              </div>
            </div>
          </div>

          <!-- Status -->
          <div>
            <label
              for="status"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Status <span class="text-red-500">*</span>
            </label>
            <select
              id="status"
              v-model="form.status"
              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
              :class="{
                'border-red-500 dark:border-red-500': form.errors.status,
              }"
            >
              <option value="active">Aktif</option>
              <option value="inactive">Tidak Aktif</option>
            </select>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Quiz dengan status aktif dapat diakses oleh user
            </p>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.status }}
            </p>
          </div>

          <!-- Error Global -->
          <div v-if="form.errors.error" class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg
                  class="h-5 w-5 text-red-400"
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
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-800 dark:text-red-200">
                  {{ form.errors.error }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div
          class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3"
        >
          <Link
            :href="route('quiz.index')"
            class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-150"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan Quiz</span>
          </button>
        </div>
      </div>
    </form>
  </AdminLayout>
</template>