<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head } from '@inertiajs/vue3';


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
                    Tentang Kami
                </h1>
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
                        <h2 class="text-3xl font-bold text-[#002b44]">Tentang Bilikbecakap</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Bilikbecakap lahir dari keresahan dan permasalahan yang muncul di Desa Senyubuk, yaitu belum
                            adanya arsip atau pendataan bahasa daerah setempat. Kondisi ini menimbulkan kekhawatiran di
                            kalangan masyarakat dan pemerintah desa akan kemungkinan hilangnya identitas bahasa daerah
                            Melayu Belitung. Melalui Bilikbecakap, dihadirkan berbagai fitur seperti penerjemah bahasa, konten
                            informasi, serta video dan modul pembelajaran bahasa Melayu Belitung. Inisiatif ini
                            berupaya menjaga warisan bahasa daerah sekaligus memperkenalkan kekayaan budaya lokal
                            melalui pendekatan digital.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Bilikbecakap pertama kali dikembangkan dalam program Kemah Budaya Kaum Muda (KBKM) 2023,
                            sebagai ruang inkubasi yang mewadahi inisiatif kaum muda dalam menciptakan karya dan inovasi
                            berbasis STEAM (Science, Technology, Engineering, Art, and Mathematics).

                            Saat ini, Bilikbecakap dikelola dan terus dikembangkan oleh Yayasan Basamudera Indonesia,
                            yang dibentuk dari tim Senyubuk KBKM 2023. Yayasan ini berkomitmen untuk melanjutkan
                            semangat kolaborasi, inovasi, dan pelestarian bahasa daerah agar tetap hidup dan relevan di
                            era digital.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-gradient-to-t from-[#54b0af]/5 via-white to-white">
            <div class="max-w-6xl mx-auto px-6 text-center space-y-10">
                <div class="space-y-5">
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-[#002b44] leading-tight">
                        Mari Bersama Melestarikan Bahasa Daerah
                    </h2>
                    <p class="text-lg md:text-xl text-gray-700 max-w-4xl mx-auto leading-relaxed">
                        Setiap kosakata, cerita rakyat, atau modul pembelajaran yang kamu bagikan
                        adalah <span class="font-semibold text-[#54b0af]">benih pelestarian budaya</span> —
                        <em>semuanya berharga</em>.
                    </p>
                </div>
            </div>
        </section>

    </FrontendLayout>
</template>