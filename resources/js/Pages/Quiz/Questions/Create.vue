<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import QuillEditor from "@/Components/QuillEditor.vue";

const props = defineProps({
  quiz: Object,
  nextOrder: Number,
});

const form = useForm({
  question: "",
  question_type: "multiple_choice",
  order: props.nextOrder,
  options: [
    { option_text: "", is_correct: false, order: 0 },
    { option_text: "", is_correct: false, order: 1 },
    { option_text: "", is_correct: false, order: 2 },
    { option_text: "", is_correct: false, order: 3 },
  ],
  correct_answer: "",
});

// Watch question type changes
watch(() => form.question_type, (newType) => {
  if (newType === 'fill_blank') {
    form.options = [];
    form.correct_answer = ""; // Reset
  } else if (newType === 'multiple_choice' && form.options.length === 0) {
    form.options = [
      { option_text: "", is_correct: false, order: 0 },
      { option_text: "", is_correct: false, order: 1 },
      { option_text: "", is_correct: false, order: 2 },
      { option_text: "", is_correct: false, order: 3 },
    ];
    form.correct_answer = ""; // Reset
  }
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

// ✅ CLIENT-SIDE VALIDATION
const canSubmit = computed(() => {
  // Cek pertanyaan tidak kosong
  if (!form.question || !form.question.trim()) return false;
  
  if (form.question_type === 'multiple_choice') {
    // Minimal 2 options
    if (form.options.length < 2) return false;
    
    // Semua option harus terisi
    const allFilled = form.options.every(opt => opt.option_text && opt.option_text.trim());
    if (!allFilled) return false;
    
    // Minimal 1 jawaban benar
    const hasCorrect = form.options.some(opt => opt.is_correct);
    if (!hasCorrect) return false;
    
    return true;
  } else if (form.question_type === 'fill_blank') {
    // Correct answer harus terisi
    return form.correct_answer && form.correct_answer.trim();
  }
  
  return false;
});

const submit = () => {
  // ✅ CLEAN DATA sebelum submit
  const submitData = {
    question: form.question,
    question_type: form.question_type,
    order: form.order,
  };

  if (form.question_type === 'multiple_choice') {
    submitData.options = form.options;
  } else if (form.question_type === 'fill_blank') {
    submitData.correct_answer = form.correct_answer.trim();
  }

  form.post(route("quiz.questions.store", props.quiz.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by controller
    },
  });
};

const getOptionLabel = (index) => {
  return String.fromCharCode(65 + index);
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
              <!-- Tipe Soal -->
              <div class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-700">
                <label class="block text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3">
                  Tipe Soal <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <!-- Multiple Choice -->
                  <label
                    :class="[
                      'flex items-center p-4 rounded-lg border-2 cursor-pointer transition-all',
                      form.question_type === 'multiple_choice'
                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                        : 'border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-600'
                    ]"
                  >
                    <input
                      v-model="form.question_type"
                      type="radio"
                      value="multiple_choice"
                      class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                    />
                    <div class="ml-3">
                      <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Pilihan Ganda</span>
                      </div>
                      <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">A, B, C, D (max 6)</p>
                    </div>
                  </label>

                  <!-- Fill Blank -->
                  <label
                    :class="[
                      'flex items-center p-4 rounded-lg border-2 cursor-pointer transition-all',
                      form.question_type === 'fill_blank'
                        ? 'border-green-500 bg-green-50 dark:bg-green-900/30'
                        : 'border-slate-200 dark:border-slate-700 hover:border-green-300 dark:hover:border-green-600'
                    ]"
                  >
                    <input
                      v-model="form.question_type"
                      type="radio"
                      value="fill_blank"
                      class="w-4 h-4 text-green-600 focus:ring-green-500"
                    />
                    <div class="ml-3">
                      <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Isian Singkat</span>
                      </div>
                      <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Jawaban teks bebas</p>
                    </div>
                  </label>
                </div>
                <p v-if="form.errors.question_type" class="mt-2 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.question_type }}
                </p>
              </div>

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

              <!-- CONDITIONAL: Multiple Choice Options -->
              <div v-if="form.question_type === 'multiple_choice'">
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

                      <div class="flex-shrink-0 flex items-center gap-2">
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

              <!-- CONDITIONAL: Fill Blank Answer -->
              <div v-else-if="form.question_type === 'fill_blank'" class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border-2 border-green-200 dark:border-green-700">
                <label
                  for="correct_answer"
                  class="block text-sm font-medium text-green-900 dark:text-green-100 mb-2"
                >
                  Jawaban yang Benar <span class="text-red-500">*</span>
                </label>
                <input
                  id="correct_answer"
                  v-model="form.correct_answer"
                  type="text"
                  placeholder="Masukkan jawaban yang benar (case-insensitive)"
                  class="w-full px-4 py-2 border border-green-300 dark:border-green-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-slate-700 dark:text-white"
                  :class="{
                    'border-red-500 dark:border-red-500': form.errors.correct_answer,
                  }"
                />
                <p class="mt-2 text-xs text-green-700 dark:text-green-300">
                  💡 Jawaban akan dicocokkan tanpa memperhatikan huruf besar/kecil. Contoh: "Sedang" = "sedang" = "SEDANG"
                </p>
                <p v-if="form.errors.correct_answer" class="mt-2 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.correct_answer }}
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
                :disabled="form.processing || !canSubmit"
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
                    Pilih Tipe Soal
                  </p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">
                    Pilihan ganda untuk opsi A-D, atau isian untuk jawaban bebas
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
                    Tandai jawaban benar dengan icon centang atau isi jawaban isian
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