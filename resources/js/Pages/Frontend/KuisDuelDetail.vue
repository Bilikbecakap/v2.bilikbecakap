<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    quiz: Object,
    totalAttempts: Number,
    averageScore: Number,
});

// Form untuk 2 pemain
const form = useForm({
    player1_name: '',
    player2_name: '',
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const submitStart = () => {
    form.post(route('quiz-attempt.begin-duel', props.quiz.slug), {
        onError: (errors) => {
            console.log(errors);
        }
    });
};
</script>

<template>
    <Head :title="`${quiz.title} - Kuis Tantangan`" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <!-- Decorative -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-orange-500/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-red-500/20 rounded-full blur-3xl z-10"></div>

            <div class="container mx-auto px-6 relative z-20">
                <!-- Back Button -->
                <Link href="/kuis"
                    class="inline-flex items-center gap-2 text-orange-600 hover:text-orange-700 mb-6 text-sm font-medium">
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
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border-2 border-orange-200 overflow-hidden">
                            <!-- Hero Image -->
                            <div class="relative h-80 overflow-hidden bg-gradient-to-br from-orange-100 to-red-100">
                                <img 
                                    v-if="quiz.thumbnail" 
                                    :src="`/storage/${quiz.thumbnail}`" 
                                    :alt="quiz.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <div class="bg-white/80 rounded-xl w-32 h-32 flex items-center justify-center shadow-lg">
                                        <svg class="w-24 h-24 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Badges -->
                                <div class="absolute top-4 left-4 right-4 flex items-center justify-between">
                                    <!-- Type & Tantangan Badge -->
                                    <div class="flex items-center gap-2">
                                        <span :class="{
                                            'bg-[#54b0af]': quiz.type === 'umum',
                                            'bg-amber-500': quiz.type === 'modul'
                                        }" class="text-white text-sm font-bold px-4 py-2 rounded-full">
                                            {{ quiz.type === 'umum' ? '📝 Umum' : '📚 Modul' }}
                                        </span>
                                        
                                        <!-- Tantangan Badge -->
                                        <span class="bg-orange-500 text-white text-sm font-bold px-4 py-2 rounded-full flex items-center">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            🏆 TANTANGAN
                                        </span>
                                    </div>

                                    <!-- Questions Badge -->
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

                                <!-- Tantangan Info -->
                                <div class="p-6 bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-xl">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center shadow-lg">
                                                <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-bold text-orange-900 text-lg mb-2">🏆 Mode Tantangan - Tarik Tambang!</p>
                                            <ul class="text-sm text-orange-800 space-y-1.5">
                                                <li class="flex items-start gap-2">
                                                    <span class="text-orange-600 font-bold">•</span>
                                                    <span><strong>2 Pemain</strong> bermain di layar yang sama</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-orange-600 font-bold">•</span>
                                                    <span><strong>Jawab berbarengan</strong> untuk setiap soal</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-orange-600 font-bold">•</span>
                                                    <span><strong>Siapa cepat & benar</strong> akan tarik tali ke arahnya</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-orange-600 font-bold">•</span>
                                                    <span><strong>Menang jika</strong> berhasil jawab benar <strong>{{ Math.ceil(quiz.total_questions / 2) }} soal</strong> (50%+1)</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="text-orange-600 font-bold">•</span>
                                                    <span><strong>Animasi tarik tambang interaktif</strong> akan bergerak sesuai jawaban</span>
                                                </li>
                                            </ul>
                                        </div>
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

                        <!-- Panduan Bermain Tantangan -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8">
                            <h3 class="text-2xl font-bold text-[#002b44] mb-6 flex items-center gap-3">
                                <svg class="w-6 h-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Cara Bermain Tantangan
                            </h3>

                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white font-bold">1</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Masukkan Nama 2 Pemain</h4>
                                        <p class="text-gray-600">Player 1 (Kiri) dan Player 2 (Kanan) harus mengisi nama masing-masing.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white font-bold">2</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Soal Muncul di Tengah Layar</h4>
                                        <p class="text-gray-600">Kedua pemain akan melihat soal yang sama di bagian tengah atas.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white font-bold">3</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Jawab Berbarengan dari Sisi Masing-Masing</h4>
                                        <p class="text-gray-600">Player 1 menjawab dari kolom kiri, Player 2 dari kolom kanan. Ketik jawaban dengan keyboard virtual A-Z.</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white font-bold">4</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Tarik Tali dengan Jawaban Benar</h4>
                                        <p class="text-gray-600">Setiap jawaban benar akan menarik tali ke arah Anda. Perhatikan animasi tarik tambang!</p>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-orange-500 text-white font-bold">5</div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44]">Menang dengan {{ Math.ceil(quiz.total_questions / 2) }} Jawaban Benar</h4>
                                        <p class="text-gray-600">Pemain pertama yang berhasil menjawab benar {{ Math.ceil(quiz.total_questions / 2) }} soal (50%+1) adalah pemenangnya!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Sidebar (1/3) -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Form Input 2 Pemain -->
                        <form @submit.prevent="submitStart" class="bg-gradient-to-br from-[#54b0af] to-[#459a99] rounded-2xl shadow-2xl border border-white/20 p-8 text-white space-y-6">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-3 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <h3 class="text-2xl font-bold mb-2">🏆 Siap Bertanding?</h3>
                                <p class="text-sm opacity-90">
                                    Masukkan nama kedua pemain untuk memulai tantangan!
                                </p>
                            </div>

                            <!-- Player 1 Input -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold flex items-center gap-2">
                                    <span class="bg-white text-black px-2 py-0.5 rounded-full text-xs font-bold">P1</span>
                                    Nama Player 1 (Kiri) *
                                </label>
                                <input 
                                    v-model="form.player1_name"
                                    type="text" 
                                    placeholder="Masukkan nama Player 1"
                                    required
                                    class="w-full px-4 py-3 rounded-xl text-[#002b44] placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-white border-none"
                                />
                                <p v-if="form.errors.player1_name" class="text-sm text-red-200">
                                    {{ form.errors.player1_name }}
                                </p>
                            </div>

                            <!-- VS Divider -->
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t-2 border-white/30"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-4 bg-white text-orange-600 font-bold rounded-full text-lg shadow-lg">VS</span>
                                </div>
                            </div>

                            <!-- Player 2 Input -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold flex items-center gap-2">
                                    <span class="bg-white text-black px-2 py-0.5 rounded-full text-xs font-bold">P2</span>
                                    Nama Player 2 (Kanan) *
                                </label>
                                <input 
                                    v-model="form.player2_name"
                                    type="text" 
                                    placeholder="Masukkan nama Player 2"
                                    required
                                    class="w-full px-4 py-3 rounded-xl text-[#002b44] placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-white border-none"
                                />
                                <p v-if="form.errors.player2_name" class="text-sm text-red-200">
                                    {{ form.errors.player2_name }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <button 
                                type="submit"
                                :disabled="form.processing || !form.player1_name.trim() || !form.player2_name.trim()"
                                class="w-full bg-white hover:bg-gray-50 disabled:bg-gray-300 text-black font-bold py-4 px-6 rounded-xl text-center transition-all duration-300 transform hover:scale-105 disabled:hover:scale-100 shadow-lg">
                                <span class="flex items-center justify-center gap-2">
                                    <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-if="form.processing">Memulai...</span>
                                    <span v-else class="flex items-center gap-2">
                                        Mulai Tantangan
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                </span>
                            </button>

                            <!-- Info Checklist -->
                            <div class="text-sm space-y-2 border-t border-white/30 pt-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">{{ quiz.total_questions }} Soal</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">Format Tarik Tambang</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">Target {{ Math.ceil(quiz.total_questions / 2) }} Jawaban Benar</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm">2 Pemain - 1 Device</span>
                                </div>
                            </div>
                        </form>

                        <!-- Tips Card -->
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
                            <div class="flex gap-3">
                                <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.082 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <p class="font-semibold text-amber-900 mb-1">Tips Bermain</p>
                                    <p class="text-sm text-amber-800">
                                        Pastikan kedua pemain sudah siap di samping device. Jawab dengan cepat dan tepat untuk mengungguli lawan!
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
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>