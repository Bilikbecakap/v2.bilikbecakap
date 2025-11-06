<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Beranda - Bilik Bercakap" />

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
                            <div class="inline-flex items-center gap-2 bg-[#54b0af]/10 text-[#54b0af] px-4 py-2 rounded-full text-sm font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
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
                            <a href="#" class="inline-flex items-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-8 py-4 rounded-full font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                Mulai Belajar
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                            <a href="#" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-[#002b44] px-8 py-4 rounded-full font-semibold border-2 border-[#002b44] transition-all duration-300 transform hover:-translate-y-1">
                                Bergabung
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-6 pt-8 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#54b0af]">1000+</div>
                                <div class="text-sm text-gray-600 mt-1">Kata dalam Kamus</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#54b0af]">50+</div>
                                <div class="text-sm text-gray-600 mt-1">Modul Pembelajaran</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#54b0af]">500+</div>
                                <div class="text-sm text-gray-600 mt-1">Pengguna Aktif</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Hero Image with Animated Elements -->
                    <div class="relative animate-fade-in">
                        <!-- Main Hero Image -->
                        <div class="relative z-10">
                            <img src="/hero-image.png" alt="Bilik Bercakap Hero" class="w-full h-auto">
                        </div>

                        <!-- Floating Animated Elements -->
                        <div class="absolute top-24 left-20 animate-float-slow">
                            <div class="bg-white p-4 rounded-xl shadow-xl border-2 border-[#54b0af]">
                                <svg class="w-12 h-12 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>

                        <div class="absolute top-1/4 -right-8 animate-float-medium">
                            <div class="bg-[#54b0af] p-4 rounded-xl shadow-xl">
                                <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                            </div>
                        </div>

                        <div class="absolute bottom-12 -left-6 animate-float-fast">
                            <div class="bg-[#002b44] p-4 rounded-xl shadow-xl">
                                <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                        </div>

                        <div class="absolute -bottom-8 right-1/4 animate-float-medium">
                            <div class="bg-white p-4 rounded-xl shadow-xl border-2 border-[#002b44]">
                                <svg class="w-12 h-12 text-[#002b44]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>

                        <!-- Decorative Circles -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#54b0af]/20 rounded-full blur-2xl animate-pulse-slow"></div>
                        <div class="absolute bottom-0 left-0 w-40 h-40 bg-[#002b44]/20 rounded-full blur-2xl animate-pulse-slow" style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>

            <!-- Wave Divider -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg class="w-full h-auto" viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 80C1200 80 1320 70 1380 65L1440 60V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#54b0af" fill-opacity="0.1"/>
                </svg>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

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
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

@keyframes float-medium {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(-5deg);
    }
}

@keyframes float-fast {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-25px) rotate(3deg);
    }
}

@keyframes pulse-slow {
    0%, 100% {
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