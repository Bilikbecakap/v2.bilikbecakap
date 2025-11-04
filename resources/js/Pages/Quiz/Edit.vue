<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const props = defineProps({
  quiz: Object,
  moduls: Array,
});

const form = useForm({
  _method: 'PUT',
  title: props.quiz.title,
  description: props.quiz.description,
  thumbnail: null,
  music: null,
  remove_thumbnail: false,
  remove_music: false,
  duration: props.quiz.duration,
  type: props.quiz.type,
  modul_pembelajaran_id: props.quiz.modul_pembelajaran_id,
  status: props.quiz.status,
});

// Preview states
const thumbnailPreview = ref(props.quiz.thumbnail_url);
const musicFileName = ref(props.quiz.music ? props.quiz.music.split('/').pop() : null);

// Watch type changes to reset modul_pembelajaran_id
watch(
  () => form.type,
  (newType) => {
    if (newType === "umum") {
      form.modul_pembelajaran_id = null;
    }
  }
);

// Handle thumbnail upload
const handleThumbnailChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.thumbnail = file;
    form.remove_thumbnail = false;
    
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
  form.remove_thumbnail = true;
  thumbnailPreview.value = null;
  document.getElementById('thumbnail').value = '';
};

// Handle music upload
const handleMusicChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.music = file;
    form.remove_music = false;
    musicFileName.value = file.name;
  }
};

// Remove music
const removeMusic = () => {
  form.music = null;
  form.remove_music = true;
  musicFileName.value = null;
  document.getElementById('music').value = '';
};

const submit = () => {
  router.post(route("quiz.update", props.quiz.id), {
    _method: 'PUT',
    title: form.title,
    description: form.description,
    thumbnail: form.thumbnail,
    music: form.music,
    remove_thumbnail: form.remove_thumbnail ? '1' : '0',
    remove_music: form.remove_music ? '1' : '0',
    duration: form.duration,
    type: form.type,
    modul_pembelajaran_id: form.modul_pembelajaran_id,
    status: form.status,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Success
    },
    onError: (errors) => {
      console.error('Update error:', errors);
    },
  });
};
</script>

<template>
  <Head title="Edit Quiz" />

  <AdminLayout>
    <template #title>Edit Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Edit Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Perbarui informasi quiz
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

          <!-- 👇 TAMBAH: Thumbnail Upload -->
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
              Kosongkan jika tidak ingin mengubah thumbnail
            </p>
            <p v-if="form.errors.thumbnail" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.thumbnail }}
            </p>
          </div>

          <!-- 👇 TAMBAH: Music Upload -->
          <div>
            <label
              for="music"
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              Background Music
            </label>
            
            <!-- Upload Button / File Info -->
            <div v-if="!musicFileName" class="flex items-center justify-center w-full">
              <label
                for="music"
                class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 dark:border-slate-600 border-dashed rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                :class="{
                  'border-red-500 dark:border-red-500': form.errors.music,
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
                      d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                    />
                  </svg>
                  <p class="mb-2 text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-semibold">Klik untuk upload</span> atau drag & drop
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    MP3, WAV, OGG (MAX. 100MB)
                  </p>
                </div>
                <input
                  id="music"
                  type="file"
                  accept="audio/mpeg,audio/wav,audio/ogg"
                  class="hidden"
                  @change="handleMusicChange"
                />
              </label>
            </div>

            <!-- File Info -->
            <div v-else class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                  <svg
                    class="w-6 h-6 text-blue-600 dark:text-blue-400"
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
                <div>
                  <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ musicFileName }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    Audio file ready
                  </p>
                </div>
              </div>
              <button
                type="button"
                @click="removeMusic"
                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
              </button>
            </div>

            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Kosongkan jika tidak ingin mengubah background music
            </p>
            <p v-if="form.errors.music" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.music }}
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
            <span v-else>Update Quiz</span>
          </button>
        </div>
      </div>
    </form>
  </AdminLayout>
</template>