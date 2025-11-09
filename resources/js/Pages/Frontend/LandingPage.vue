<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const openFaq = ref(1);

const toggleFaq = (index) => {
    openFaq.value = openFaq.value === index ? null : index;
};

defineProps({
    latestArtikel: Array
});
</script>

<template>

    <Head title="Bilikbecakap - Bersatu Memajukan Kebudayaan" />

    <FrontendLayout>
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-[#54b0af]/10 via-white to-[#002b44]/5 py-20 overflow-hidden">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/10 rounded-full blur-3xl -z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-[#002b44]/10 rounded-full blur-3xl -z-10"></div>

            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Column: Content -->
                    <div class="space-y-8 animate-fade-in-up">
                        <div class="space-y-4">
                            <!-- Badge -->
                            <div
                                class="inline-flex items-center gap-2 bg-[#54b0af]/10 text-[#54b0af] px-4 py-2 rounded-full text-sm font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                Platform Pelestarian Budaya Belitung
                            </div>

                            <!-- Heading -->
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-[#002b44] leading-tight">
                                Selamat Datang di
                                <span class="text-[#54b0af] block mt-2">Bilikbecakap</span>
                            </h1>

                            <!-- Description -->
                            <p class="text-lg text-gray-600 leading-relaxed">
                                Jelajahi dan pelajari bahasa serta budaya Belitung Timur dengan Bilikbecakap.
                                Kami hadir untuk menjaga warisan budaya Belitung melalui kamus digital,
                                mesin penerjemah bahasa daerah, pembelajaran interaktif, dan modul budaya.
                            </p>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-wrap items-center gap-4">
                            <a href="#keunggulan-kami"
                                class="inline-flex items-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-8 py-4 rounded-full font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                Mulai Belajar
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                            <a href="#"
                                class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-[#002b44] px-8 py-4 rounded-full font-semibold border-2 border-[#002b44] transition-all duration-300 transform hover:-translate-y-1">
                                Bergabung
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-6 pt-8 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#54b0af]">1300+</div>
                                <div class="text-sm text-gray-600 mt-1">Kata dalam Kamus</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#54b0af]">10+</div>
                                <div class="text-sm text-gray-600 mt-1">Modul & Quizz Pembelajaran</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#54b0af]">80%</div>
                                <div class="text-sm text-gray-600 mt-1">Akurasi Penerjemah</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Hero Image with Animated Elements -->
                    <div class="relative animate-fade-in">
                        <!-- Main Hero Image -->
                        <div class="relative z-99">
                            <img src="/hero-bilikbecakap.png" alt="Bilik Bercakap Hero" class="w-full h-auto">
                        </div>

                        <!-- Floating Animated Elements -->
                        <div class="absolute top-24 left-40 animate-float-slow">
                            <div class="bg-white p-4 rounded-xl shadow-xl border-2 border-[#54b0af]">
                                <svg class="w-12 h-12 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>

                        <div class="absolute top-1/4 -right-8 animate-float-medium">
                            <div class="bg-[#54b0af] p-4 rounded-xl shadow-xl">
                                <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                            </div>
                        </div>

                        <div class="absolute bottom-20 -left-6 animate-float-fast">
                            <div class="bg-[#002b44] p-4 rounded-xl shadow-xl">
                                <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                        </div>

                        <div class="absolute -bottom-2 right-1/4 animate-float-medium">
                            <div class="bg-white p-4 rounded-xl shadow-xl border-2 border-[#002b44]">
                                <svg class="w-12 h-12 text-[#002b44]" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>

                        <!-- Decorative Circles -->
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-[#54b0af]/20 rounded-full blur-2xl animate-pulse-slow">
                        </div>
                        <div class="absolute bottom-0 left-0 w-40 h-40 bg-[#002b44]/20 rounded-full blur-2xl animate-pulse-slow"
                            style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>

            <!-- Wave Divider (Smooth Transition) -->
            <div class="absolute bottom-0 left-0 right-0 overflow-hidden leading-[0]">
                <svg class="relative block w-full h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120"
                    preserveAspectRatio="none">
                    <path d="M0,64 C360,160 1080,0 1440,96 L1440,120 L0,120 Z" fill="#ffffff">
                    </path>
                </svg>
            </div>
        </section>

        <!-- What We Offer Section -->
        <section class="py-20 bg-white" id="keunggulan-kami">
            <div class="container mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center mb-16 space-y-4">
                    <div
                        class="inline-flex items-center gap-2 bg-[#54b0af]/10 text-[#54b0af] px-4 py-2 rounded-full text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Temukan Keunggulan Kami
                    </div>
                    <h2 class="text-4xl md:text-5xl font-bold text-[#002b44]">Fitur Utama</h2> <!-- What We Offer -->
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Berbagai fitur yang memudahkan Anda dalam mempelajari dan melestarikan Bahasa Melayu Belitung
                    </p>
                </div>

                <!-- Feature Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Card 1: Mesin Penerjemah -->
                    <div
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2">
                        <!-- Image -->
                        <div class="relative h-48 bg-gradient-to-br from-[#54b0af]/10 to-[#54b0af]/5 overflow-hidden">
                            <img src="/ilustration/penerjemah.png" alt="Mesin Penerjemah"
                                class="w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-4 right-4 bg-[#54b0af] text-white p-2 rounded-lg">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-4">
                            <a href="/penerjemah" class="block">
                                <h3
                                    class="text-2xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors hover:underline hover:underline-offset-4">
                                    Mesin Penerjemah
                                </h3>
                            </a>
                            <p class="text-gray-600 leading-relaxed">
                                Terjemahkan kata atau kalimat dari Bahasa Indonesia ke Bahasa Belitung secara instan
                                dengan akurasi tinggi
                            </p>
                            <a href="/penerjemah"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-3 transition-all duration-300 group/link">
                                Coba Sekarang
                                <svg class="w-5 h-5 group-hover/link:translate-x-1 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Kamus Digital -->
                    <div
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2">
                        <div class="relative h-48 bg-gradient-to-br from-[#002b44]/10 to-[#002b44]/5 overflow-hidden">
                            <img src="/ilustration/kamus.png" alt="Kamus Digital"
                                class="w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-4 right-4 bg-[#002b44] text-white p-2 rounded-lg">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <a href="/kamus" class="block">
                                <h3
                                    class="text-2xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors hover:underline hover:underline-offset-4">
                                    Kamus Digital
                                </h3>
                            </a>
                            <p class="text-gray-600 leading-relaxed">
                                Cari makna kata dan istilah dalam Bahasa Melayu Belitung dengan mudah dan cepat
                            </p>
                            <a href="/kamus"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-3 transition-all duration-300 group/link">
                                Jelajahi Kamus
                                <svg class="w-5 h-5 group-hover/link:translate-x-1 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: Pembelajaran & Quiz -->
                    <div
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2">
                        <div class="relative h-48 bg-gradient-to-br from-amber-500/10 to-amber-500/5 overflow-hidden">
                            <img src="/ilustration/pembelajaran.png" alt="Pembelajaran & Quiz"
                                class="w-full h-full object-contain p-6 group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-4 right-4 bg-amber-500 text-white p-2 rounded-lg">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <a href="/pembelajaran" class="block">
                                <h3
                                    class="text-2xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors hover:underline hover:underline-offset-4">
                                    Pembelajaran & Quiz
                                </h3>
                            </a>
                            <p class="text-gray-600 leading-relaxed">
                                Nikmati pembelajaran Budaya Belitung serta lestarikan bahasa dan budaya melalui quiz
                                interaktif
                            </p>
                            <a href="/pembelajaran"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-3 transition-all duration-300 group/link">
                                Mulai Belajar
                                <svg class="w-5 h-5 group-hover/link:translate-x-1 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Achievement Section -->
        <section class="py-20 bg-gradient-to-br from-[#002b44]/5 via-white to-[#54b0af]/5">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Column: Image -->
                    <div class="relative group">
                        <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                            <img src="/images/kbkm2023.jpg" alt="KBKM 2023"
                                class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-700">
                            <!-- Overlay Gradient -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-[#002b44]/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                        </div>

                        <!-- Decorative Elements -->
                        <div
                            class="absolute -top-6 -left-6 w-24 h-24 bg-[#54b0af] rounded-full blur-2xl opacity-30 animate-pulse-slow">
                        </div>
                        <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-[#002b44] rounded-full blur-2xl opacity-20 animate-pulse-slow"
                            style="animation-delay: 1s;"></div>

                    </div>

                    <!-- Right Column: Content -->
                    <div class="space-y-6">
                        <!-- Badge -->
                        <div
                            class="inline-flex items-center gap-2 bg-[#54b0af]/10 text-[#54b0af] px-4 py-2 rounded-full text-sm font-medium">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Perjalanan Kami
                        </div>

                        <!-- Heading -->
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-[#002b44] leading-tight">
                            Langkah Demi Langkah,
                            <span class="text-[#54b0af] block mt-2">Melestarikan Bahasa dan Budaya Bersama</span>
                        </h2>

                        <!-- Description -->
                        <div class="space-y-4 text-gray-600 leading-relaxed">
                            <p>
                                Seiring dengan dukungan pengguna, <span
                                    class="font-semibold text-[#54b0af]">Bilikbecakap</span> terus berkembang dalam
                                upaya pelestarian bahasa dan budaya Belitung Timur. Kami bangga bisa memberikan
                                fitur-fitur yang semakin kaya dan bermanfaat bagi masyarakat.
                            </p>
                            <p>
                                Bilik Becakap hadir di dalam program <span class="font-semibold text-[#002b44]">Kemah
                                    Budaya Kaum Muda (KBKM) 2023</span> sebagai ruang inkubator yang mewadahi inisiatif
                                kaum muda dalam menciptakan aplikasi dan prakarya serta menjadi jembatan yang
                                mempertemukan antara gagasan dengan eksperimentasi di bidang <span
                                    class="font-semibold text-[#54b0af]">STEAM</span> (Science, Technology, Engineering,
                                Art, Mathematics). KBKM mendorong para peserta agar karyanya dapat terus berlanjut dan
                                berdampak luas sehingga mampu menjadi solusi untuk permasalahan sosial di tengah
                                masyarakat.
                            </p>
                        </div>

                        <!-- CTA Button -->
                        <div class="pt-4">
                            <a href="/tentang"
                                class="inline-flex items-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-8 py-4 rounded-full font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                Pelajari Lebih Lanjut
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 bg-[#002b44] relative overflow-hidden">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-[#54b0af]/10 rounded-full blur-3xl"></div>

            <div class="container mx-auto px-6 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16 space-y-4">
                    <h2 class="text-4xl md:text-5xl font-bold text-white">Pertanyaan yang Sering Ditanyakan</h2>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                        Temukan jawaban atas pertanyaan umum tentang Bilikbecakap
                    </p>
                </div>

                <!-- FAQ Accordion -->
                <div class="max-w-4xl mx-auto space-y-4">
                    <!-- FAQ Item 1 -->
                    <div
                        class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300">
                        <button @click="toggleFaq(1)"
                            class="w-full px-8 py-6 flex items-center justify-between text-left group">
                            <span class="text-lg font-semibold text-white pr-8">Apa itu Bilikbecakap?</span>
                            <svg class="w-6 h-6 text-[#54b0af] transform transition-transform duration-300"
                                :class="{ 'rotate-180': openFaq === 1 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="openFaq === 1" class="px-8 pb-6">
                            <p class="text-gray-300 leading-relaxed">
                                Bilikbecakap adalah platform digital yang didedikasikan untuk pelestarian bahasa dan
                                budaya Belitung Timur, khususnya Bahasa Belitung.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div
                        class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300">
                        <button @click="toggleFaq(2)"
                            class="w-full px-8 py-6 flex items-center justify-between text-left group">
                            <span class="text-lg font-semibold text-white pr-8">Bagaimana cara menggunakan Kamus Digital
                                di Bilikbecakap?</span>
                            <svg class="w-6 h-6 text-[#54b0af] transform transition-transform duration-300"
                                :class="{ 'rotate-180': openFaq === 2 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="openFaq === 2" class="px-8 pb-6">
                            <p class="text-gray-300 leading-relaxed">
                                Anda dapat mencari kata atau istilah dalam Bahasa Belitung dengan mengetik kata yang
                                ingin dicari pada kolom Kamus Digital. Sistem akan menampilkan arti dan contoh
                                penggunaan kata tersebut, memudahkan Anda dalam memahami konteks dan makna yang tepat.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div
                        class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300">
                        <button @click="toggleFaq(3)"
                            class="w-full px-8 py-6 flex items-center justify-between text-left group">
                            <span class="text-lg font-semibold text-white pr-8">Apa saja yang dapat dipelajari dalam
                                Modul Pembelajaran Bahasa & Budaya?</span>
                            <svg class="w-6 h-6 text-[#54b0af] transform transition-transform duration-300"
                                :class="{ 'rotate-180': openFaq === 3 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="openFaq === 3" class="px-8 pb-6">
                            <p class="text-gray-300 leading-relaxed">
                                Modul pembelajaran kami mencakup dasar-dasar Bahasa Belitung, tata bahasa, serta wawasan
                                budaya seperti adat istiadat dan sejarah Belitung Timur. Materi disusun secara
                                interaktif untuk mempermudah pemahaman dan menarik minat belajar.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div
                        class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300">
                        <button @click="toggleFaq(4)"
                            class="w-full px-8 py-6 flex items-center justify-between text-left group">
                            <span class="text-lg font-semibold text-white pr-8">Bisakah Bilikbecakap menerjemahkan
                                kalimat dari Bahasa Indonesia ke Bahasa Belitung?</span>
                            <svg class="w-6 h-6 text-[#54b0af] transform transition-transform duration-300"
                                :class="{ 'rotate-180': openFaq === 4 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="openFaq === 4" class="px-8 pb-6">
                            <p class="text-gray-300 leading-relaxed">
                                Ya, Bilikbecakap memiliki fitur mesin penerjemah yang dapat menerjemahkan kata atau
                                kalimat dari Bahasa Indonesia ke Bahasa Belitung secara cepat. Cukup masukkan teks yang
                                ingin diterjemahkan, dan hasilnya akan segera muncul.
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div
                        class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300">
                        <button @click="toggleFaq(5)"
                            class="w-full px-8 py-6 flex items-center justify-between text-left group">
                            <span class="text-lg font-semibold text-white pr-8">Bagaimana Bilikbecakap berkontribusi
                                pada pelestarian dan keberlanjutan bahasa serta budaya daerah?</span>
                            <svg class="w-6 h-6 text-[#54b0af] transform transition-transform duration-300"
                                :class="{ 'rotate-180': openFaq === 5 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="openFaq === 5" class="px-8 pb-6">
                            <p class="text-gray-300 leading-relaxed">
                                Bilikbecakap memiliki komitmen jangka panjang untuk mendukung pelestarian bahasa dan
                                budaya Belitung Timur. Dengan menyediakan akses digital ke kamus, mesin penerjemah, dan
                                modul pembelajaran budaya, platform ini membantu masyarakat lokal dan generasi muda
                                menjaga dan menghargai warisan budaya mereka. Selain itu, Bilikbecakap berencana
                                memperluas kolaborasi dengan lembaga pendidikan dan komunitas budaya untuk memperkaya
                                konten serta memastikan keberlanjutan platform ini dalam jangka panjang.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blog Section -->
        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center mb-16 space-y-4">
                    <div
                        class="inline-flex items-center gap-2 bg-[#54b0af]/10 text-[#54b0af] px-4 py-2 rounded-full text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        Berita & Blog
                    </div>
                    <h2 class="text-4xl md:text-5xl font-bold text-[#002b44]">Jelajahi Berita & Blog Terbaru Kami</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Dapatkan informasi terkini seputar budaya, bahasa, dan perkembangan Bilikbecakap
                    </p>
                </div>

                <!-- Blog Cards -->
                <div v-if="latestArtikel && latestArtikel.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="artikel in latestArtikel" :key="artikel.id"
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2">
                        <!-- Image -->
                        <div class="relative h-56 overflow-hidden bg-gradient-to-br from-[#54b0af]/10 to-gray-100">
                            <img v-if="artikel.gambar_thumbnail" :src="artikel.gambar_thumbnail" :alt="artikel.judul"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                            <!-- Category Badge -->
                            <div v-if="artikel.kategori"
                                class="absolute top-4 left-4 bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm font-medium">
                                {{ artikel.kategori }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-4">
                            <!-- Date & Author -->
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ new Date(artikel.tanggal_publish).toLocaleDateString('id-ID', {
                                        day: 'numeric',
                                    month: 'short', year: 'numeric' }) }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <!-- <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ artikel.penulis }} -->
                                </div>
                            </div>

                            <!-- Title -->
                            <h3
                                class="text-xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors line-clamp-2">
                                {{ artikel.judul }}
                            </h3>

                            <!-- Excerpt -->
                            <p class="text-gray-600 leading-relaxed line-clamp-3">
                                {{ artikel.excerpt }}
                            </p>

                            <!-- Read More Button -->
                            <a :href="`/artikel/${artikel.slug}`"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-3 transition-all duration-300 group/link">
                                Baca Selengkapnya
                                <svg class="w-5 h-5 group-hover/link:translate-x-1 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <p class="text-gray-500">Belum ada artikel tersedia</p>
                </div>

                <!-- View All Button -->
                <div v-if="latestArtikel && latestArtikel.length > 0" class="text-center mt-12">
                    <a href="/artikel"
                        class="inline-flex items-center gap-2 bg-[#002b44] hover:bg-[#003a5c] text-white px-8 py-4 rounded-full font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Lihat Semua Artikel
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

    </FrontendLayout>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes float-slow {

    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }

    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

@keyframes float-medium {

    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }

    50% {
        transform: translateY(-15px) rotate(-5deg);
    }
}

@keyframes float-fast {

    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }

    50% {
        transform: translateY(-25px) rotate(3deg);
    }
}

@keyframes pulse-slow {

    0%,
    100% {
        opacity: 0.3;
        transform: scale(1);
    }

    50% {
        opacity: 0.5;
        transform: scale(1.1);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 1s ease-out;
}

.animate-fade-in {
    animation: fade-in 1.2s ease-out;
}

.animate-float-slow {
    animation: float-slow 6s ease-in-out infinite;
}

.animate-float-medium {
    animation: float-medium 5s ease-in-out infinite;
}

.animate-float-fast {
    animation: float-fast 4s ease-in-out infinite;
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}
</style>