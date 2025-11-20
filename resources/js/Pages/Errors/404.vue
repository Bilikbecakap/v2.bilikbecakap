<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="404 - Halaman Tidak Ditemukan" />

  <FrontendLayout>
    <div class="min-h-screen py-8 relative overflow-hidden flex items-center justify-center">
      <!-- Background -->
      <div class="absolute inset-0 z-0">
        <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
      </div>

      <!-- Decorative -->
      <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

      <!-- Content -->
      <div class="container mx-auto px-6 relative z-20">
        <div class="max-w-2xl mx-auto">
          <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl border border-white/20 p-8 text-center">
            <!-- Error Code -->
            <div class="mb-6">
              <h1 class="text-9xl font-bold text-[#54b0af] mb-4">404</h1>
              <div class="w-32 h-1 bg-gradient-to-r from-[#54b0af] to-[#459a99] mx-auto rounded-full"></div>
            </div>

            <!-- Icon -->
            <div class="mb-6">
              <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-r from-[#54b0af]/10 to-[#459a99]/10">
                <svg class="w-12 h-12 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>

            <!-- Message -->
            <h2 class="text-3xl font-bold text-slate-900 mb-4">
              Halaman Tidak Ditemukan
            </h2>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <Link
                :href="route('landing')"
                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#54b0af] to-[#459a99] text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Beranda
              </Link>
              
              <button
                @click="$router.back()"
                class="inline-flex items-center justify-center px-6 py-3 border-2 border-[#54b0af] text-[#54b0af] font-medium rounded-lg hover:bg-[#54b0af]/10 transition-all duration-200"
              >
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Halaman Sebelumnya
              </button>
            </div>

            <!-- Quick Links -->
            <div class="mt-8 pt-8 border-t border-slate-200">
              <p class="text-sm text-slate-500 mb-4">Atau kunjungi halaman lain:</p>
              <div class="flex flex-wrap gap-3 justify-center">
                <Link :href="route('kamus.public')" class="text-sm text-[#54b0af] hover:text-[#459a99] font-medium">
                  Kamus
                </Link>
                <span class="text-slate-300">•</span>
                <Link :href="route('penerjemah')" class="text-sm text-[#54b0af] hover:text-[#459a99] font-medium">
                  Penerjemah
                </Link>
                <span class="text-slate-300">•</span>
                <Link :href="route('pembelajaran')" class="text-sm text-[#54b0af] hover:text-[#459a99] font-medium">
                  Pembelajaran
                </Link>
                <span class="text-slate-300">•</span>
                <Link :href="route('kuis.index')" class="text-sm text-[#54b0af] hover:text-[#459a99] font-medium">
                  Kuis
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FrontendLayout>
</template>