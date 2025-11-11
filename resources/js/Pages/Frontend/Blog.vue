<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps({
    artikel: Object,
    populerArtikel: Array,
    kategoriList: Array,
    search: String,
    kategori: String,
    sort: String,
    direction: String,
    locale: String,
});

const { t } = useTranslations();
const searchQuery = ref(props.search || '');
const selectedKategori = ref(props.kategori || '');

const handleSearch = () => {
    router.get('/artikel', {
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleKategoryFilter = (categoryId) => {
    router.get('/artikel', {
        kategori: categoryId,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const truncateText = (text, maxWords = 30) => {
    if (!text) return '';
    const plainText = text.replace(/<[^>]*>/g, '');
    const words = plainText.split(' ').filter(word => word.trim().length > 0);
    return words.slice(0, maxWords).join(' ') + (words.length > maxWords ? '...' : '');
};

const getExcerpt = (artikel) => {
    const konten = artikel.konten;
    return truncateText(konten, 30);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const getTitle = (artikel) => {
    return artikel.judul || 'Artikel';
};
</script>

<template>
    <Head title="Artikel & Blog - Bilik Bercakap" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <div class="container mx-auto px-6 relative z-20">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-[#54b0af] mb-4 drop-shadow-sm">
                        {{ t('messages.blog title') }}
                    </h1>
                    <p class="text-lg text-[#002b44]/80 max-w-2xl mx-auto drop-shadow-sm">
                        {{ t('messages.blog title deskripsi') }}
                    </p>
                </div>

                <!-- Main Layout: 2/3 + 1/3 -->
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Left Column: Articles (2/3) -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Article Cards -->
                        <div v-if="artikel.data && artikel.data.length > 0" v-for="(post, index) in artikel.data"
                            :key="post.id" class="group">
                            <!-- Article Content -->
                            <div
                                class="bg-white/95 backdrop-blur-sm rounded-lg overflow-hidden border border-gray-100 hover:border-[#54b0af] transition-all duration-300">
                                <!-- Image -->
                                <div class="relative overflow-hidden h-80 md:h-96">
                                    <Link :href="`/artikel/${post.slug}`" class="block w-full h-full">
                                    <img v-if="post.gambar_thumbnail" :src="`/storage/${post.gambar_thumbnail}`"
                                        :alt="getTitle(post)"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div v-else
                                        class="w-full h-full bg-gradient-to-br from-[#54b0af]/20 to-[#54b0af]/10 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-300" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                    </Link>
                                </div>

                                <!-- Article Info -->
                                <div class="p-6 md:p-8">
                                    <!-- Meta Info -->
                                    <div class="flex flex-wrap items-center gap-4 mb-4">
                                        <!-- Views -->
                                        <div class="flex items-center gap-1 text-sm text-[#54b0af]">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                <path fill-rule="evenodd"
                                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-gray-700">{{ post.views_count || 0 }} {{ t('messages.views') }}</span>
                                        </div>

                                        <!-- Date -->
                                        <div class="flex items-center gap-1 text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ formatDate(post.tanggal_publish || post.created_at) }}</span>
                                        </div>

                                        <!-- Category -->
                                        <div v-if="post.kategori" class="flex items-center gap-1 text-sm">
                                            <span
                                                class="px-3 py-1 bg-[#54b0af]/10 text-[#54b0af] rounded-full text-xs font-medium">
                                                {{ post.kategori.nama_kategori }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <Link :href="`/artikel/${post.slug}`" class="block group/title">
                                    <h2
                                        class="text-2xl md:text-3xl font-bold text-[#002b44] mb-4 group-hover/title:text-[#54b0af] transition-colors">
                                        {{ getTitle(post) }}
                                    </h2>
                                    </Link>

                                    <!-- Description/Excerpt -->
                                    <p class="text-gray-600 leading-relaxed mb-6 line-clamp-3 text-base">
                                        {{ getExcerpt(post) }}
                                    </p>

                                    <!-- Read More Button -->
                                    <Link :href="`/artikel/${post.slug}`"
                                        class="inline-flex items-center gap-2 bg-[#54b0af] hover:bg-[#459a99] text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:translate-x-1">
                                    {{ t('messages.baca selengkapnya') }}
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    </Link>
                                </div>
                            </div>

                            <!-- Divider (except last) -->
                            <div v-if="index < artikel.data.length - 1" class="border-b border-gray-200 my-8"></div>
                        </div>

                        <!-- Empty State -->
                        <div v-else
                            class="bg-white/95 backdrop-blur-sm rounded-lg p-12 text-center border border-gray-100">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            <p class="text-xl font-medium text-gray-600 mb-2">
                                {{ search ? t('messages.no_results_found') : t('messages.no_data_available') }}
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="artikel.data && artikel.data.length > 0"
                            class="flex justify-center items-center gap-2 pt-8">
                            <component :is="artikel.prev_page_url ? 'a' : 'span'" :href="artikel.prev_page_url"
                                class="p-2 rounded-lg transition-all" :class="artikel.prev_page_url
                                    ? 'bg-white/95 text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300 cursor-pointer'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </component>

                            <template v-for="(link, i) in artikel.links" :key="i">
                                <component v-if="link.url && !isNaN(link.label)" :is="'a'" :href="link.url"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition-all"
                                    :class="link.active
                                        ? 'bg-[#54b0af] text-white'
                                        : 'bg-white/95 text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'">
                                    {{ link.label }}
                                </component>
                                <span v-else-if="link.label === '...'" class="px-2 text-gray-500">...</span>
                            </template>

                            <component :is="artikel.next_page_url ? 'a' : 'span'" :href="artikel.next_page_url"
                                class="p-2 rounded-lg transition-all" :class="artikel.next_page_url
                                    ? 'bg-white/95 text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300 cursor-pointer'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </component>
                        </div>
                    </div>

                    <!-- Right Sidebar (1/3) -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Search Section -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-[#54b0af] mb-4 flex items-center gap-2">
                                <span class="text-[#FCB415]">━━</span> {{ t('messages.cari') }}
                            </h3>
                            <form @submit.prevent="handleSearch" class="space-y-3">
                                <input v-model="searchQuery" type="text" placeholder="Keywords Here...."
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#54b0af] focus:outline-none transition-colors text-sm">
                                <button type="submit"
                                    class="w-full bg-[#54b0af] hover:bg-[#459a99] text-white py-3 rounded-lg font-semibold transition-all">
                                    {{ t('messages.cari') }}
                                </button>
                            </form>
                        </div>

                        <!-- Popular Feeds Section -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-[#002b44] mb-6 flex items-center gap-2">
                                <span class="text-[#FCB415]">━━</span> {{ t('messages.popular feeds') }}
                            </h3>
                            <div v-if="populerArtikel && populerArtikel.length > 0" class="space-y-4">
                                <Link v-for="item in populerArtikel" :key="item.id"
                                    :href="`/artikel/${item.slug}`"
                                    class="flex gap-3 pb-4 border-b border-gray-100 last:border-b-0 hover:opacity-80 transition-opacity group">
                                <!-- Thumbnail -->
                                <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                    <img v-if="item.gambar_thumbnail" :src="`/storage/${item.gambar_thumbnail}`"
                                        :alt="getTitle(item)"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="font-semibold text-[#002b44] text-sm line-clamp-2 group-hover:text-[#54b0af]">
                                        {{ getTitle(item) }}
                                    </h4>
                                    <p class="text-xs text-[#002b44] mt-2 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        {{ new Date(item.tanggal_publish || item.created_at).toLocaleDateString('id-ID',
                                            {
                                                day: 'numeric',
                                                month: 'short', year: 'numeric'
                                            }) }}
                                    </p>
                                </div>
                                </Link>
                            </div>
                            <div v-else class="text-center text-gray-500 py-6">
                                <p class="text-sm">Belum ada artikel populer</p>
                            </div>
                        </div>

                        <!-- Categories Section -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-[#002b44] mb-4 flex items-center gap-2">
                                <span class="text-[#FCB415]">━━</span> {{ t('messages.kategori') }}
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <!-- All Categories Badge -->
                                <Link href="/artikel"
                                    class="px-3 py-2 bg-[#54b0af]/10 text-[#54b0af] hover:bg-[#54b0af] hover:text-white rounded-full text-xs font-semibold uppercase tracking-wide transition-all">
                                    Semua
                                </Link>
                                
                                <!-- Individual Categories Badges -->
                                <Link v-for="cat in kategoriList" :key="cat.id"
                                    :href="`/artikel?kategori=${cat.id}`"
                                    :class="[
                                        'px-3 py-2 rounded-full text-xs font-semibold uppercase tracking-wide transition-all',
                                        kategori == cat.id
                                            ? 'bg-[#54b0af] text-white' 
                                            : 'bg-gray-100 text-[#002b44] hover:bg-[#54b0af]/10 hover:text-[#54b0af]'
                                    ]">
                                    {{ cat.nama_kategori }}
                                </Link>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="bg-gradient-to-br from-[#54b0af] to-[#459a99] rounded-lg p-6 text-white space-y-3">
                            <h3 class="font-bold text-lg">{{ t('messages.Ingin Bergabung') }}</h3>
                            <p class="text-sm opacity-90">{{ t('messages.Ingin Bergabung Deskripsi') }}</p>
                            <a href="/" class="inline-block w-full text-center bg-white text-[#54b0af] font-semibold py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                {{ t('messages.pelajari lebih lanjut') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
/* Smooth transitions */
:deep(input, select, button) {
    transition: all 0.3s ease;
}
</style>