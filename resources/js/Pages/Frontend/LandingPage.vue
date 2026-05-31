<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();
const openFaq = ref(1);

const toggleFaq = (index) => {
    openFaq.value = openFaq.value === index ? null : index;
};

defineProps({
    latestArtikel: Array
});

// Counter animation
const stat1 = ref(0);
const stat2 = ref(0);
const stat3 = ref(0);

// Scroll animations
const isFeatureVisible = ref(false);
const isAchievementVisible = ref(false);
const isBlogVisible = ref(false);

// Parallax effect
const scrollY = ref(0);

// Interactive cards state
const hoveredCard = ref(null);

onMounted(() => {
    animateCounter(stat1, 1300, 2000);
    animateCounter(stat2, 10, 1500);
    animateCounter(stat3, 80, 2000);

    // Scroll observer
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px'
    };

    const featureObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                isFeatureVisible.value = true;
            }
        });
    }, observerOptions);

    const achievementObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                isAchievementVisible.value = true;
            }
        });
    }, observerOptions);

    const blogObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                isBlogVisible.value = true;
            }
        });
    }, observerOptions);

    const featureSection = document.getElementById('keunggulan-kami');
    const achievementSection = document.querySelector('.achievement-section');
    const blogSection = document.querySelector('.blog-section');

    if (featureSection) featureObserver.observe(featureSection);
    if (achievementSection) achievementObserver.observe(achievementSection);
    if (blogSection) blogObserver.observe(blogSection);

    // Parallax scroll
    window.addEventListener('scroll', () => {
        scrollY.value = window.scrollY;
    });
});

const animateCounter = (refValue, target, duration) => {
    const start = 0;
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
        refValue.value += increment;
        if (refValue.value >= target) {
            refValue.value = target;
            clearInterval(timer);
        }
    }, 16);
};

</script>

