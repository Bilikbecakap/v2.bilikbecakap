<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    quiz: Object,
    attempt: Object,
    questions: Array,
});

const form = useForm({
    answers: [],
});

const currentQuestionIndex = ref(0);
const timerInterval = ref(null);
const showSubmitModal = ref(false);
const isSubmitting = ref(false);

// Audio player refs
const audioPlayer = ref(null);
const isMusicPlaying = ref(false);
const isMusicMuted = ref(false);

// Sound effect refs
const choiceSoundEffect = ref(null);

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

// Initialize answers IMMEDIATELY (before onMounted)
const initializeAnswers = () => {
  if (!form.answers || form.answers.length === 0) {
    form.answers = savedData.answers || props.questions.map((question) => ({
      question_id: question.id,
      option_id: null,
    }));
  }
};

// Call it right away
initializeAnswers();

onMounted(() => {
  startTimer();
  // Delay sedikit untuk memastikan DOM ready
  setTimeout(() => {
    initAudio();
    initSoundEffects();
  }, 100);
  window.addEventListener('beforeunload', handleBeforeUnload);
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

// Check if quiz has music from master data
const hasMusic = computed(() => {
  return props.quiz.master_media_music_quiz && props.quiz.master_media_music_quiz.audio_url;
});

const musicUrl = computed(() => {
  return props.quiz.master_media_music_quiz?.audio_url || null;
});

const musicFileName = computed(() => {
  if (!props.quiz.master_media_music_quiz?.audio) return '';
  return props.quiz.master_media_music_quiz.audio.split('/').pop();
});

// Music controls
const toggleMusic = () => {
  if (!audioPlayer.value) return;
  
  if (isMusicPlaying.value) {
    audioPlayer.value.pause();
    isMusicPlaying.value = false;
  } else {
    audioPlayer.value.play().catch(e => {
      console.error('Error playing audio:', e);
    });
    isMusicPlaying.value = true;
  }
};

const toggleMute = () => {
  if (!audioPlayer.value) return;
  
  audioPlayer.value.muted = !audioPlayer.value.muted;
  isMusicMuted.value = audioPlayer.value.muted;
};

const initAudio = () => {
  if (hasMusic.value && audioPlayer.value) {
    console.log('Initializing audio:', musicUrl.value);
    
    // Set volume default
    audioPlayer.value.volume = 0.3;
    
    // Loop audio
    audioPlayer.value.loop = true;
    
    // Auto play dengan handling error
    audioPlayer.value.play().then(() => {
      isMusicPlaying.value = true;
      console.log('Audio playing successfully');
    }).catch(e => {
      console.log('Autoplay prevented. User needs to interact first.', e);
      isMusicPlaying.value = false;
    });
    
    // Event listeners
    audioPlayer.value.addEventListener('play', () => {
      isMusicPlaying.value = true;
    });
    
    audioPlayer.value.addEventListener('pause', () => {
      isMusicPlaying.value = false;
    });

    audioPlayer.value.addEventListener('error', (e) => {
      console.error('Audio error:', e);
    });

    audioPlayer.value.addEventListener('loadedmetadata', () => {
      console.log('Audio metadata loaded');
    });
  }
};

const initSoundEffects = () => {
  if (choiceSoundEffect.value) {
    choiceSoundEffect.value.volume = 0.5;
  }
};

const playChoiceSound = () => {
  if (choiceSoundEffect.value) {
    choiceSoundEffect.value.currentTime = 0;
    choiceSoundEffect.value.play().catch(e => {
      console.error('Error playing choice sound:', e);
    });
  }
};

const stopAudio = () => {
  if (audioPlayer.value) {
    audioPlayer.value.pause();
    audioPlayer.value.currentTime = 0;
    isMusicPlaying.value = false;
  }
};

// Prevent page refresh/close
const handleBeforeUnload = (e) => {
  if (!isSubmitting.value) {
    e.preventDefault();
    e.returnValue = '';
    return '';
  }
};

onBeforeUnmount(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }
  stopAudio();
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
  }
  stopAudio();
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
  if (timePercentage.value > 50) return "text-green-600";
  if (timePercentage.value > 20) return "text-yellow-600";
  return "text-red-600";
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
  return form.answers && form.answers[currentQuestionIndex.value];
});

