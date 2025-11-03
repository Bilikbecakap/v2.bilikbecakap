<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount, watch } from "vue";

const props = defineProps({
  quiz: Object,
  attempt: Object,
  questions: Array,
});

const currentQuestionIndex = ref(0);
const timerInterval = ref(null);
const showSubmitModal = ref(false);
const isSubmitting = ref(false);

// Storage keys
const answersKey = `quiz_answers_${props.attempt.id}`;
const timerKey = `quiz_timer_${props.attempt.id}`;

// Load saved data
const loadSavedData = () => {
  try {
    const savedAnswers = localStorage.getItem(answersKey);
    const savedTimer = localStorage.getItem(timerKey);
    
    return {
      answers: savedAnswers ? JSON.parse(savedAnswers) : null,
      timeRemaining: savedTimer ? parseInt(savedTimer) : props.quiz.duration * 60
    };
  } catch (e) {
    console.error('Error loading saved data:', e);
    return {
      answers: null,
      timeRemaining: props.quiz.duration * 60
    };
  }
};

const savedData = loadSavedData();
const timeRemaining = ref(savedData.timeRemaining);

// Initialize form
const form = useForm({
  answers: savedData.answers || props.questions.map((question) => ({
    question_id: question.id,
    option_id: null,
  })),
});

// Auto-save answers dan timer
watch(() => form.answers, (newAnswers) => {
  try {
    localStorage.setItem(answersKey, JSON.stringify(newAnswers));
  } catch (e) {
    console.error('Error saving answers:', e);
  }
}, { deep: true });

watch(timeRemaining, (newTime) => {
  try {
    localStorage.setItem(timerKey, newTime.toString());
  } catch (e) {
    console.error('Error saving timer:', e);
  }
});

// Prevent page refresh/close
const handleBeforeUnload = (e) => {
  if (!isSubmitting.value) {
    e.preventDefault();
    e.returnValue = '';
    return '';
  }
};

onMounted(() => {
  startTimer();
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

const startTimer = () => {
  timerInterval.value = setInterval(() => {
    if (timeRemaining.value > 0) {
      timeRemaining.value--;
    } else {
      autoSubmit();
    }
  }, 1000);
};

const formattedTime = computed(() => {
  const minutes = Math.floor(timeRemaining.value / 60);
  const seconds = timeRemaining.value % 60;
  return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
});

const timePercentage = computed(() => {
  const totalSeconds = props.quiz.duration * 60;
  return (timeRemaining.value / totalSeconds) * 100;
});

const timeColor = computed(() => {
  if (timePercentage.value > 50) return "text-green-600 dark:text-green-400";
  if (timePercentage.value > 20) return "text-yellow-600 dark:text-yellow-400";
  return "text-red-600 dark:text-red-400";
});

const timeBarColor = computed(() => {
  if (timePercentage.value > 50) return "bg-green-500";
  if (timePercentage.value > 20) return "bg-yellow-500";
  return "bg-red-500";
});

const currentQuestion = computed(() => {
  return props.questions[currentQuestionIndex.value];
});

const currentAnswer = computed(() => {
  return form.answers[currentQuestionIndex.value];
});

const answeredCount = computed(() => {
  return form.answers.filter((answer) => answer.option_id !== null).length;
});

const unansweredCount = computed(() => {
  return props.questions.length - answeredCount.value;
});

const isAllAnswered = computed(() => {
  return form.answers.every((answer) => answer.option_id !== null);
});

const selectOption = (optionId) => {
  form.answers[currentQuestionIndex.value].option_id = optionId;
};

const goToQuestion = (index) => {
  currentQuestionIndex.value = index;
};

const nextQuestion = () => {
  if (currentQuestionIndex.value < props.questions.length - 1) {
    currentQuestionIndex.value++;
  }
};

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
  }
};

const confirmSubmit = () => {
  showSubmitModal.value = true;
};

const cancelSubmit = () => {
  showSubmitModal.value = false;
};

const clearStorage = () => {
  try {
    localStorage.removeItem(answersKey);
    localStorage.removeItem(timerKey);
  } catch (e) {
    console.error('Error clearing storage:', e);
  }
};

