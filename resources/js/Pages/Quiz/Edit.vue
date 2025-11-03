<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
  quiz: Object,
  moduls: Array,
});

const form = useForm({
  title: props.quiz.title,
  description: props.quiz.description,
  duration: props.quiz.duration,
  type: props.quiz.type,
  modul_pembelajaran_id: props.quiz.modul_pembelajaran_id,
  status: props.quiz.status,
});

// Watch type changes to reset modul_pembelajaran_id
watch(
  () => form.type,
  (newType) => {
    if (newType === "umum") {
      form.modul_pembelajaran_id = null;
    }
  }
);

const submit = () => {
  form.put(route("quiz.update", props.quiz.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by controller
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

        <div class="flex gap-3">
          <Link
            :href="route('quiz.questions.index', quiz.id)"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-sm font-medium rounded-lg hover:shadow-md transition-all duration-200"
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
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            Kelola Soal
          </Link>

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

          <!-- Info Box -->
          <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg
                  class="h-5 w-5 text-blue-400"
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
              <div class="ml-3">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                  Setelah menyimpan perubahan, Anda dapat mengelola soal-soal quiz dengan
                  mengklik tombol "Kelola Soal" di atas.
                </p>
              </div>
            </div>
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