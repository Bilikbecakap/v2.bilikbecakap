<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import QuillEditor from "@/Components/QuillEditor.vue";

const props = defineProps({
  quiz: Object,
  nextOrder: Number,
});

const form = useForm({
  question: "",
  order: props.nextOrder,
  options: [
    { option_text: "", is_correct: false, order: 0 },
    { option_text: "", is_correct: false, order: 1 },
    { option_text: "", is_correct: false, order: 2 },
    { option_text: "", is_correct: false, order: 3 },
  ],
});

const addOption = () => {
  if (form.options.length < 6) {
    form.options.push({
      option_text: "",
      is_correct: false,
      order: form.options.length,
    });
  }
};

const removeOption = (index) => {
  if (form.options.length > 2) {
    form.options.splice(index, 1);
    // Re-order
    form.options.forEach((opt, idx) => {
      opt.order = idx;
    });
  }
};

const setCorrectAnswer = (index) => {
  form.options.forEach((opt, idx) => {
    opt.is_correct = idx === index;
  });
};

const submit = () => {
  form.post(route("quiz.questions.store", props.quiz.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by controller
    },
  });
};

const getOptionLabel = (index) => {
  return String.fromCharCode(65 + index); // A, B, C, D, ...
};
</script>

<template>
  <Head title="Tambah Soal Baru" />

  <AdminLayout>
    <template #title>Tambah Soal Baru</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Tambah Soal Baru
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ quiz.title }}
          </p>
        </div>

        <div>
          <Link
            :href="route('quiz.questions.index', quiz.id)"
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
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
          >
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
              <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                Informasi Soal
              </h3>
            </div>

            <div class="p-6 space-y-6">
              <!-- Pertanyaan dengan Rich Text Editor -->
              <div>
                <label
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                >
                  Pertanyaan <span class="text-red-500">*</span>
                </label>
                <QuillEditor
                  v-model="form.question"
                  placeholder="Tulis pertanyaan soal dengan format yang menarik..."
                  :upload-url="route('modul-pembelajaran.upload-image')"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Anda dapat menggunakan bold, italic, warna, dan format lainnya
                </p>
                <p v-if="form.errors.question" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.question }}
                </p>
              </div>

              <!-- Urutan -->
              <div>
                <label
                  for="order"
                  class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                >
                  Urutan Soal <span class="text-red-500">*</span>
                </label>
                <input
                  id="order"
                  v-model.number="form.order"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                  :class="{
                    'border-red-500 dark:border-red-500': form.errors.order,
                  }"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Nomor urut soal dalam quiz (otomatis diisi)
                </p>
                <p v-if="form.errors.order" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.order }}
                </p>
              </div>

              <!-- Pilihan Jawaban -->
              <div>
                <div class="flex items-center justify-between mb-3">
                  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Pilihan Jawaban <span class="text-red-500">*</span>
                  </label>
                  <button
                    v-if="form.options.length < 6"
                    @click="addOption"
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150"
                  >
                    <svg
                      class="w-3 h-3 mr-1"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                      />
                    </svg>
                    Tambah Pilihan
                  </button>
                </div>

                <div class="space-y-3">
                  <div
                    v-for="(option, index) in form.options"
                    :key="index"
                    :class="[
                      'p-4 rounded-lg border-2 transition-all duration-150',
                      option.is_correct
                        ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                        : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50',
                    ]"
                  >
                    <div class="flex items-start gap-3">
                      <!-- Label -->
                      <div
                        :class="[
                          'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                          option.is_correct
                            ? 'bg-green-500 text-white'
                            : 'bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                        ]"
                      >
                        {{ getOptionLabel(index) }}
                      </div>

                      <!-- Input -->
                      <div class="flex-1">
                        <input
                          v-model="option.option_text"
                          type="text"
                          :placeholder="`Masukkan pilihan ${getOptionLabel(index)}`"
                          class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
                          :class="{
                            'border-red-500 dark:border-red-500':
                              form.errors[`options.${index}.option_text`],
                          }"
                        />
                        <p
                          v-if="form.errors[`options.${index}.option_text`]"
                          class="mt-1 text-xs text-red-600 dark:text-red-400"
                        >
                          {{ form.errors[`options.${index}.option_text`] }}
                        </p>
                      </div>

                      <!-- Actions -->
                      <div class="flex-shrink-0 flex items-center gap-2">
                        <!-- Set Correct -->
                        <button
                          @click="setCorrectAnswer(index)"
                          type="button"
                          :class="[
                            'p-2 rounded-lg transition-colors duration-150',
                            option.is_correct
                              ? 'bg-green-500 text-white'
                              : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-green-500 hover:text-white',
                          ]"
                          title="Tandai sebagai jawaban benar"
                        >
                          <svg
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"
                            />
                          </svg>
                        </button>

                        <!-- Remove -->
                        <button
                          v-if="form.options.length > 2"
                          @click="removeOption(index)"
                          type="button"
                          class="p-2 rounded-lg bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/40 transition-colors duration-150"
                          title="Hapus pilihan"
                        >
                          <svg
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <p
                  v-if="form.errors.options"
                  class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                  {{ form.errors.options }}
                </p>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                  Klik icon centang untuk menandai jawaban yang benar. Minimal 2 pilihan,
                  maksimal 6 pilihan.
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
                :href="route('quiz.questions.index', quiz.id)"
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
                <span v-else>Simpan Soal</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar - Tips -->
        <div class="lg:col-span-1">
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 sticky top-6"
          >
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
              <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                Tips Membuat Soal
              </h3>
            </div>

            <div class="p-6 space-y-4">
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
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-800 dark:text-white mb-1">
                    Format Kaya
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Gunakan bold, italic, warna untuk membuat soal lebih menarik
                  </p>
                </div>
              </div>

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
                    Pertanyaan Jelas
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Pastikan pertanyaan mudah dipahami dan tidak ambigu
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
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-800 dark:text-white mb-1">
                    Jawaban Benar
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Tandai minimal 1 jawaban benar dengan klik icon centang
                  </p>
                </div>
              </div>

              <div class="flex items-start">
                <div
                  class="flex-shrink-0 w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center mr-3"
                >
                  <svg
                    class="w-4 h-4 text-orange-600 dark:text-orange-400"
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
                    Pilihan Masuk Akal
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Buat pilihan jawaban yang relevan dan masuk akal
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </AdminLayout>
</template>