const submitQuiz = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }

  isSubmitting.value = true;

  // Kirim semua jawaban (termasuk yang null)
  // Backend yang akan handle filtering
  const payload = {
    answers: form.answers
  };

  form.post(route("quiz.attempt.submit", [props.quiz.id, props.attempt.id]), {
    data: payload,
    preserveScroll: true,
    onSuccess: () => {
      clearStorage();
    },
    onError: (errors) => {
      console.error('Submit error:', errors);
      isSubmitting.value = false;
      // Restart timer jika error
      startTimer();
    },
    onFinish: () => {
      showSubmitModal.value = false;
    }
  });
};

const autoSubmit = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }

  isSubmitting.value = true;

  const payload = {
    answers: form.answers
  };

  form.post(route("quiz.attempt.submit", [props.quiz.id, props.attempt.id]), {
    data: payload,
    preserveScroll: true,
    onSuccess: () => {
      clearStorage();
    },
    onError: (errors) => {
      console.error('Auto submit error:', errors);
    }
  });
};

const getOptionLabel = (index) => {
  return String.fromCharCode(65 + index);
};

const isOptionSelected = (optionId) => {
  return currentAnswer.value.option_id === optionId;
};

const isQuestionAnswered = (index) => {
  return form.answers[index].option_id !== null;
};
</script>

