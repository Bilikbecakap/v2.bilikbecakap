<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const selectedImage = ref(null);

defineProps({
    galeri: Object,
});
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

            <div class="max-w-4xl mx-auto text-center space-y-4 relative z-10">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-[#002b44] leading-tight">
                    Galeri Budaya Belitung
                </h1>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">
                    Jelajahi koleksi visual budaya, tradisi, dan keindahan alam Melayu Belitung
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
                <!-- Gallery Grid with Mixed Layouts -->
                <div v-if="galeri.data && galeri.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 auto-rows-[280px]">
                    <!-- Featured Large Item (1st Item) -->
                    <div v-if="galeri.data[0]" 
                        class="lg:col-span-2 lg:row-span-2 group relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer bg-gray-200">
                        <img :src="`/storage/${galeri.data[0].gambar}`" :alt="galeri.data[0].keterangan" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Dark Overlay on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <!-- Description Text -->
                        <div class="absolute inset-0 flex flex-col justify-end p-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <h3 class="text-white font-bold text-xl line-clamp-3">
                                {{ galeri.data[0].keterangan || 'Galeri' }}
                            </h3>
                        </div>
                    </div>

                    <!-- Standard Grid Items (2-6) -->
                    <div v-for="(item, index) in galeri.data.slice(1, 6)" :key="item.id"
                        class="group relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer bg-gray-200">
                        <img :src="`/storage/${item.gambar}`" :alt="item.keterangan"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Dark Overlay on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <!-- Description Text -->
                        <div class="absolute inset-0 flex flex-col justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <h3 class="text-white font-semibold text-sm line-clamp-2">
                                {{ item.keterangan || 'Galeri' }}
                            </h3>
                        </div>
                    </div>

                    <!-- Tall Item (6th) -->
                    <div v-if="galeri.data[5]"
                        class="lg:row-span-2 group relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer bg-gray-200">
                        <img :src="`/storage/${galeri.data[5].gambar}`" :alt="galeri.data[5].keterangan"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Dark Overlay on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <!-- Description Text -->
                        <div class="absolute inset-0 flex flex-col justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <h3 class="text-white font-semibold text-sm line-clamp-2">
                                {{ galeri.data[5].keterangan || 'Galeri' }}
                            </h3>
                        </div>
                    </div>

                    <!-- Remaining Items in Regular Grid -->
                    <div v-for="item in galeri.data.slice(6)" :key="item.id"
                        class="group relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer bg-gray-200">
                        <img :src="`/storage/${item.gambar}`" :alt="item.keterangan"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Dark Overlay on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <!-- Description Text -->
                        <div class="absolute inset-0 flex flex-col justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <h3 class="text-white font-semibold text-sm line-clamp-2">
                                {{ item.keterangan || 'Galeri' }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <p class="text-gray-500 text-lg">Galeri belum memiliki gambar</p>
                </div>

                <!-- Pagination -->
                <div v-if="galeri.last_page > 1" class="flex flex-col items-center justify-between mt-12 pt-8 border-t border-gray-200 space-y-4">
                    <div class="text-sm text-gray-600">
                        Menampilkan {{ galeri.from }} - {{ galeri.to }} dari {{ galeri.total }} gambar
                    </div>
                    <div class="flex gap-3">
                        <Link v-if="galeri.prev_page_url" :href="galeri.prev_page_url" 
                            class="px-5 py-3 border border-[#54b0af] text-[#54b0af] font-semibold rounded-lg hover:bg-[#54b0af]/10 transition-colors duration-300">
                            ← Sebelumnya
                        </Link>
                        <Link v-if="galeri.next_page_url" :href="galeri.next_page_url" 
                            class="px-5 py-3 bg-[#54b0af] text-white font-semibold rounded-lg hover:bg-[#459a99] transition-colors duration-300">
                            Selanjutnya →
                        </Link>
                    </div>
                </div>
            </div>
        </section>

    </FrontendLayout>
</template>

<style scoped>
/* Optional: Add smooth transitions */
.auto-rows-\[280px\] {
    grid-auto-rows: 280px;
}

@media (max-width: 1024px) {
    .auto-rows-\[280px\] {
        grid-auto-rows: 250px;
    }
}

@media (max-width: 640px) {
    .auto-rows-\[280px\] {
        grid-auto-rows: 200px;
    }
}
</style>