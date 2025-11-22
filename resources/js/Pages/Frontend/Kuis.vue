<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';

const props = defineProps({
    quizzes: Object,
    search: String,
    type: String,
    is_duel_enabled: String,
});

const searchQuery = ref(props.search || '');
const selectedType = ref(props.type || '');
const selectedDuelMode = ref(props.is_duel_enabled || '');

// Data Kuis Inovatif (Statis)
const innovativeQuizzes = ref([
    {
        id: 'pelafalan-kata',
        title: 'Kuis Pelafalan Kata',
        description: 'Uji kemampuan pelafalan Anda dengan teknologi speech recognition. Ucapkan kata dalam Bahasa Melayu Belitung dengan benar!',
        slug: 'pelafalan-kata',
        route: 'kuis-inovatif.pelafalan-kata',
        icon: '🎤',
        gradient: 'from-red-500 to-pink-500',
        borderColor: 'border-red-200',
        bgGradient: 'from-red-50 to-pink-50',
        questions: 10,
        duration: 15,
        type: 'inovatif'
    },
    {
        id: 'dengar-jawab',
        title: 'Kuis Dengar & Jawab',
        description: 'Dengarkan audio dalam Bahasa Melayu Belitung dan pilih jawaban yang tepat. Latih kemampuan mendengar Anda!',
        slug: 'dengar-jawab',
        route: 'kuis-inovatif.dengar-jawab',
        icon: '🎧',
        gradient: 'from-purple-500 to-pink-500',
        borderColor: 'border-purple-200',
        bgGradient: 'from-purple-50 to-pink-50',
        questions: 10,
        duration: 10,
        type: 'inovatif'
    },
    {
        id: 'cocokkan-kata',
        title: 'Kuis Cocokkan Kata',
        description: 'Cocokkan kata Bahasa Melayu Belitung dengan artinya dalam Bahasa Indonesia. Interaktif dengan drag & drop!',
        slug: 'cocokkan-kata',
        route: 'kuis-inovatif.cocokkan-kata',
        icon: '🎯',
        gradient: 'from-blue-500 to-cyan-500',
        borderColor: 'border-blue-200',
        bgGradient: 'from-blue-50 to-cyan-50',
        questions: 8,
        duration: 12,
        type: 'inovatif'
    }
]);

const handleSearch = () => {
    applyFilters();
};

