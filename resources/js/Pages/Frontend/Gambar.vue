<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const selectedImage = ref(null);
const currentImageIndex = ref(0);

defineProps({
    galeri: Object,
});

const openImageModal = (item, index) => {
    selectedImage.value = item;
    currentImageIndex.value = index;
};

const closeImageModal = () => {
    selectedImage.value = null;
};

const nextImage = (galeriData) => {
    if (currentImageIndex.value < galeriData.length - 1) {
        currentImageIndex.value++;
        selectedImage.value = galeriData[currentImageIndex.value];
    }
};

const prevImage = (galeriData) => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
        selectedImage.value = galeriData[currentImageIndex.value];
    }
};
</script>

<template>
    <Head title="Galeri" />

    <FrontendLayout>
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-[#54b0af]/10 via-white to-[#002b44]/5 py-16 overflow-hidden min-h-[300px] md:min-h-[400px] flex items-center justify-center">
            <div class="absolute inset-0 z-0">
                <img src="/background/breadcrumb.jpg" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <div class="absolute -top-10 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <div class="max-w-4xl mx-auto text-center space-y-4 relative z-10 px-6">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-[#002b44] leading-tight">
                    Galeri Bilikbecakap
                </h1>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">
                    Jelajahi koleksi kegiatan bilikbecakap, visual budaya, tradisi, dan keindahan alam Melayu Belitung
                </p>
            </div>

            <div class="absolute bottom-0 left-0 right-0 overflow-hidden leading-[0]">
                <svg class="relative block w-full h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none">
                    <path d="M0,64 C360,160 1080,0 1440,96 L1440,120 L0,120 Z" fill="#F9FAFB"></path>
                </svg>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-6">
                <!-- Gallery Grid -->
                <div v-if="galeri.data && galeri.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="(item, index) in galeri.data" :key="item.id"
                        @click="openImageModal(item, index)"
                        class="group relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer bg-white aspect-square">
                        
                        <img :src="`/storage/${item.gambar}`" :alt="item.keterangan" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Dark Overlay on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <!-- Zoom Icon -->
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                            <div class="w-16 h-16 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-xl">
                                <svg class="w-8 h-8 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Description Text -->
                        <div class="absolute inset-x-0 bottom-0 p-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <p class="text-white font-semibold text-base line-clamp-2">
                                {{ item.keterangan || 'Galeri Budaya Belitung' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-500 text-lg">Galeri belum memiliki gambar</p>
                </div>

                <!-- Pagination -->
                <div v-if="galeri.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between mt-12 pt-8 border-t border-gray-200 gap-4">
                    <div class="text-sm text-gray-600">
                        Menampilkan {{ galeri.from }} - {{ galeri.to }} dari {{ galeri.total }} gambar
                    </div>
                    <div class="flex gap-3">
                        <Link v-if="galeri.prev_page_url" :href="galeri.prev_page_url" 
                            class="px-6 py-3 border-2 border-[#54b0af] text-[#54b0af] font-semibold rounded-lg hover:bg-[#54b0af] hover:text-white transition-all duration-300">
                            ← Sebelumnya
                        </Link>
                        <Link v-if="galeri.next_page_url" :href="galeri.next_page_url" 
                            class="px-6 py-3 bg-[#54b0af] text-white font-semibold rounded-lg hover:bg-[#459a99] transition-all duration-300">
                            Selanjutnya →
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Image Modal/Lightbox -->
        <Transition name="modal">
            <div v-if="selectedImage" 
                @click="closeImageModal"
                class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-xl bg-black/30 p-4">
                
                <!-- Close Button -->
                <button @click.stop="closeImageModal"
                    class="absolute top-4 right-4 sm:top-6 sm:right-6 z-60 w-14 h-14 bg-white/90 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-gray-700 hover:text-[#54b0af] shadow-xl transition-all duration-300 group">
                    <svg class="w-7 h-7 group-hover:rotate-90 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Previous Button -->
                <button v-if="currentImageIndex > 0"
                    @click.stop="prevImage(galeri.data)"
                    class="absolute left-2 sm:left-6 z-60 w-14 h-14 bg-white/90 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-gray-700 hover:text-[#54b0af] shadow-xl transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Next Button -->
                <button v-if="currentImageIndex < galeri.data.length - 1"
                    @click.stop="nextImage(galeri.data)"
                    class="absolute right-2 sm:right-6 z-60 w-14 h-14 bg-white/90 hover:bg-white backdrop-blur-md rounded-full flex items-center justify-center text-gray-700 hover:text-[#54b0af] shadow-xl transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Modal Content -->
                <div @click.stop class="relative max-w-6xl max-h-[90vh] w-full mx-auto">
                    <!-- Image Container -->
                    <div class="relative bg-white/95 backdrop-blur-xl rounded-3xl overflow-hidden shadow-2xl border-4 border-white/50">
                        <img :src="`/storage/${selectedImage.gambar}`" 
                            :alt="selectedImage.keterangan"
                            class="w-full h-auto max-h-[75vh] object-contain mx-auto">
                        
                        <!-- Image Info Bar -->
                        <div class="bg-gradient-to-t from-white via-white/95 to-transparent absolute bottom-0 left-0 right-0 p-4 sm:p-6 border-t border-gray-200/50">
                            <div>
                                <p class="text-base sm:text-lg font-bold text-gray-800 mb-1">{{ selectedImage.keterangan || 'Galeri Budaya Belitung' }}</p>
                                <div class="flex items-center gap-4 text-xs sm:text-sm text-gray-600">
                                    <span class="inline-flex items-center gap-1.5 bg-[#54b0af]/10 px-3 py-1.5 rounded-full">
                                        <svg class="w-4 h-4 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="font-semibold text-[#54b0af]">Foto {{ currentImageIndex + 1 }} dari {{ galeri.data.length }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </FrontendLayout>
</template>

<style scoped>
/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
}

/* Aspect Ratio Square */
.aspect-square {
    aspect-ratio: 1 / 1;
}

/* Backdrop Blur Enhancement */
.backdrop-blur-xl {
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(20px);
}
</style>