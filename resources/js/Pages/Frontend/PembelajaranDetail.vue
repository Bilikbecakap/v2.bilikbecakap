<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';

const props = defineProps({
    modul: Object,
    otherModules: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

// YouTube ID Extract
const getVideoEmbedId = (videoUrl) => {
    if (!videoUrl) return null;
    const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i;
    const match = videoUrl.match(regExp);
    return match ? match[1] : null;
};

const getYouTubeEmbedUrl = computed(() => {
    const videoId = getVideoEmbedId(props.modul.video_embed);
    return videoId ? `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1&showinfo=0&controls=1` : null;
});

// PDF URL
const getPdfUrl = (pdfFile) => {
    return pdfFile ? `/storage/${pdfFile}` : null;
};

// Share
const share = () => {
    if (navigator.share) {
        navigator.share({
            title: props.modul.title,
            text: props.modul.deskripsi,
            url: window.location.href,
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link berhasil disalin!');
    }
};
</script>

<template>

    <Head :title="`${modul.title} - Modul Pembelajaran`" />

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
                <Link :href="'/pembelajaran'"
                    class="inline-flex items-center gap-2 text-[#54b0af] hover:text-[#459a99] mb-6 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
                </Link>

                <!-- 2/3 + 1/3 Layout -->
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- 2/3: Konten Utama -->
                    <div class="lg:col-span-2">
                        <div
                            class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 overflow-hidden">
                            <!-- Thumbnail -->
                            <div class="aspect-video relative overflow-hidden">
                                <img v-if="modul.thumbnail" :src="`/storage/${modul.thumbnail}`" :alt="modul.title"
                                    class="w-full h-full object-cover" />
                                <div v-else
                                    class="flex items-center justify-center h-full bg-gradient-to-br from-[#54b0af]/20 to-[#FCB415]/20">
                                    <div
                                        class="bg-white/80 rounded-xl w-32 h-32 flex items-center justify-center shadow-lg">
                                        <svg class="w-16 h-16 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Konten -->
                            <div class="p-6 md:p-8">
                                <div class="flex items-start justify-between mb-6">
                                    <div>
                                        <h1 class="text-2xl md:text-3xl font-bold text-[#002b44] mb-2">
                                            {{ modul.title }}
                                        </h1>
                                        <div class="flex flex-wrap items-center gap-3 text-sm text-[#002b44]/70">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                {{ modul.category?.nama_kategori || 'Umum' }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ formatDate(modul.created_at) }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ modul.view_count }} kali
                                            </span>
                                        </div>
                                    </div>
                                    <button @click="share"
                                        class="bg-[#FCB415] hover:bg-[#e0a013] text-white p-3 rounded-xl transition-colors"
                                        aria-label="Bagikan modul ini">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Deskripsi (Quill HTML) -->
                                <div v-if="modul.deskripsi"
                                    class="mb-8 quill-content prose prose-lg max-w-none text-gray-700 leading-relaxed">
                                    <div v-html="modul.deskripsi"></div>
                                </div>

                                <!-- Konten Quill -->
                                <div v-if="modul.content" class="mb-8 quill-content prose prose-lg max-w-none"
                                    v-html="modul.content"></div>

                                <!-- PDF: CLEAN VIEWER (tanpa toolbar) -->
                                <div v-if="modul.pdf_file" class="mb-8">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-lg font-semibold text-[#002b44]">File PDF</h3>
                                        <a :href="getPdfUrl(modul.pdf_file)" download
                                            class="bg-[#54b0af] hover:bg-[#459a99] text-white px-4 py-2 rounded-xl text-sm flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl overflow-hidden border border-gray-200">
                                        <embed
                                            :src="`${getPdfUrl(modul.pdf_file)}#toolbar=0&navpanes=0&scrollbar=0&view=FitH`"
                                            type="application/pdf" class="w-full h-96 md:h-[600px]"
                                            @error="console.error('PDF gagal dimuat')" />
                                    </div>
                                </div>

                                <!-- YouTube Video -->
                                <div v-if="getYouTubeEmbedUrl" class="mb-8">
                                    <h3 class="text-lg font-semibold text-[#002b44] mb-3">Video Pembelajaran</h3>
                                    <div class="aspect-video bg-black rounded-xl overflow-hidden shadow-lg">
                                        <iframe :src="getYouTubeEmbedUrl" class="w-full h-full" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 1/3: Sidebar -->
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-6 space-y-6 sticky top-6">
                            <!-- Modul Lainnya -->
                            <div>
                                <h3 class="text-lg font-bold text-[#002b44] mb-3">Modul Lainnya</h3>
                                <div class="space-y-0 divide-y divide-gray-100">
                                    <Link v-for="item in otherModules" :key="item.id"
                                        :href="`/pembelajaran/${item.slug}`"
                                        class="block p-4 bg-[#54b0af]/5 transition-all group">
                                    <div class="flex gap-3 items-start">
                                        <div
                                            class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gradient-to-br from-[#54b0af]/10 to-[#FCB415]/10">
                                            <img v-if="item.thumbnail" :src="`/storage/${item.thumbnail}`"
                                                class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4
                                                class="font-medium text-[#002b44] text-sm line-clamp-2 group-hover:text-[#54b0af]">
                                                {{ item.title }}
                                            </h4>
                                            <p class="text-xs text-[#002b44]/60 mt-1">
                                                {{ item.category?.nama_kategori || 'Umum' }} • {{ item.view_count }}
                                                dilihat
                                            </p>
                                        </div>
                                    </div>
                                    </Link>
                                </div>
                            </div>

                            <!-- Petunjuk -->
                            <div class="p-4 bg-white/90 backdrop-blur-sm rounded-xl border border-white/20">
                                <div class="flex items-start gap-3">
                                    <div class="bg-[#54b0af]/10 p-2 rounded-lg">
                                        <svg class="w-5 h-5 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-[#002b44] text-sm mb-1">Petunjuk</h4>
                                        <p class="text-xs text-gray-600 leading-relaxed">
                                            Baca deskripsi, tonton video, unduh PDF. Lihat modul lainnya di samping.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Lihat Semua -->
                            <div class="text-center pt-2">
                                <Link :href="'/pembelajaran'"
                                    class="text-sm text-[#54b0af] hover:text-[#459a99] font-medium">
                                Lihat semua modul
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style></style>