const applyFilters = () => {
    router.get(
        '/kuis',
        {
            search: searchQuery.value || undefined,
            type: selectedType.value || undefined,
            is_duel_enabled: selectedDuelMode.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const clearSearch = () => {
    searchQuery.value = '';
    applyFilters();
};

// Reset filters saat search dihapus
watch(searchQuery, () => {
    if (!searchQuery.value) clearSearch();
});
</script>

<template>
    <Head title="Kuis - Bilikbecakap" />

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
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-[#54b0af] mb-4 drop-shadow-sm">
                        Kuis Pembelajaran Bahasa Belitung
                    </h1>
                    <p class="text-lg text-[#002b44]/80 max-w-2xl mx-auto drop-shadow-sm">
                        Uji kemampuan Anda dengan mengerjakan kuis interaktif tentang Bahasa dan Budaya Belitung
                    </p>
                </div>

                <!-- Search & Filters -->
                <div class="max-w-4xl mx-auto mb-12 space-y-6">
                    <!-- Search Bar -->
                    <form @submit.prevent="handleSearch" class="relative">
                        <input v-model="searchQuery" type="text"
                            placeholder="Cari judul kuis..."
                            class="w-full px-6 py-4 pr-14 rounded-2xl border-2 border-white/50 bg-white/90 backdrop-blur-sm text-[#002b44] placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent shadow-lg" />
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-[#FCB415] hover:bg-[#e0a013] text-white p-3 rounded-xl transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>

                    <!-- Unified Filter: Tipe Quiz -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-sm font-medium text-[#002b44]/70">Tipe Quiz:</span>
                        <button @click="selectedType = ''; selectedDuelMode = ''; applyFilters()"
                            :class="!selectedType && !selectedDuelMode ? 'bg-[#54b0af] text-white' : 'bg-white/80 text-[#002b44] hover:bg-white'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors border border-white/30">
                            Semua
                        </button>
                        <button @click="selectedType = 'umum'; selectedDuelMode = ''; applyFilters()"
                            :class="selectedType === 'umum' && !selectedDuelMode ? 'bg-[#54b0af] text-white' : 'bg-white/80 text-[#002b44] hover:bg-white'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors border border-white/30">
                            Umum
                        </button>
                        <button @click="selectedType = 'modul'; selectedDuelMode = ''; applyFilters()"
                            :class="selectedType === 'modul' && !selectedDuelMode ? 'bg-[#54b0af] text-white' : 'bg-white/80 text-[#002b44] hover:bg-white'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors border border-white/30">
                            Modul
                        </button>
                        <!-- TAMBAH: Tantangan (Duel Mode) -->
                        <button @click="selectedType = ''; selectedDuelMode = '1'; applyFilters()"
                            :class="selectedDuelMode === '1' ? 'bg-[#54b0af] text-white' : 'bg-white/80 text-[#002b44] hover:bg-white'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors border border-white/30 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Tantangan
                        </button>
                    </div>

                    <!-- Active Filter Info -->
                    <div v-if="search || type || is_duel_enabled" class="flex flex-wrap items-center gap-3 text-sm text-[#002b44]/70">
                        <span v-if="search">Pencarian: <span class="font-semibold">"{{ search }}"</span></span>
                        <span v-if="type">
                            Tipe: <span class="font-semibold">{{ type === 'umum' ? 'Umum' : 'Modul' }}</span>
                        </span>
                        <span v-if="is_duel_enabled === '1'">
                            Tipe: <span class="font-semibold">Tantangan</span>
                        </span>
                        <button @click="searchQuery = ''; selectedType = ''; selectedDuelMode = ''; applyFilters()"
                            class="text-[#54b0af] hover:text-[#459a99] font-medium">
                            Hapus filter
                        </button>
                    </div>
                </div>

                <!-- Grid Kuis (Gabungan Database + Inovatif) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-12">
                    <!-- Kuis dari Database -->
                    <div v-for="quiz in quizzes.data" :key="quiz.id"
                        class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group">
                        
                        <!-- Quiz Thumbnail/Image -->
                        <div class="relative h-48 bg-gradient-to-br from-[#54b0af]/20 to-[#FCB415]/20 overflow-hidden">
                            <img 
                                v-if="quiz.thumbnail" 
                                :src="`/storage/${quiz.thumbnail}`" 
                                :alt="quiz.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="flex items-center justify-center h-full">
                                <div class="bg-white/80 rounded-xl w-20 h-20 flex items-center justify-center shadow-md">
                                    <svg class="w-12 h-12 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Type Badge -->
                            <div class="absolute top-4 right-4">
                                <span :class="{
                                    'bg-[#54b0af]': quiz.type === 'umum',
                                    'bg-amber-500': quiz.type === 'modul'
                                }" class="text-white text-xs font-bold px-3 py-1 rounded-full">
                                    {{ quiz.type === 'umum' ? 'Umum' : 'Modul' }}
                                </span>
                            </div>

                            <!-- Tantangan Badge (jika duel enabled) -->
                            <div v-if="quiz.is_duel_enabled" class="absolute top-4 left-4">
                                <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    TANTANGAN
                                </span>
                            </div>

                            <!-- Questions Count Badge -->
                            <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm text-[#002b44] text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ quiz.total_questions }} soal
                            </div>
                        </div>

                        <!-- Quiz Content -->
                        <div class="p-5 space-y-4">
                            <!-- Title -->
                            <a :href="`/kuis/${quiz.slug}`" class="block hover:text-[#54b0af] transition-colors">
                                <h3 class="font-bold text-[#002b44] text-lg line-clamp-2">{{ quiz.title }}</h3>
                            </a>

                            <!-- Description -->
                            <p class="text-sm text-gray-600 line-clamp-2">
                                {{ quiz.description || 'Tidak ada deskripsi.' }}
                            </p>

                            <!-- Quiz Info Row -->
                            <div class="flex items-center justify-between text-xs text-[#002b44]/60 border-t border-gray-200 pt-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ quiz.duration }} menit</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>{{ quiz.attempts_count || 0 }} mengerjakan</span>
                                </div>
                            </div>

                            <!-- CTA Button (warna sama untuk semua) -->
                            <a :href="`/kuis/${quiz.slug}`"
                                class="block w-full bg-gradient-to-r from-[#54b0af] to-[#459a99] hover:shadow-lg text-white font-semibold py-3 rounded-xl transition-all duration-300 text-center group/btn">
                                <span class="flex items-center justify-center gap-2">
                                    Mulai Kuis
                                    <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- Kuis Inovatif (Statis) -->
                    <div 
                        v-for="innovativeQuiz in innovativeQuizzes" 
                        :key="innovativeQuiz.id"
                        class="relative bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border-2 overflow-hidden hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group"
                        :class="innovativeQuiz.borderColor"
                    >
                        <!-- Quiz Header dengan Gradient -->
                        <div :class="`relative h-48 bg-gradient-to-br ${innovativeQuiz.bgGradient} overflow-hidden`">
                            <!-- Icon Besar (TANPA ANIMASI) -->
                            <div class="flex items-center justify-center h-full">
                                <div class="text-8xl group-hover:scale-110 transition-transform duration-300">
                                    {{ innovativeQuiz.icon }}
                                </div>
                            </div>

                            <!-- Badge DALAM PENGEMBANGAN -->
                            <div class="absolute top-4 right-4">
                                <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    DALAM PENGEMBANGAN
                                </span>
                            </div>

                            <!-- Questions Count Badge -->
                            <div class="absolute bottom-4 left-4 bg-white/95 backdrop-blur-sm text-[#002b44] text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-md">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ innovativeQuiz.questions }} soal
                            </div>
                        </div>

                        <!-- Quiz Content -->
                        <div class="p-5 space-y-4">
                            <!-- Title -->
                            <h3 class="font-bold text-[#002b44] text-lg group-hover:text-[#54b0af] transition-colors">
                                {{ innovativeQuiz.title }}
                            </h3>

                            <!-- Description -->
                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                {{ innovativeQuiz.description }}
                            </p>

                            <!-- Quiz Info Row -->
                            <div class="flex items-center justify-between text-xs text-[#002b44]/60 border-t border-gray-200 pt-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ innovativeQuiz.duration }} menit</span>
                                </div>
                                <div class="flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-full">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span class="font-semibold">BETA</span>
                                </div>
                            </div>

                            <!-- CTA Button dengan Gradient Khusus -->
                            <a :href="route(innovativeQuiz.route)"
                                :class="`block w-full bg-gradient-to-r ${innovativeQuiz.gradient} hover:shadow-xl text-white font-semibold py-3 rounded-xl transition-all duration-300 text-center group/btn`">
                                <span class="flex items-center justify-center gap-2">
                                    Coba Sekarang
                                    <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!quizzes.data || quizzes.data.length === 0"
                    class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 p-20 text-center">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <p class="text-xl font-medium text-gray-600">
                        {{ search || type || is_duel_enabled ? 'Tidak ada kuis yang ditemukan' : 'Belum ada kuis tersedia' }}
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="quizzes.data && quizzes.data.length > 0"
                    class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-4 mt-8">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            Menampilkan {{ quizzes.from }} - {{ quizzes.to }} dari {{ quizzes.total }} kuis
                        </div>

                        <div class="flex items-center gap-1">
                            <!-- Previous -->
                            <component :is="quizzes.prev_page_url ? 'a' : 'span'" :href="quizzes.prev_page_url"
                                class="p-2 rounded-lg transition-all"
                                :class="quizzes.prev_page_url
                                    ? 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </component>

                            <!-- Page Numbers -->
                            <template v-for="(link, i) in quizzes.links" :key="i">
                                <component v-if="link.url && !isNaN(link.label)" :is="'a'" :href="link.url"
                                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                                    :class="link.active
                                        ? 'bg-[#54b0af] text-white shadow-md'
                                        : 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'">
                                    {{ link.label }}
                                </component>
                                <span v-else-if="link.label === '...'" class="px-3 py-1.5 text-sm text-gray-500">
                                    ...
                                </span>
                            </template>

                            <!-- Next -->
                            <component :is="quizzes.next_page_url ? 'a' : 'span'" :href="quizzes.next_page_url"
                                class="p-2 rounded-lg transition-all"
                                :class="quizzes.next_page_url
                                    ? 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </component>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="mt-8 bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-[#54b0af]/10 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-[#002b44] mb-2">Tentang Kuis</h4>
                            <p class="text-gray-600 leading-relaxed">
                                Kuis kami dirancang untuk menguji pemahaman Anda tentang Bahasa dan Budaya Belitung.
                                Setiap kuis memiliki soal-soal yang dapat diselesaikan dalam waktu yang ditentukan.
                                <strong class="text-orange-600">Kuis Tantangan</strong> memungkinkan Anda bermain bersama teman dalam format yang lebih seru!
                                <span class="block mt-2 text-purple-600 font-medium">
                                    🚀 Kuis Inovatif sedang dalam tahap pengembangan dan menggunakan teknologi speech recognition!
                                </span>
                            </p>
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