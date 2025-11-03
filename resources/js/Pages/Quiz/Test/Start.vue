<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  quiz: Object,
  totalQuestions: Number,
});

const form = useForm({
  participant_name: "",
});

const submit = () => {
  form.post(route("quiz.attempt.begin", props.quiz.id), {
    preserveScroll: true,
  });
};

const getTypeBadge = (type) => {
  const badges = {
    modul: "bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200",
    umum: "bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200",
  };
  return badges[type] || badges["umum"];
};

const getTypeText = (type) => {
  const texts = {
    modul: "Quiz Modul",
    umum: "Quiz Umum",
  };
  return texts[type] || type;
};
</script>

<template>
  <Head :title="`Mulai Quiz - ${quiz.title}`" />

  <AdminLayout>
    <template #title>Mulai Quiz</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Persiapan Quiz
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Isi data Anda sebelum memulai quiz
          </p>
        </div>

        <div>
          <Link
            :href="route('quiz.attempt.index')"
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

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Form -->
        <div class="lg:col-span-2">
          <form @submit.prevent="submit">
            <div
              class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
            >
              <!-- Quiz Info Header -->
              <div
                class="bg-gradient-to-r from-blue-500 to-cyan-500 p-6 text-white rounded-t-xl"
              >
                <div class="flex items-start justify-between mb-3">
                  <span
                    :class="[
                      'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm',
                    ]"
                  >
                    {{ getTypeText(quiz.type) }}
                  </span>
                </div>
                <h3 class="text-2xl font-bold mb-2">{{ quiz.title }}</h3>
                <p v-if="quiz.description" class="text-sm text-white/80">
                  {{ quiz.description }}
                </p>
              </div>

              <!-- Form Body -->
              <div class="p-6">
                <!-- Participant Name -->
                <div class="mb-6">
                  <label
                    for="participant_name"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                  >
                    Nama Lengkap <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="participant_name"
                    v-model="form.participant_name"
                    type="text"
                    placeholder="Masukkan nama lengkap Anda"
                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                    :class="{
                      'border-red-500 dark:border-red-500': form.errors.participant_name,
                    }"
                  />
                  <p
                    v-if="form.errors.participant_name"
                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                  >
                    {{ form.errors.participant_name }}
                  </p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Nama akan digunakan untuk identifikasi hasil quiz Anda
                  </p>
                </div>

                <!-- Error Global -->
                <div
                  v-if="form.errors.error"
                  class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 p-4"
                >
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

                <!-- Quiz Instructions -->
                <div
                  class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg"
                >
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
                      <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                        Petunjuk Pengerjaan:
                      </h4>
                      <ul
                        class="text-sm text-blue-700 dark:text-blue-300 space-y-1 list-disc list-inside"
                      >
                        <li>Pastikan koneksi internet Anda stabil</li>
                        <li>Kerjakan quiz dengan jujur dan mandiri</li>
                        <li>
                          Waktu akan mulai berjalan setelah Anda klik "Mulai Quiz"
                        </li>
                        <li>Pastikan semua jawaban terisi sebelum submit</li>
                        <li>Hasil akan ditampilkan setelah quiz selesai</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3">
                  <Link
                    :href="route('quiz.attempt.index')"
                    class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                  >
                    Batal
                  </Link>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg
                      v-if="form.processing"
                      class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                      xmlns="http://www.w3.org/2000/svg"
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
                      class="w-4 h-4 mr-2"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                    <span v-if="form.processing">Memulai...</span>
                    <span v-else>Mulai Quiz</span>
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Right Column - Quiz Info -->
        <div class="space-y-6">
          <!-- Quiz Statistics -->
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
          >
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
              <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                Detail Quiz
              </h3>
            </div>

            <div class="p-6 space-y-4">
              <!-- Duration -->
              <div
                class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
              >
                <div class="flex items-center">
                  <div
                    class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center mr-3"
                  >
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Durasi</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-white">
                      {{ quiz.duration }} Menit
                    </p>
                  </div>
                </div>
              </div>

              <!-- Total Questions -->
              <div
                class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"
              >
                <div class="flex items-center">
                  <div
                    class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center mr-3"
                  >
                    <svg
                      class="w-5 h-5 text-white"
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
                  </div>
                  <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Jumlah Soal</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-white">
                      {{ totalQuestions }} Soal
                    </p>
                  </div>
                </div>
              </div>

              <!-- Modul Info -->
              <div
                v-if="quiz.modul_pembelajaran"
                class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg"
              >
                <div class="flex items-start">
                  <div
                    class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center mr-3 flex-shrink-0"
                  >
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                      Modul Pembelajaran
                    </p>
                    <p class="text-sm font-medium text-slate-800 dark:text-white">
                      {{ quiz.modul_pembelajaran.title }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tips Card -->
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
          >
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
              <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                Tips Sukses
              </h3>
            </div>

            <div class="p-6 space-y-4">
              <div class="flex items-start">
                <div
                  class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3"
                >
                  <svg
                    class="w-4 h-4 text-green-600 dark:text-green-400"
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
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-800 dark:text-white mb-1">
                    Baca dengan Teliti
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Pastikan membaca setiap soal dengan cermat sebelum menjawab
                  </p>
                </div>
              </div>

              <div class="flex items-start">
                <div
                  class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-3"
                >
                  <svg
                    class="w-4 h-4 text-blue-600 dark:text-blue-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-800 dark:text-white mb-1">
                    Kelola Waktu
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Perhatikan waktu yang tersedia dan jangan terlalu lama di satu soal
                  </p>
                </div>
              </div>

              <div class="flex items-start">
                <div
                  class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center mr-3"
                >
                  <svg
                    class="w-4 h-4 text-purple-600 dark:text-purple-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-800 dark:text-white mb-1">
                    Periksa Jawaban
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Review jawaban Anda sebelum submit untuk memastikan tidak ada yang terlewat
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>