<template>
  <Head :title="`Quiz - ${quiz.title}`" />

  <AdminLayout>
    <template #title>Mengerjakan Quiz</template>

    <div class="space-y-6">
      <!-- Timer Card -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-4 sm:px-6 py-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h3 class="text-sm font-semibold text-slate-800 dark:text-white">
                {{ quiz.title }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ attempt.participant_name }}
              </p>
            </div>

            <div class="flex items-center gap-3">
              <div class="text-right">
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Waktu Tersisa</p>
                <p :class="['text-2xl font-bold', timeColor]">{{ formattedTime }}</p>
              </div>
              <div
                class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center"
              >
                <svg
                  class="w-7 h-7 text-slate-400"
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
            </div>
          </div>

          <div class="mt-4 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
            <div
              :class="['h-full transition-all duration-1000', timeBarColor]"
              :style="{ width: `${timePercentage}%` }"
            ></div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Question -->
        <div class="lg:col-span-3">
          <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
          >
            <div
              class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-t-xl"
            >
              <div class="flex items-center justify-between">
                <h3 class="text-base sm:text-lg font-semibold">
                  Soal {{ currentQuestionIndex + 1 }} dari {{ questions.length }}
                </h3>
                <span
                  v-if="isQuestionAnswered(currentQuestionIndex)"
                  class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm"
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
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Terjawab
                </span>
              </div>
            </div>

            <div class="p-4 sm:p-6">
              <div
                class="text-base sm:text-lg font-medium text-slate-900 dark:text-slate-100 mb-6 prose prose-sm sm:prose-lg dark:prose-invert max-w-none"
                v-html="currentQuestion.question"
              ></div>

              <div class="space-y-3">
                <button
                  v-for="(option, index) in currentQuestion.options"
                  :key="option.id"
                  @click="selectOption(option.id)"
                  :class="[
                    'w-full flex items-start p-3 sm:p-4 rounded-lg border-2 text-left transition-all duration-150',
                    isOptionSelected(option.id)
                      ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md'
                      : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50/50 dark:hover:bg-blue-900/10',
                  ]"
                >
                  <span
                    :class="[
                      'flex-shrink-0 inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full text-xs sm:text-sm font-bold mr-3 sm:mr-4',
                      isOptionSelected(option.id)
                        ? 'bg-blue-500 text-white'
                        : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
                    ]"
                  >
                    {{ getOptionLabel(index) }}
                  </span>
                  <span
                    :class="[
                      'flex-1 text-sm sm:text-base',
                      isOptionSelected(option.id)
                        ? 'text-slate-900 dark:text-slate-100 font-medium'
                        : 'text-slate-700 dark:text-slate-300',
                    ]"
                  >
                    {{ option.option_text }}
                  </span>
                  <svg
                    v-if="isOptionSelected(option.id)"
                    class="flex-shrink-0 w-5 h-5 sm:w-6 sm:h-6 text-blue-500 ml-2"
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
                </button>
              </div>

              <div class="mt-6 sm:mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                <!-- Mobile -->
                <div class="flex flex-col gap-3 sm:hidden">
                  <div class="flex gap-2">
                    <button
                      @click="previousQuestion"
                      :disabled="currentQuestionIndex === 0"
                      class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                      </svg>
                      Sebelumnya
                    </button>

                    <button
                      v-if="currentQuestionIndex < questions.length - 1"
                      @click="nextQuestion"
                      class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-150"
                    >
                      Selanjutnya
                      <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </div>

                  <button
                    @click="confirmSubmit"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Submit Quiz
                  </button>
                </div>

                <!-- Desktop -->
                <div class="hidden sm:flex items-center justify-between">
                  <button
                    @click="previousQuestion"
                    :disabled="currentQuestionIndex === 0"
                    class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Sebelumnya
                  </button>

                  <div class="flex items-center gap-3">
                    <button
                      v-if="currentQuestionIndex < questions.length - 1"
                      @click="nextQuestion"
                      class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-150"
                    >
                      Selanjutnya
                      <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>

                    <button
                      @click="confirmSubmit"
                      class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-200"
                    >
                      <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Submit Quiz
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigator -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 lg:sticky lg:top-6">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700">
              <h3 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-white">
                Navigasi Soal
              </h3>
              <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span>
                  <span class="font-semibold text-green-600 dark:text-green-400">{{ answeredCount }}</span>
                  Terjawab
                </span>
                <span>
                  <span class="font-semibold text-red-600 dark:text-red-400">{{ unansweredCount }}</span>
                  Belum
                </span>
              </div>
            </div>

            <div class="p-4">
              <div class="grid grid-cols-5 gap-2">
                <button
                  v-for="(question, index) in questions"
                  :key="question.id"
                  @click="goToQuestion(index)"
                  :class="[
                    'aspect-square rounded-lg text-sm font-semibold transition-all duration-150',
                    currentQuestionIndex === index
                      ? 'bg-blue-500 text-white ring-2 ring-blue-300 dark:ring-blue-600'
                      : isQuestionAnswered(index)
                      ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/30'
                      : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600',
                  ]"
                >
                  {{ index + 1 }}
                </button>
              </div>
            </div>

            <div class="px-4 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 space-y-2">
              <div class="flex items-center text-xs text-slate-600 dark:text-slate-400">
                <div class="w-6 h-6 rounded bg-blue-500 mr-2"></div>
                <span>Soal Aktif</span>
              </div>
              <div class="flex items-center text-xs text-slate-600 dark:text-slate-400">
                <div class="w-6 h-6 rounded bg-green-100 dark:bg-green-900/20 border border-green-300 dark:border-green-700 mr-2"></div>
                <span>Sudah Dijawab</span>
              </div>
              <div class="flex items-center text-xs text-slate-600 dark:text-slate-400">
                <div class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-700 mr-2"></div>
                <span>Belum Dijawab</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showSubmitModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="cancelSubmit"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100">
                Submit Quiz?
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">
                  Apakah Anda yakin ingin mengakhiri quiz ini? Pastikan semua jawaban sudah benar.
                </p>
                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600 dark:text-slate-400">Total Soal:</span>
                    <span class="font-semibold text-slate-800 dark:text-white">{{ questions.length }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600 dark:text-slate-400">Terjawab:</span>
                    <span class="font-semibold text-green-600 dark:text-green-400">{{ answeredCount }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600 dark:text-slate-400">Belum Dijawab:</span>
                    <span class="font-semibold text-red-600 dark:text-red-400">{{ unansweredCount }}</span>
                  </div>
                </div>
                <p v-if="!isAllAnswered" class="text-xs text-yellow-600 dark:text-yellow-400 mt-3">
                  ⚠️ Perhatian: Anda masih memiliki soal yang belum dijawab!
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              @click="submitQuiz"
              :disabled="form.processing"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Mengirim...</span>
              <span v-else>Ya, Submit</span>
            </button>
            <button
              @click="cancelSubmit"
              :disabled="form.processing"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto sm:text-sm"
            >
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
:deep(.prose) { color: inherit; }
:deep(.prose p) { margin-bottom: 0.5em; }
:deep(.prose strong) { font-weight: 600; }
:deep(.prose em) { font-style: italic; }
:deep(.prose ul), :deep(.prose ol) { margin-left: 1.5em; margin-bottom: 0.5em; }
:deep(.prose img) { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5em 0; }
</style>