<template>

    <Head title="Bilikbecakap - Bersatu Memajukan Kebudayaan" />

    <FrontendLayout>
        <!-- Hero Section -->
        <section class="hero-section relative overflow-hidden min-h-screen flex items-center">

            <!-- Mobile overlay: top-to-bottom, semi-transparan agar teks terbaca -->
            <div class="absolute inset-0 pointer-events-none lg:hidden"
                style="background: linear-gradient(to bottom, rgba(255,255,255,0.93) 0%, rgba(255,255,255,0.88) 65%, rgba(255,255,255,0.65) 100%);"></div>
            <!-- Desktop overlay: kiri ke kanan, berhenti sebelum ilustrasi -->
            <div class="absolute inset-0 pointer-events-none hidden lg:block"
                style="background: linear-gradient(to right, rgba(255,255,255,0.87) 0%, rgba(255,255,255,0.85) 42%, rgba(255,255,255,0.40) 54%, transparent 64%);"></div>

            <!-- Animated Background Particles -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                <div class="particle particle-4"></div>
                <div class="particle particle-5"></div>
            </div>

            <!-- Decorative Background Elements with Parallax -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/10 rounded-full blur-3xl -z-10 animate-blob"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-[#002b44]/10 rounded-full blur-3xl -z-10 animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-[#FCB415]/10 rounded-full blur-3xl -z-10 animate-blob animation-delay-4000"></div>

            <div class="container mx-auto px-6 lg:px-8 relative z-10 py-20 lg:py-28">
                <div class="max-w-xl lg:max-w-[50%] animate-fade-in-up space-y-7 lg:space-y-8">

                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 bg-[#54b0af]/10 border border-[#54b0af]/30 px-4 py-2 rounded-full backdrop-blur-sm">
                        <span class="w-2 h-2 bg-[#54b0af] rounded-full animate-pulse"></span>
                        <span class="text-[#54b0af] text-xs font-semibold tracking-widest uppercase">{{ t("messages.hero_badge") }}</span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold leading-tight">
                        <span class="text-[#002b44] whitespace-nowrap">{{ t("messages.hero_title_1") }}</span><br>
                        <span class="text-[#54b0af]">{{ t("messages.hero_title_2") }}</span>
                    </h1>

                    <!-- Description -->
                    <p class="text-lg text-gray-700 leading-relaxed animate-fade-in-delayed max-w-lg">
                        {{ t("messages.hero_description") }}
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <a href="#keunggulan-kami"
                            class="group relative inline-flex items-center justify-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-8 py-4 rounded-full font-semibold text-base transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 overflow-hidden">
                            <span class="relative z-10">🚀 {{ t("messages.hero_cta") }}</span>
                            <svg class="w-5 h-5 group-hover:translate-y-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            <span class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="pt-6 border-t border-gray-290">

                        <!-- Mobile: simple 3-col, no card -->
                        <div class="grid grid-cols-3 gap-3 md:hidden">
                            <div class="text-center">
                                <div class="text-xl font-bold text-[#54b0af]">{{ Math.floor(stat1) }}+</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ t("messages.stat_kosakata_label") }}</div>
                            </div>
                            <div class="text-center border-x border-gray-200">
                                <div class="text-xl font-bold text-[#54b0af]">{{ Math.floor(stat2) }}+</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ t("messages.stat_modul_label") }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-[#54b0af]">{{ Math.floor(stat3) }}%</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ t("messages.stat_akurasi_label") }}</div>
                            </div>
                        </div>

                        <!-- Desktop: full cards -->
                        <div class="hidden md:flex flex-row gap-2">
                            <div class="group cursor-default hover:scale-[1.03] transition-all duration-300 bg-white/95 backdrop-blur-sm border border-gray-200 hover:border-[#54b0af]/60 rounded-xl px-4 py-4 shadow hover:shadow-md w-48">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 bg-[#54b0af]/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-[#54b0af]/20 transition-colors">
                                        <svg class="w-5 h-5 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-base font-bold text-[#54b0af] group-hover:text-[#002b44] transition-colors leading-none">{{ Math.floor(stat1) }}+</div>
                                        <div class="text-xs font-semibold text-gray-700 mt-0.5">{{ t("messages.stat_kosakata_label") }}</div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-2 leading-snug">{{ t("messages.stat_kosakata_desc") }}</p>
                            </div>
                            <div class="group cursor-default hover:scale-[1.03] transition-all duration-300 bg-white/80 backdrop-blur-sm border border-gray-100 hover:border-[#002b44]/40 rounded-xl px-4 py-4 shadow hover:shadow-md w-48">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 bg-[#002b44]/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-[#002b44]/20 transition-colors">
                                        <svg class="w-5 h-5 text-[#002b44]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-base font-bold text-[#54b0af] group-hover:text-[#002b44] transition-colors leading-none">{{ Math.floor(stat2) }}+</div>
                                        <div class="text-xs font-semibold text-gray-700 mt-0.5">{{ t("messages.stat_modul_label") }}</div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-2 leading-snug">{{ t("messages.stat_modul_desc") }}</p>
                            </div>
                            <div class="group cursor-default hover:scale-[1.03] transition-all duration-300 bg-white/80 backdrop-blur-sm border border-gray-100 hover:border-[#FCB415]/60 rounded-xl px-4 py-4 shadow hover:shadow-md w-48">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 bg-[#FCB415]/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-[#FCB415]/20 transition-colors">
                                        <svg class="w-5 h-5 text-[#FCB415]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-base font-bold text-[#54b0af] group-hover:text-[#002b44] transition-colors leading-none">{{ Math.floor(stat3) }}%</div>
                                        <div class="text-xs font-semibold text-gray-700 mt-0.5">{{ t("messages.stat_akurasi_label") }}</div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-2 leading-snug">{{ t("messages.stat_akurasi_desc") }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- App info card - bottom right -->
            <a href="/aplikasi" class="group hidden lg:block absolute bottom-24 right-10 xl:right-16 z-20">
                <div class="bg-white/90 backdrop-blur-md border border-gray-100 rounded-2xl shadow-xl px-4 py-3.5 w-64 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-bold text-[#002b44] truncate">{{ t("messages.app_card_title") }}</span>
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-1.5 py-0.5 rounded-full flex-shrink-0">BETA</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ t("messages.app_card_desc") }}</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-400">{{ t("messages.app_card_label") }}</span>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 group-hover:gap-2 transition-all duration-200">
                            {{ t("messages.app_card_cta") }}
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            <!-- Wave Divider with animation -->
            <div class="absolute bottom-0 left-0 right-0 overflow-hidden leading-[0]">
                <svg class="relative block w-full h-[60px] lg:h-[120px] wave-animation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120"
                    preserveAspectRatio="none">
                    <path d="M0,64 C360,160 1080,0 1440,96 L1440,120 L0,120 Z" fill="#ffffff"></path>
                </svg>
            </div>
        </section>

        <!-- What We Offer Section with stagger animation -->
        <section class="py-20 bg-white relative overflow-hidden" id="keunggulan-kami">

            <!-- Batik pattern background -->
            <div class="absolute inset-0 pointer-events-none">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="batikPattern" width="60" height="60" patternUnits="userSpaceOnUse">
                            <!-- Diamond outer -->
                            <path d="M30 2 L58 30 L30 58 L2 30 Z" fill="none" stroke="#54b0af" stroke-width="0.7" opacity="0.25"/>
                            <!-- Diamond inner -->
                            <path d="M30 14 L46 30 L30 46 L14 30 Z" fill="none" stroke="#002b44" stroke-width="0.5" opacity="0.12"/>
                            <!-- Corner dots -->
                            <circle cx="30" cy="2" r="1.2" fill="#54b0af" opacity="0.2"/>
                            <circle cx="58" cy="30" r="1.2" fill="#54b0af" opacity="0.2"/>
                            <circle cx="30" cy="58" r="1.2" fill="#54b0af" opacity="0.2"/>
                            <circle cx="2" cy="30" r="1.2" fill="#54b0af" opacity="0.2"/>
                            <!-- Center dot -->
                            <circle cx="30" cy="30" r="1.5" fill="#FCB415" opacity="0.15"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#batikPattern)"/>
                </svg>
            </div>

            <div class="container mx-auto px-6 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16 space-y-4" :class="{ 'animate-slide-up': isFeatureVisible }">
                    <h2 class="text-4xl md:text-5xl font-bold text-[#002b44]">{{ t("messages.Fitur Utama") }}</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        {{ t("messages.Fitur Utama Deskripsi") }}
                    </p>
                </div>

                <!-- Feature Cards with stagger animation -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Card 1: Mesin Penerjemah -->
                    <div @mouseenter="hoveredCard = 1" 
                         @mouseleave="hoveredCard = null"
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2"
                        :class="{ 'animate-slide-up-1': isFeatureVisible }">
                        <!-- Image with Ken Burns effect -->
                        <div class="relative h-48 bg-gradient-to-br from-[#54b0af]/10 to-[#54b0af]/5 overflow-hidden">
                            <img src="/ilustration/penerjemah.png" alt="Mesin Penerjemah"
                                class="w-full h-full object-contain p-6 transition-all duration-700 group-hover:scale-125">
                            <div class="absolute top-4 right-4 bg-[#54b0af] text-white p-2 rounded-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-12">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                            </div>
                            <!-- Overlay effect -->
                            <div class="absolute inset-0 bg-gradient-to-t from-[#54b0af]/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-4">
                            <a href="/penerjemah" class="block">
                                <h3 class="text-2xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors hover:underline hover:underline-offset-4">
                                    {{ t("messages.Mesin Penerjemah") }}
                                </h3>
                            </a>
                            <p class="text-gray-600 leading-relaxed">
                                {{ t("messages.Mesin Penerjemah Deskripsi") }}
                            </p>
                            <a href="/penerjemah"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-4 transition-all duration-300 group/link">
                                {{ t("messages.coba sekarang") }}
                                <svg class="w-5 h-5 group-hover/link:translate-x-2 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Kamus Digital -->
                    <div @mouseenter="hoveredCard = 2" 
                         @mouseleave="hoveredCard = null"
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2"
                        :class="{ 'animate-slide-up-2': isFeatureVisible }">
                        <div class="relative h-48 bg-gradient-to-br from-[#002b44]/10 to-[#002b44]/5 overflow-hidden">
                            <img src="/ilustration/kamus.png" alt="Kamus Digital"
                                class="w-full h-full object-contain p-6 transition-all duration-700 group-hover:scale-125">
                            <div class="absolute top-4 right-4 bg-[#002b44] text-white p-2 rounded-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-12">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-[#002b44]/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        </div>

                        <div class="p-6 space-y-4">
                            <a href="/kamus" class="block">
                                <h3 class="text-2xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors hover:underline hover:underline-offset-4">
                                    {{ t("messages.kamus digital") }}
                                </h3>
                            </a>
                            <p class="text-gray-600 leading-relaxed">
                                {{ t("messages.kamus digital deskripsi") }}
                            </p>
                            <a href="/kamus"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-4 transition-all duration-300 group/link">
                                {{ t("messages.coba sekarang") }}
                                <svg class="w-5 h-5 group-hover/link:translate-x-2 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: Pembelajaran & Quiz -->
                    <div @mouseenter="hoveredCard = 3" 
                         @mouseleave="hoveredCard = null"
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2"
                        :class="{ 'animate-slide-up-3': isFeatureVisible }">
                        <div class="relative h-48 bg-gradient-to-br from-amber-500/10 to-amber-500/5 overflow-hidden">
                            <img src="/ilustration/pembelajaran.png" alt="Pembelajaran & Quiz"
                                class="w-full h-full object-contain p-6 transition-all duration-700 group-hover:scale-125">
                            <div class="absolute top-4 right-4 bg-amber-500 text-white p-2 rounded-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-12">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-amber-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        </div>

                        <div class="p-6 space-y-4">
                            <a href="/pembelajaran" class="block">
                                <h3 class="text-2xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors hover:underline hover:underline-offset-4">
                                    {{ t("messages.pembelajaran dan kuis") }}
                                </h3>
                            </a>
                            <p class="text-gray-600 leading-relaxed">
                                {{ t("messages.pembelajaran dan kuis deskripsi") }}
                            </p>
                            <a href="/pembelajaran"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-4 transition-all duration-300 group/link">
                                {{ t("messages.coba sekarang") }}
                                <svg class="w-5 h-5 group-hover/link:translate-x-2 transition-transform" fill="none"
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

        <!-- App Banner -->
        <div class="bg-[#002b44] py-6">
            <div class="container mx-auto px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center gap-5 sm:gap-6">

                    <!-- Icon -->
                    <div class="w-12 h-12 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <!-- Text -->
                    <div class="flex-1 text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                            <span class="text-white font-bold text-base">{{ t("messages.app_banner_title") }}</span>
                            <span class="bg-[#FCB415] text-[#002b44] text-xs font-black px-2 py-0.5 rounded-full leading-none">BETA</span>
                        </div>
                        <p class="text-white/60 text-sm">{{ t("messages.app_banner_desc") }}</p>
                    </div>

                    <!-- CTA -->
                    <a href="/aplikasi"
                        class="group flex-shrink-0 inline-flex items-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ t("messages.app_banner_cta") }}
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>

                </div>
            </div>
        </div>

        <!-- Achievement Section with parallax -->
        <section class="achievement-section py-20 bg-gradient-to-br from-[#002b44]/5 via-white to-[#54b0af]/5 relative overflow-hidden">
            <!-- Animated background -->
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-10 left-10 w-64 h-64 bg-[#54b0af]/20 rounded-full blur-3xl animate-blob"></div>
                <div class="absolute bottom-10 right-10 w-64 h-64 bg-[#002b44]/20 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            </div>

            <div class="container mx-auto px-6 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Column: Image with 3D effect -->
                    <div class="relative group" :class="{ 'animate-slide-left': isAchievementVisible }">
                        <div class="relative overflow-hidden rounded-2xl shadow-2xl transform transition-all duration-700 group-hover:scale-105 group-hover:rotate-2">
                            <img src="/images/kbkm2023.jpg" alt="KBKM 2023"
                                class="w-full h-auto object-cover">
                            <!-- Animated Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-[#002b44]/80 via-[#002b44]/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-700">
                                <div class="absolute bottom-6 left-6 right-6 text-white transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                                    <p class="text-lg font-semibold">Kemah Budaya Kaum Muda 2023</p>
                                </div>
                            </div>
                            
                            <!-- Shine effect -->
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                        </div>

                        <!-- Decorative floating elements -->
                        <div class="absolute -top-6 -left-6 w-24 h-24 bg-[#54b0af] rounded-full blur-2xl opacity-30 animate-pulse-slow"></div>
                        <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-[#002b44] rounded-full blur-2xl opacity-20 animate-pulse-slow" style="animation-delay: 1s;"></div>
                        
                        <!-- Frame decoration -->
                        <div class="absolute -inset-4 border-2 border-[#54b0af]/20 rounded-3xl transform rotate-3 group-hover:rotate-6 transition-transform duration-500"></div>
                    </div>

                    <!-- Right Column: Content with typing effect -->
                    <div class="space-y-6" :class="{ 'animate-slide-right': isAchievementVisible }">

                        <!-- Heading -->
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-[#002b44] leading-tight">
                            {{ t("messages.perjalanan kami deskripsi 1") }},
                            <span class="text-[#54b0af] block mt-2">{{ t("messages.perjalanan kami deskripsi 2") }}</span>
                        </h2>

                        <!-- Description with reveal animation -->
                        <div class="space-y-4 text-gray-600 leading-relaxed">
                            <p class="transform transition-all duration-500 hover:translate-x-2">
                                {{ t("messages.our_journey_deskripsi_1") }}, <span
                                    class="font-semibold text-[#54b0af]">Bilikbecakap</span> {{ t("messages.our_journey_deskripsi_2") }}
                            </p>
                            <p v-html="t('messages.our_journey_deskripsi_3')" class="transform transition-all duration-500 hover:translate-x-2"></p>
                        </div>

                        <!-- CTA Button with ripple -->
                        <div class="pt-4">
                            <a href="/tentang"
                                class="group relative inline-flex items-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-8 py-4 rounded-full font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 overflow-hidden">
                                <span class="relative z-10">{{ t("messages.pelajari lebih lanjut") }}</span>
                                <svg class="relative z-10 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                <!-- Ripple effect -->
                                <span class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 scale-0 group-hover:scale-100 transition-all duration-500 rounded-full"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section with smooth accordion -->
        <section class="py-20 bg-[#002b44] relative overflow-hidden">
            <!-- Animated background particles -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="faq-particle faq-particle-1"></div>
                <div class="faq-particle faq-particle-2"></div>
                <div class="faq-particle faq-particle-3"></div>
            </div>

            <!-- Decorative Background Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-[#54b0af]/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>

            <div class="container mx-auto px-6 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16 space-y-4 animate-fade-in">
                    <h2 class="text-4xl md:text-5xl font-bold text-white">{{ t("messages.faq_title") }}</h2>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                        {{ t("messages.faq_desc") }}
                    </p>
                </div>

                <!-- FAQ Accordion with enhanced animations -->
                <div class="max-w-4xl mx-auto space-y-4">
                    <!-- FAQ Item 1 -->
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300 transform hover:scale-[1.02]">
                        <button @click="toggleFaq(1)"
                            class="w-full px-6 lg:px-8 py-4 lg:py-6 flex items-center justify-between text-left group">
                            <span class="text-base lg:text-lg font-semibold text-white pr-4 lg:pr-8 group-hover:text-[#54b0af] transition-colors">{{ t("messages.faq_1") }}</span>
                            <svg class="w-5 lg:w-6 h-5 lg:h-6 text-[#54b0af] transform transition-transform duration-500 flex-shrink-0"
                                :class="{ 'rotate-180': openFaq === 1 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-500 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0">
                            <div v-show="openFaq === 1" class="overflow-hidden">
                                <div class="px-6 lg:px-8 pb-4 lg:pb-6">
                                    <p class="text-gray-300 leading-relaxed text-sm lg:text-base">
                                        {{ t("messages.faq_answer_1") }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300 transform hover:scale-[1.02]">
                        <button @click="toggleFaq(2)"
                            class="w-full px-6 lg:px-8 py-4 lg:py-6 flex items-center justify-between text-left group">
                            <span class="text-base lg:text-lg font-semibold text-white pr-4 lg:pr-8 group-hover:text-[#54b0af] transition-colors">{{ t("messages.faq_2") }} di Bilikbecakap?</span>
                            <svg class="w-5 lg:w-6 h-5 lg:h-6 text-[#54b0af] transform transition-transform duration-500 flex-shrink-0"
                                :class="{ 'rotate-180': openFaq === 2 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-500 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0">
                            <div v-show="openFaq === 2" class="overflow-hidden">
                                <div class="px-6 lg:px-8 pb-4 lg:pb-6">
                                    <p class="text-gray-300 leading-relaxed text-sm lg:text-base">
                                        {{ t("messages.faq_answer_2") }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300 transform hover:scale-[1.02]">
                        <button @click="toggleFaq(3)"
                            class="w-full px-6 lg:px-8 py-4 lg:py-6 flex items-center justify-between text-left group">
                            <span class="text-base lg:text-lg font-semibold text-white pr-4 lg:pr-8 group-hover:text-[#54b0af] transition-colors">{{ t("messages.faq_3") }}</span>
                            <svg class="w-5 lg:w-6 h-5 lg:h-6 text-[#54b0af] transform transition-transform duration-500 flex-shrink-0"
                                :class="{ 'rotate-180': openFaq === 3 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-500 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0">
                            <div v-show="openFaq === 3" class="overflow-hidden">
                                <div class="px-6 lg:px-8 pb-4 lg:pb-6">
                                    <p class="text-gray-300 leading-relaxed text-sm lg:text-base">
                                        {{ t("messages.faq_answer_3") }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300 transform hover:scale-[1.02]">
                        <button @click="toggleFaq(4)"
                            class="w-full px-6 lg:px-8 py-4 lg:py-6 flex items-center justify-between text-left group">
                            <span class="text-base lg:text-lg font-semibold text-white pr-4 lg:pr-8 group-hover:text-[#54b0af] transition-colors">{{ t("messages.faq_4") }}</span>
                            <svg class="w-5 lg:w-6 h-5 lg:h-6 text-[#54b0af] transform transition-transform duration-500 flex-shrink-0"
                                :class="{ 'rotate-180': openFaq === 4 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-500 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0">
                            <div v-show="openFaq === 4" class="overflow-hidden">
                                <div class="px-6 lg:px-8 pb-4 lg:pb-6">
                                    <p class="text-gray-300 leading-relaxed text-sm lg:text-base">
                                        {{ t("messages.faq_answer_4") }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden hover:border-[#54b0af]/50 transition-all duration-300 transform hover:scale-[1.02]">
                        <button @click="toggleFaq(5)"
                            class="w-full px-6 lg:px-8 py-4 lg:py-6 flex items-center justify-between text-left group">
                            <span class="text-base lg:text-lg font-semibold text-white pr-4 lg:pr-8 group-hover:text-[#54b0af] transition-colors">{{ t("messages.faq_5") }}</span>
                            <svg class="w-5 lg:w-6 h-5 lg:h-6 text-[#54b0af] transform transition-transform duration-500 flex-shrink-0"
                                :class="{ 'rotate-180': openFaq === 5 }" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-500 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0">
                            <div v-show="openFaq === 5" class="overflow-hidden">
                                <div class="px-6 lg:px-8 pb-4 lg:pb-6">
                                    <p class="text-gray-300 leading-relaxed text-sm lg:text-base">
                                        {{ t("messages.faq_answer_5") }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blog Section with card hover effects -->
        <section class="blog-section py-20 bg-white">
            <div class="container mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center mb-16 space-y-4" :class="{ 'animate-slide-up': isBlogVisible }">
                    <h2 class="text-4xl md:text-5xl font-bold text-[#002b44]">{{ t("messages.berita dan blog title") }}</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        {{ t("messages.berita dan blog deskripsi") }}
                    </p>
                </div>

                <!-- Blog Cards -->
                <div v-if="latestArtikel && latestArtikel.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="(artikel, index) in latestArtikel" :key="artikel.id"
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 hover:border-[#54b0af] transform hover:-translate-y-2"
                        :class="{ 
                            'animate-slide-up-1': isBlogVisible,
                            'animate-slide-up-2': isBlogVisible && index === 1,
                            'animate-slide-up-3': isBlogVisible && index === 2
                        }">
                        <!-- Image with zoom effect -->
                        <div class="relative h-56 overflow-hidden bg-gradient-to-br from-[#54b0af]/10 to-gray-100">
                            <img v-if="artikel.gambar_thumbnail" :src="artikel.gambar_thumbnail" :alt="artikel.judul"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-125">
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                            <!-- Category Badge with pulse -->
                            <div v-if="artikel.kategori"
                                class="absolute top-4 left-4 bg-[#54b0af] text-white px-3 py-1 rounded-full text-sm font-medium animate-pulse-badge">
                                {{ artikel.kategori }}
                            </div>
                            <!-- Overlay gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
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
                            </div>

                            <!-- Title -->
                            <h3 class="text-xl font-bold text-[#002b44] group-hover:text-[#54b0af] transition-colors line-clamp-2">
                                {{ artikel.judul }}
                            </h3>

                            <!-- Excerpt -->
                            <p class="text-gray-600 leading-relaxed line-clamp-3">
                                {{ artikel.excerpt }}
                            </p>

                            <!-- Read More Button -->
                            <a :href="`/artikel/${artikel.slug}`"
                                class="inline-flex items-center gap-2 text-[#54b0af] font-semibold hover:gap-4 transition-all duration-300 group/link">
                                {{ t("messages.baca selengkapnya") }}
                                <svg class="w-5 h-5 group-hover/link:translate-x-2 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 animate-fade-in">
                    <svg class="w-20 h-20 text-gray-300 mx-auto mb-4 animate-bounce-slow" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <p class="text-gray-500">Belum ada artikel tersedia</p>
                </div>

                <!-- View All Button -->
                <div v-if="latestArtikel && latestArtikel.length > 0" class="text-center mt-12">
                    <a href="/artikel"
                        class="group relative inline-flex items-center gap-2 bg-[#002b44] hover:bg-[#003a5c] text-white px-8 py-4 rounded-full font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 overflow-hidden">
                        <span class="relative z-10">{{ t("messages.lihat semua artikel") }}</span>
                        <svg class="relative z-10 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        <span class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></span>
                    </a>
                </div>
            </div>
        </section>

    </FrontendLayout>
</template>

<style scoped>
/* Hero section background */
.hero-section {
    background-image: url('/background-herosection.png');
    background-size: cover;
    background-position: 72% center; /* mobile: geser ke kanan agar ilustrasi kelihatan */
}

@media (min-width: 1024px) {
    .hero-section {
        background-position: center center;
    }
}

/* Existing animations */
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

/* New enhanced animations */
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes bounce-slow {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-left {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slide-right {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes wave {
    0%, 100% {
        d: path("M0,64 C360,160 1080,0 1440,96 L1440,120 L0,120 Z");
    }
    50% {
        d: path("M0,96 C360,0 1080,160 1440,64 L1440,120 L0,120 Z");
    }
}

@keyframes particle-float {
    0%, 100% {
        transform: translate(0, 0);
        opacity: 0.3;
    }
    50% {
        transform: translate(100px, -100px);
        opacity: 0.7;
    }
}

@keyframes pulse-badge {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Animation classes */
.animate-fade-in-up {
    animation: fade-in-up 1s ease-out;
}

.animate-fade-in {
    animation: fade-in 1.2s ease-out;
}

.animate-fade-in-delayed {
    animation: fade-in 1.5s ease-out 0.3s both;
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

.animate-blob {
    animation: blob 7s infinite;
}

.animate-gradient {
    background: linear-gradient(270deg, #54b0af, #002b44, #FCB415);
    background-size: 600% 600%;
    animation: gradient 5s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}

.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}

.animate-slide-up {
    animation: slide-up 0.8s ease-out;
}

.animate-slide-up-1 {
    animation: slide-up 0.8s ease-out 0.1s both;
}

.animate-slide-up-2 {
    animation: slide-up 0.8s ease-out 0.2s both;
}

.animate-slide-up-3 {
    animation: slide-up 0.8s ease-out 0.3s both;
}

.animate-slide-left {
    animation: slide-left 0.8s ease-out;
}

.animate-slide-right {
    animation: slide-right 0.8s ease-out;
}

.wave-animation path {
    animation: wave 8s ease-in-out infinite;
}

.animate-pulse-badge {
    animation: pulse-badge 2s ease-in-out infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Text gradient */
.text-gradient {
    background: linear-gradient(135deg, #54b0af 0%, #002b44 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Particle effects */
.particle {
    position: absolute;
    background: radial-gradient(circle, rgba(84, 176, 175, 0.3) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.particle-1 {
    width: 100px;
    height: 100px;
    top: 10%;
    left: 10%;
    animation: particle-float 15s ease-in-out infinite;
}

.particle-2 {
    width: 150px;
    height: 150px;
    top: 60%;
    right: 10%;
    animation: particle-float 20s ease-in-out infinite reverse;
}

.particle-3 {
    width: 80px;
    height: 80px;
    bottom: 20%;
    left: 30%;
    animation: particle-float 18s ease-in-out infinite;
    animation-delay: 3s;
}

.particle-4 {
    width: 120px;
    height: 120px;
    top: 40%;
    right: 25%;
    animation: particle-float 22s ease-in-out infinite;
    animation-delay: 5s;
}

.particle-5 {
    width: 90px;
    height: 90px;
    bottom: 40%;
    left: 60%;
    animation: particle-float 17s ease-in-out infinite reverse;
    animation-delay: 2s;
}

/* FAQ particles */
.faq-particle {
    position: absolute;
    background: radial-gradient(circle, rgba(84, 176, 175, 0.2) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.faq-particle-1 {
    width: 200px;
    height: 200px;
    top: 20%;
    left: 5%;
    animation: particle-float 25s ease-in-out infinite;
}

.faq-particle-2 {
    width: 150px;
    height: 150px;
    bottom: 30%;
    right: 10%;
    animation: particle-float 20s ease-in-out infinite reverse;
}

.faq-particle-3 {
    width: 180px;
    height: 180px;
    top: 50%;
    right: 30%;
    animation: particle-float 22s ease-in-out infinite;
    animation-delay: 4s;
}

/* Counter bounce effect */
.counter-bounce:hover {
    animation: bounce-slow 0.6s ease-in-out;
}

</style>