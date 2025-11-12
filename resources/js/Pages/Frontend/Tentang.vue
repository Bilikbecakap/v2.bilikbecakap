<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations'

const { t } = useTranslations();
</script>

<template>

    <Head title="Tentang Kami" />

    <FrontendLayout>
        <!-- Hero Overlay Section -->
        <section
            class="relative bg-gradient-to-br from-[#54b0af]/10 via-white to-[#002b44]/5 py-16 overflow-hidden min-h-[300px] md:min-h-[400px] flex items-center justify-center">
            <!-- Background Elements -->
            <div class="absolute inset-0 z-0">
                <img src="/background/breadcrumb.jpg" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <!-- Decorative Circles -->
            <div class="absolute -top-10 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <!-- Centered Content -->
            <div class="max-w-4xl mx-auto text-center space-y-4 relative z-10">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-[#002b44] leading-tight">
                    {{ t('messages.tentang kami') }}
                </h1>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">
                    {{ t('messages.tentang kami deskripsi') }}
                </p>
            </div>

            <!-- Wave Divider -->
            <div class="absolute bottom-0 left-0 right-0 overflow-hidden leading-[0]">
                <svg class="relative block w-full h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120"
                    preserveAspectRatio="none">
                    <path d="M0,64 C360,160 1080,0 1440,96 L1440,120 L0,120 Z" fill="#F9FAFB"></path>
                </svg>
            </div>
        </section>

        <!-- Tentang Bilikbecakap -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Column: YouTube Video (menggantikan gambar) -->
                    <div class="relative group">
                        <div class="relative overflow-hidden rounded-2xl shadow-2xl aspect-video">
                            <iframe src="https://www.youtube.com/embed/SR0oBrDs8xg"
                                title="Tentang Bilik Becakap – Video Perkenalan" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen class="w-full h-full object-cover"></iframe>

                            <!-- Overlay Gradient (opsional, agar hover tetap konsisten) -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-[#002b44]/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none">
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
                    <div class="space-y-5 text-justify">
                        <h2 class="text-3xl font-bold text-[#54b0af]">{{ t('messages.about') }} Bilikbecakap</h2>
                        <p class="text-gray-700 leading-relaxed">
                            {{ t('messages.about_bilikbecakap_1') }}
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            {{ t('messages.about_bilikbecakap_2') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action & Collaboration Section -->
        <section class="py-20 bg-gradient-to-t from-[#54b0af]/5 via-white to-white">
            <div class="max-w-6xl mx-auto px-6 text-center space-y-10">
                <div class="space-y-5">
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-[#002b44] leading-tight">
                        {{ t('messages.ajakan_1') }}
                    </h2>
                    <p class="text-lg md:text-xl text-gray-700 max-w-4xl mx-auto leading-relaxed" v-html="t('messages.ajakan_1_deskripsi')">
                    </p>
                </div>

                <!-- Contribution Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
                    <!-- Card 1: Berkontribusi -->
                    <div
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-[#54b0af]/30">
                        <div class="mb-6 flex justify-center">
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-[#e0a013] to-[#e0a013] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-[#002b44] mb-3">{{ t('messages.card_1') }}</h3>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            {{ t('messages.card_1_deskripsi') }}
                        </p>
                    </div>

                    <!-- Card 2: Belajar & Berbagi -->
                    <div
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-[#54b0af]/30">
                        <div class="mb-6 flex justify-center">
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-[#e0a013] to-[#e0a013] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17.25c0 5.105 3.07 9.772 7.5 11.876m0-13c5.5 0 10 4.745 10 10.25 0 5.105-3.07 9.772-7.5 11.876M12 6.253L9.612 9.662m0 0C7.283 12.638 6 16.571 6 21c0 1.933.5 3.759 1.37 5.338m3.242-11.676c2.329-2.976 3.759-6.909 3.759-11.338 0-1.933-.5-3.759-1.37-5.338" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-[#002b44] mb-3">{{ t('messages.card_2') }}</h3>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            {{ t('messages.card_2_deskripsi') }}
                        </p>
                    </div>

                    <!-- Card 3: Kolaborasi -->
                    <div
                        class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-[#54b0af]/30">
                        <div class="mb-6 flex justify-center">
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-[#e0a013] to-[#e0a013] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-[#002b44] mb-3">{{ t('messages.card_3') }}</h3>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            {{ t('messages.card_3_deskripsi') }}
                        </p>
                    </div>
                </div>

                <!-- Bottom CTA -->
                <div class="mt-16 pt-12 border-t border-gray-200">
                    <p class="text-gray-700 text-lg mb-6">
                        {{ t('messages.ajakan_3') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Bilikbecakap Terus Berkembang dan Tumbuh -->
        <section class="py-20 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <!-- Main Title -->
                <div class="text-center space-y-4 mb-16">
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-[#002b44] leading-tight">
                        {{ t('messages.ajakan_2') }}
                    </h2>
                    <p class="text-lg text-gray-700 max-w-4xl mx-auto leading-relaxed">
                        {{ t('messages.ajakan_2_deskripsi') }}
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                    <!-- Feature 1: Observasi & Dokumentasi Budaya -->
                    <div
                        class="group bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-[#54b0af]/30">
                        <div class="mb-6 flex items-start">
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-[#54b0af] to-[#3d8b8a] rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-[#002b44]">{{ t('messages.card_4') }}</h3>
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            {{ t('messages.card_4_deskripsi') }}
                        </p>
                    </div>

                    <!-- Feature 2: Pengembangan Media Pembelajaran -->
                    <div
                        class="group bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-[#54b0af]/30">
                        <div class="mb-6 flex items-start">
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-[#54b0af] to-[#3d8b8a] rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17.25c0 5.105 3.07 9.772 7.5 11.876m0-13c5.5 0 10 4.745 10 10.25 0 5.105-3.07 9.772-7.5 11.876M12 6.253L9.612 9.662m0 0C7.283 12.638 6 16.571 6 21c0 1.933.5 3.759 1.37 5.338m3.242-11.676c2.329-2.976 3.759-6.909 3.759-11.338 0-1.933-.5-3.759-1.37-5.338" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-[#002b44]">{{ t('messages.card_5') }}</h3>
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            {{ t('messages.card_5_deskripsi') }}
                        </p>
                    </div>

                    <!-- Feature 3: Pelatihan Model Terjemahan -->
                    <div
                        class="group bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-[#54b0af]/30">
                        <div class="mb-6 flex items-start">
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-[#54b0af] to-[#3d8b8a] rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5.36-5.36l-.707-.707M9 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-[#002b44]">{{ t('messages.card_6') }}</h3>
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            {{ t('messages.card_6_deskripsi') }}
                        </p>
                    </div>

                    <!-- Feature 4: Pengucapan & Penuturan Kata -->
                    <div
                        class="group bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-[#54b0af]/30">
                        <div class="mb-6 flex items-start">
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-[#54b0af] to-[#3d8b8a] rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001 7.072m2.828-9.9a9 9 0 0012.728 0M9 4.804A7.968 7.968 0 015.172 4m13.656 0A7.968 7.968 0 0015 4.804" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-[#002b44]">{{ t('messages.card_7') }}</h3>
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            {{ t('messages.card_7_deskripsi') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>