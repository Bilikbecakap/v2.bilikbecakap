<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();
</script>

<template>
    <Head title="Aplikasi Bilikbecakap" />

    <FrontendLayout>

        <!-- Hero Section -->
        <section class="min-h-screen relative overflow-hidden bg-gradient-to-br from-[#002b44] via-[#00395e] to-[#003d5c] flex items-center">

            <!-- Decorative blobs -->
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-[#54b0af]/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-[#FCB415]/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-[#54b0af]/5 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Dot grid pattern -->
            <div class="absolute inset-0 pointer-events-none opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="dots" width="24" height="24" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1" fill="white"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#dots)"/>
                </svg>
            </div>

            <div class="container mx-auto px-6 lg:px-8 relative z-10 py-20 lg:py-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                    <!-- Left: Content -->
                    <div class="space-y-6">
                        <!-- Beta badge -->
                        <div class="inline-flex items-center gap-2">
                            <span class="bg-[#FCB415] text-[#002b44] text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Beta</span>
                            <span class="text-[#54b0af] text-sm font-medium">{{ t("messages.app_hero_available") }}</span>
                        </div>

                        <!-- Title -->
                        <div class="space-y-3">
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight">
                                {{ t("messages.app_hero_title_1") }}<br>
                                <span class="text-[#54b0af]">{{ t("messages.app_hero_title_2") }}</span>
                            </h1>
                            <p class="text-gray-300 text-lg leading-relaxed max-w-lg">
                                {{ t("messages.app_hero_description") }}
                            </p>
                        </div>

                        <!-- Feature chips -->
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/10 text-white/80 text-xs px-3 py-1.5 rounded-full">
                                <svg class="w-3.5 h-3.5 text-[#54b0af]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ t("messages.kamus digital") }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/10 text-white/80 text-xs px-3 py-1.5 rounded-full">
                                <svg class="w-3.5 h-3.5 text-[#54b0af]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ t("messages.app_feature_penerjemah") }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/10 text-white/80 text-xs px-3 py-1.5 rounded-full">
                                <svg class="w-3.5 h-3.5 text-[#54b0af]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ t("messages.stat_modul_label") }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/10 text-white/80 text-xs px-3 py-1.5 rounded-full">
                                <svg class="w-3.5 h-3.5 text-[#54b0af]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ t("messages.app_feature_offline") }}
                            </span>
                        </div>

                        <!-- Beta notice -->
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex gap-3">
                            <svg class="w-5 h-5 text-[#FCB415] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-300 text-sm leading-relaxed" v-html="t('messages.app_beta_notice')"></p>
                        </div>

                        <!-- CTA -->
                        <div class="flex flex-col sm:flex-row items-start gap-3 pt-2">
                            <a href="#download"
                                class="group inline-flex items-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-7 py-3.5 rounded-full font-semibold text-base transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                {{ t("messages.app_hero_cta") }}
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Phone Mockup Image -->
                    <div class="flex justify-center items-center">
                        <div class="relative">
                            <div class="absolute inset-0 bg-[#54b0af]/20 rounded-full blur-3xl scale-90 pointer-events-none"></div>
                            <img
                                src="/mockuphp.png"
                                alt="Mockup Aplikasi Bilikbecakap"
                                class="relative w-full max-w-sm lg:max-w-md xl:max-w-lg drop-shadow-2xl"
                            />
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Download Section -->
        <section id="download" class="py-20 bg-white">
            <div class="container mx-auto px-6 lg:px-8">
                <div class="max-w-2xl mx-auto text-center space-y-8">

                    <div class="space-y-3">
                        <h2 class="text-3xl md:text-4xl font-bold text-[#002b44]">{{ t("messages.app_download_title") }}</h2>
                        <p class="text-gray-600 text-lg">
                            {{ t("messages.app_download_desc") }}
                        </p>
                    </div>

                    <!-- Download card -->
                    <div class="bg-gradient-to-br from-[#002b44]/5 to-[#54b0af]/10 border border-[#54b0af]/20 rounded-2xl p-8 space-y-6">

                        <!-- Version info -->
                        <div class="flex items-center justify-center gap-3">
                            <div class="w-14 h-14 bg-[#54b0af] rounded-2xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="font-bold text-[#002b44] text-lg">Bilikbecakap</div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-500">{{ t("messages.app_version_beta") }}</span>
                                    <span class="bg-[#FCB415] text-[#002b44] text-xs font-bold px-2 py-0.5 rounded-full">BETA</span>
                                </div>
                            </div>
                        </div>

                        <!-- Download button -->
                        <a href="/download/bilikbecakap.apk" download
                            class="group inline-flex items-center gap-3 bg-[#002b44] hover:bg-[#003d5c] text-white px-8 py-4 rounded-2xl font-semibold text-base transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1 w-full justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.523 15.341c-.297 0-.535-.238-.535-.536V8.69a.535.535 0 01.535-.535h.601c.297 0 .536.238.536.535v6.115c0 .298-.239.536-.536.536h-.601zM3.5 8.155v7.69c0 .932.753 1.686 1.686 1.686h13.628c.932 0 1.686-.754 1.686-1.686v-7.69H3.5zm8.5 6.574a4.287 4.287 0 110-8.573 4.287 4.287 0 010 8.573zm0-1.286a3 3 0 100-6 3 3 0 000 6zm0-1.571a1.429 1.429 0 110-2.858 1.429 1.429 0 010 2.858z"/>
                            </svg>
                            {{ t("messages.app_download_apk") }}
                            <svg class="w-4 h-4 group-hover:translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>

                        <p class="text-xs text-gray-400">
                            {{ t("messages.app_download_note") }}
                        </p>
                    </div>

                </div>
            </div>
        </section>

    </FrontendLayout>
</template>