const answeredCount = computed(() => {
  return form.answers ? form.answers.filter((answer) => answer.option_id !== null).length : 0;
});

const unansweredCount = computed(() => {
  return props.questions.length - answeredCount.value;
});

const isAllAnswered = computed(() => {
  return form.answers && form.answers.every((answer) => answer.option_id !== null);
});

const selectOption = (optionId) => {
  if (form.answers && form.answers[currentQuestionIndex.value]) {
    form.answers[currentQuestionIndex.value].option_id = optionId;
    playChoiceSound();
  }
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

  stopAudio();

  isSubmitting.value = true;

  const payload = {
    answers: form.answers
  };

  form.post(route('quiz-attempt.submit', props.quiz.slug), {
    data: payload,
    preserveScroll: true,
    onSuccess: () => {
      clearStorage();
    },
    onError: (errors) => {
      console.error('Submit error:', errors);
      isSubmitting.value = false;
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

  stopAudio();

  isSubmitting.value = true;

  const payload = {
    answers: form.answers
  };

  form.post(route('quiz-attempt.submit', props.quiz.slug), {
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
  return currentAnswer.value && currentAnswer.value.option_id === optionId;
};

const isQuestionAnswered = (index) => {
  return form.answers && form.answers[index] && form.answers[index].option_id !== null;
};
</script>

<template>
  <Head :title="`${quiz.title} - Kerjakan Kuis`" />

  <FrontendLayout>
    <!-- Hidden Audio Player dengan URL dari Master Data -->
    <audio
      v-if="hasMusic"
      ref="audioPlayer"
      :src="musicUrl"
      preload="auto"
      class="hidden"
      @error="(e) => console.error('Audio loading error:', e)"
      @loadedmetadata="() => console.log('Audio metadata loaded successfully')"
    >
      Your browser does not support the audio element.
    </audio>

    <!-- Choice Sound Effect -->
    <audio ref="choiceSoundEffect" src="/background/choice-05-199276.mp3" preload="auto" class="hidden"></audio>

    <div class="min-h-screen py-8 relative overflow-hidden">
      <!-- Background -->
      <div class="absolute inset-0 z-0">
        <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
      </div>

      <!-- Decorative -->
      <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

      <!-- Timer Card - Sticky Header -->
      <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm shadow-lg border-b border-slate-200">
        <div class="container mx-auto px-6 py-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
              <div>
                <h3 class="text-sm font-semibold text-slate-800">
                  {{ quiz.title }}
                </h3>
                <p class="text-xs text-slate-500">
                  Peserta: {{ attempt.participant_name }}
                </p>
              </div>

              <!-- Music Controls -->
              <div v-if="hasMusic" class="flex items-center gap-2 ml-4 p-2 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
                <!-- Play/Pause Button -->
                <button
                  @click="toggleMusic"
                  class="p-2 rounded-lg bg-blue-100 hover:bg-blue-200 transition-colors"
                  :title="isMusicPlaying ? 'Pause Music' : 'Play Music'"
                >
                  <svg
                    v-if="!isMusicPlaying"
                    class="w-5 h-5 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                  </svg>
                  <svg
                    v-else
                    class="w-5 h-5 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z"/>
                  </svg>
                </button>

                <!-- Mute/Unmute Button -->
                <button
                  @click="toggleMute"
                  class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors"
                  :title="isMusicMuted ? 'Unmute' : 'Mute'"
                >
                  <svg
                    v-if="!isMusicMuted"
                    class="w-5 h-5 text-slate-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M10 3.75a.75.75 0 00-1.264-.546L4.703 7H3.167a.75.75 0 00-.7.48A6.985 6.985 0 002 10c0 .887.165 1.737.468 2.52.111.29.39.48.7.48h1.535l4.033 3.796A.75.75 0 0010 16.25V3.75zM15.95 5.05a.75.75 0 00-1.06 1.061 5.5 5.5 0 010 7.778.75.75 0 001.06 1.06 7 7 0 000-9.899z"/>
                    <path d="M13.829 7.172a.75.75 0 00-1.061 1.06 2.5 2.5 0 010 3.536.75.75 0 001.06 1.06 4 4 0 000-5.656z"/>
                  </svg>
                  <svg
                    v-else
                    class="w-5 h-5 text-slate-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M10 3.75a.75.75 0 00-1.264-.546L4.703 7H3.167a.75.75 0 00-.7.48A6.985 6.985 0 002 10c0 .887.165 1.737.468 2.52.111.29.39.48.7.48h1.535l4.033 3.796A.75.75 0 0010 16.25V3.75zM15.28 6.22a.75.75 0 10-1.06 1.06L15.44 8.5l-1.22 1.22a.75.75 0 001.06 1.06l1.22-1.22 1.22 1.22a.75.75 0 001.06-1.06L17.56 8.5l1.22-1.22a.75.75 0 00-1.06-1.06l-1.22 1.22-1.22-1.22z"/>
                  </svg>
                </button>

                <!-- Music Info -->
                <div class="flex items-center gap-1.5 px-2">
                  <svg
                    class="w-4 h-4 text-blue-500"
                    :class="{ 'animate-pulse': isMusicPlaying, 'opacity-50': !isMusicPlaying }"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                  </svg>
                  <div class="text-xs">
                    <p class="font-medium text-slate-700 leading-none">
                      {{ isMusicPlaying ? 'Playing' : 'Paused' }}
                    </p>
                    <p class="text-slate-500 text-[10px] leading-none mt-0.5 truncate max-w-[100px]" :title="musicFileName">
                      {{ musicFileName }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div class="text-right">
                <p class="text-xs text-slate-500 mb-1">Waktu Tersisa</p>
                <p :class="['text-2xl font-bold', timeColor]">{{ formattedTime }}</p>
              </div>
              <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="mt-4 w-full bg-slate-200 rounded-full h-2 overflow-hidden">
            <div
              :class="['h-full transition-all duration-1000', timeBarColor]"
              :style="{ width: `${timePercentage}%` }"
            ></div>
          </div>
        </div>
      </div>

      <div class="container mx-auto px-6 py-8 relative z-20" v-if="form.answers && form.answers.length > 0">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
          <!-- Question -->
          <div class="lg:col-span-3">
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20">
              <div class="px-4 sm:px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white rounded-t-xl">
                <div class="flex items-center justify-between">
                  <h3 class="text-base sm:text-lg font-semibold">
                    Soal {{ currentQuestionIndex + 1 }} dari {{ questions.length }}
                  </h3>
                  <span
                    v-if="isQuestionAnswered(currentQuestionIndex)"
                    class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm"
                  >
                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Terjawab
                  </span>
                </div>
              </div>

              <div class="p-4 sm:p-6">
                <!-- Question Text dengan HTML render -->
                <div
                  class="text-base sm:text-lg font-medium text-slate-900 mb-6 prose prose-sm sm:prose-lg max-w-none"
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
                        ? 'border-[#54b0af] bg-[#54b0af]/10 shadow-md'
                        : 'border-slate-200 bg-slate-50 hover:border-[#54b0af]/50 hover:bg-[#54b0af]/5',
                    ]"
                  >
                    <span
                      :class="[
                        'flex-shrink-0 inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full text-xs sm:text-sm font-bold mr-3 sm:mr-4',
                        isOptionSelected(option.id)
                          ? 'bg-[#54b0af] text-white'
                          : 'bg-slate-200 text-slate-600',
                      ]"
                    >
                      {{ getOptionLabel(index) }}
                    </span>
                    <span
                      :class="[
                        'flex-1 text-sm sm:text-base',
                        isOptionSelected(option.id)
                          ? 'text-slate-900 font-medium'
                          : 'text-slate-700',
                      ]"
                    >
                      {{ option.option_text }}
                    </span>
                    <svg
                      v-if="isOptionSelected(option.id)"
                      class="flex-shrink-0 w-5 h-5 sm:w-6 sm:h-6 text-[#54b0af] ml-2"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                </div>

                <div class="mt-6 sm:mt-8 pt-6 border-t border-slate-200">
                  <!-- Mobile Navigation -->
                  <div class="flex flex-col gap-3 sm:hidden">
                    <div class="flex gap-2">
                      <button
                        @click="previousQuestion"
                        :disabled="currentQuestionIndex === 0"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Sebelumnya
                      </button>

                      <button
                        v-if="currentQuestionIndex < questions.length - 1"
                        @click="nextQuestion"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-[#54b0af] text-white text-sm font-medium rounded-lg hover:bg-[#459a99] transition-colors duration-150"
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
                      Submit Kuis
                    </button>
                  </div>

                  <!-- Desktop Navigation -->
                  <div class="hidden sm:flex items-center justify-between">
                    <button
                      @click="previousQuestion"
                      :disabled="currentQuestionIndex === 0"
                      class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
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
                        class="inline-flex items-center px-4 py-2 bg-[#54b0af] text-white text-sm font-medium rounded-lg hover:bg-[#459a99] transition-colors duration-150"
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
                        Submit Kuis
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigator Sidebar -->
          <div class="lg:col-span-1">
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20 lg:sticky lg:top-28">
              <div class="px-4 sm:px-6 py-4 border-b border-slate-200">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800">
                  Navigasi Soal
                </h3>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                  <span>
                    <span class="font-semibold text-green-600">{{ answeredCount }}</span>
                    Terjawab
                  </span>
                  <span>
                    <span class="font-semibold text-red-600">{{ unansweredCount }}</span>
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
                        ? 'bg-[#54b0af] text-white ring-2 ring-[#54b0af]/30'
                        : isQuestionAnswered(index)
                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                    ]"
                  >
                    {{ index + 1 }}
                  </button>
                </div>
              </div>

              <div class="px-4 sm:px-6 py-4 border-t border-slate-200 space-y-2">
                <div class="flex items-center text-xs text-slate-600">
                  <div class="w-6 h-6 rounded bg-[#54b0af] mr-2"></div>
                  <span>Soal Aktif</span>
                </div>
                <div class="flex items-center text-xs text-slate-600">
                  <div class="w-6 h-6 rounded bg-green-100 border border-green-700 mr-2"></div>
                  <span>Sudah Dijawab</span>
                </div>
                <div class="flex items-center text-xs text-slate-600">
                  <div class="w-6 h-6 rounded bg-slate-100 mr-2"></div>
                  <span>Belum Dijawab</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Confirmation Modal -->
    <div v-if="showSubmitModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="cancelSubmit"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-slate-900">
                Submit Kuis?
              </h3>
              <div class="mt-2">
                <p class="text-sm text-slate-500 mb-3">
                  Apakah Anda yakin ingin mengakhiri kuis ini? Pastikan semua jawaban sudah benar.
                </p>
                <div class="bg-slate-50 rounded-lg p-3 space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Total Soal:</span>
                    <span class="font-semibold text-slate-800">{{ questions.length }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Terjawab:</span>
                    <span class="font-semibold text-green-600">{{ answeredCount }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Belum Dijawab:</span>
                    <span class="font-semibold text-red-600">{{ unansweredCount }}</span>
                  </div>
                </div>
                <p v-if="!isAllAnswered" class="text-xs text-yellow-600 mt-3">
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
              class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto sm:text-sm"
            >
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </FrontendLayout>
</template>

<style scoped>
:deep(.prose) { color: inherit; }
:deep(.prose p) { margin-bottom: 0.5em; }
:deep(.prose strong) { font-weight: 600; }
:deep(.prose em) { font-style: italic; }
:deep(.prose ul), :deep(.prose ol) { margin-left: 1.5em; margin-bottom: 0.5em; }
:deep(.prose img) { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5em 0; }
</style>