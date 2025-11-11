<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    quiz: Object,
    totalAttempts: Number,
    averageScore: Number,
});

const form = useForm({
    participant_name: '',
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const submitStart = () => {
    form.post(route('quiz-attempt.begin', props.quiz.slug), {
        onError: (errors) => {
            console.log(errors);
        }
    });
};
</script>

<template>
    <Head :title="`${quiz.title} - Kuis`" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <!-- Decorative -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <div class="container mx-auto px-6 relative z-20">
                <!-- Back Button -->
                <Link href="/kuis"
                    class="inline-flex items-center gap-2 text-[#54b0af] hover:text-[#459a99] mb-6 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Kuis
                </Link>

                <!-- 2/3 + 1/3 Layout -->
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Left: Main Content (2/3) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Quiz Header Card -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 overflow-hidden">
                            <!-- Hero Image -->
                            <div class="relative h-80 overflow-hidden bg-gradient-to-br from-[#54b0af]/20 to-[#FCB415]/20">
                                <img 
                                    v-if="quiz.thumbnail" 
                                    :src="`/storage/${quiz.thumbnail}`" 
                                    :alt="quiz.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <div class="bg-white/80 rounded-xl w-32 h-32 flex items-center justify-center shadow-lg">
                                        <svg class="w-24 h-24 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Type & Questions Badge -->
                                <div class="absolute top-4 left-4 right-4 flex items-center justify-between">
                                    <span :class="{
                                        'bg-[#54b0af]': quiz.type === 'umum',
                                        'bg-amber-500': quiz.type === 'modul'
                                    }" class="text-white text-sm font-bold px-4 py-2 rounded-full">
                                        {{ quiz.type === 'umum' ? '📝 Umum' : '📚 Modul' }}
                                    </span>
                                    <span class="bg-white/90 backdrop-blur-sm text-[#002b44] text-sm font-bold px-4 py-2 rounded-full flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ quiz.total_questions }} Soal
                                    </span>
                                </div>
                            </div>

                            <!-- Title & Info -->
                            <div class="p-8 space-y-6">
                                <div>
                                    <h1 class="text-4xl font-bold text-[#002b44] mb-3">{{ quiz.title }}</h1>
                                    <p class="text-lg text-gray-600 leading-relaxed">
                                        {{ quiz.description || 'Tidak ada deskripsi kuis.' }}
                                    </p>
                                </div>

                                <!-- Quiz Info Grid -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gradient-to-r from-[#54b0af]/10 to-[#FCB415]/10 rounded-xl border border-white/20">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-[#54b0af]">{{ quiz.duration }}</div>
                                        <div class="text-sm text-gray-600 mt-1">Menit</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-[#54b0af]">{{ quiz.total_questions }}</div>
                                        <div class="text-sm text-gray-600 mt-1">Soal</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-[#54b0af]">{{ totalAttempts }}</div>
                                        <div class="text-sm text-gray-600 mt-1">Mengerjakan</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-[#54b0af]">{{ averageScore }}%</div>
                                        <div class="text-sm text-gray-600 mt-1">Rata-rata</div>
                                    </div>
                                </div>

                                <!-- Modul Info (jika quiz tipe modul) -->
                                <div v-if="quiz.type === 'modul' && quiz.modul_pembelajaran" class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-blue-900 mb-1">Baca terlebih dahulu Modul berikut untuk mengerjakan kuis ini</p>
                                            <a :href="`/pembelajaran/${quiz.modul_pembelajaran.slug}`" class="text-blue-600 hover:text-blue-700 font-medium">
                                                {{ quiz.modul_pembelajaran.title }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panduan Kuis -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8">
                            <h3 class="text-2xl font-bold text-[#002b44] mb-6 flex items-center gap-3">
                                <svg class="w-6 h-6 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Panduan Mengerjakan Kuis
                            </h3>

                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-[#54b0af] text-white font-bold">1</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Masukkan Nama Anda</h4>
                                        <p class="text-gray-600">Nama Anda akan digunakan untuk mencatat hasil kuis.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-[#54b0af] text-white font-bold">2</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Baca Setiap Soal dengan Cermat</h4>
                                        <p class="text-gray-600">Pastikan Anda memahami pertanyaan sebelum memilih jawaban.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-[#54b0af] text-white font-bold">3</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Pilih Jawaban Terbaik</h4>
                                        <p class="text-gray-600">Anda dapat mengubah jawaban selama kuis masih berlangsung.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-[#54b0af] text-white font-bold">4</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Selesaikan dalam Waktu yang Ditentukan</h4>
                                        <p class="text-gray-600">Kuis harus selesai dalam {{ quiz.duration }} menit. Waktu akan berjalan mundur.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-[#54b0af] text-white font-bold">5</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Lihat Hasil dan Pembahasan</h4>
                                        <p class="text-gray-600">Setelah selesai, Anda akan melihat skor dan pembahasan jawaban.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Sidebar (1/3) -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Form Input Nama -->
                        <form @submit.prevent="submitStart" class="bg-gradient-to-br from-[#54b0af] to-[#459a99] rounded-2xl shadow-2xl border border-white/20 p-8 text-white space-y-6">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-3 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                <h3 class="text-2xl font-bold mb-2">Siap Mengerjakan?</h3>
                                <p class="text-sm opacity-90">
                                    Masukkan nama Anda untuk memulai kuis ini.
                                </p>
                            </div>

                            <!-- Input Nama -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold">Nama Peserta *</label>
                                <input 
                                    v-model="form.participant_name"
                                    type="text" 
                                    placeholder="Masukkan nama lengkap Anda"
                                    required
                                    class="w-full px-4 py-3 rounded-xl text-[#002b44] placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-white border-none"
                                />
                                <p v-if="form.errors.participant_name" class="text-sm text-red-200">
                                    {{ form.errors.participant_name }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <button 
                                type="submit"
                                :disabled="form.processing || !form.participant_name.trim()"
                                class="w-full bg-white hover:bg-gray-50 disabled:bg-gray-300 text-[#54b0af] font-bold py-4 px-6 rounded-xl text-center transition-all duration-300 transform hover:scale-105 disabled:hover:scale-100">
                                <span class="flex items-center justify-center gap-2">
                                    <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-if="form.processing">Memulai...</span>
                                    <span v-else>
                                        Mulai Mengerjakan Kuis
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                </span>
                            </button>

                            <div class="text-sm space-y-2 border-t border-white/30 pt-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">{{ quiz.total_questions }} Soal Pilihan Ganda</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">{{ quiz.duration }} Menit Durasi</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">Hasil Instan Setelah Selesai</span>
                                </div>
                            </div>
                        </form>

                        <!-- Stats Card -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-6 space-y-4">
                            <h4 class="font-bold text-[#002b44] flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Statistik Kuis
                            </h4>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-600">Total Peserta</span>
                                    <span class="font-bold text-[#54b0af] text-lg">{{ totalAttempts }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-600">Rata-rata Skor</span>
                                    <span class="font-bold text-[#54b0af] text-lg">{{ averageScore }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
                            <div class="flex gap-3">
                                <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4v2m0-6a4 4 0 100 8 4 4 0 000-8zm0-5a9 9 0 100 18 9 9 0 000-18z" />
                                </svg>
                                <div>
                                    <p class="font-semibold text-amber-900 mb-1">Tips</p>
                                    <p class="text-sm text-amber-800">
                                        Pastikan koneksi internet Anda stabil. Kuis tidak dapat dilanjutkan jika koneksi terputus.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
/* Custom styling jika diperlukan */
</style>