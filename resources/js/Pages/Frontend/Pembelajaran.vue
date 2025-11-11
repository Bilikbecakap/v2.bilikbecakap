<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();
const props = defineProps({
    modul: Object,
    categoryList: Array,
    search: String,
    category: String,
    sort: String,
    direction: String,
});

const searchQuery = ref(props.search || '');
const selectedCategory = ref(props.category || '');
const sortOption = ref(props.sort || 'created_at');
const sortDirection = ref(props.direction || 'desc');

const handleSearch = () => {
    applyFilters();
};

const handleCategoryChange = () => {
    applyFilters();
};

const handleSort = (field) => {
    if (sortOption.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortOption.value = field;
        sortDirection.value = 'desc';
    }
    applyFilters();
};

const applyFilters = () => {
    router.get(
        '/pembelajaran',
        {
            search: searchQuery.value || undefined,
            category: selectedCategory.value || undefined,
            sort: sortOption.value,
            direction: sortDirection.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const clearSearch = () => {
    searchQuery.value = '';
    applyFilters();
};

// Reset filters saat search dihapus
watch(searchQuery, () => {
    if (!searchQuery.value) clearSearch();
});
</script>

<template>
    <Head title="Modul Pembelajaran - Bilik Bercakap" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(253,202,211,0.17)]"></div>
            </div>

            <!-- Decorative -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <div class="container mx-auto px-6 relative z-20">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-[#54b0af] mb-4 drop-shadow-sm">
                        {{ t('messages.modul pembelajaran bahasa belitung') }}
                    </h1>
                    <p class="text-lg text-[#002b44]/80 max-w-2xl mx-auto drop-shadow-sm">
                        {{ t('messages.modul pembelajaran bahasa belitung deskripsi') }}
                    </p>
                </div>

                <!-- Search & Filters -->
                <div class="max-w-4xl mx-auto mb-12 space-y-6">
                    <!-- Search -->
                    <form @submit.prevent="handleSearch" class="relative">
                        <input v-model="searchQuery" type="text" :placeholder="t('messages.pembelajaran placeholder')"
                            class="w-full px-6 py-4 pr-14 rounded-2xl border-2 border-white/50 bg-white/90 backdrop-blur-sm text-[#002b44] placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent shadow-lg" />
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-[#FCB415] hover:bg-[#e0a013] text-white p-3 rounded-xl transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>

                    <!-- Active Search Info -->
                    <div v-if="search || category" class="flex flex-wrap items-center gap-3 text-sm text-[#002b44]/70">
                        <span v-if="search">{{ t('messages.Pencarian') }}: <span class="font-semibold">"{{ search }}"</span></span>
                        <span v-if="category">
                            Kategori:
                            <span class="font-semibold">
                                {{ categoryList.find(c => c.id == category)?.nama_kategori || 'Unknown' }}
                            </span>
                        </span>
                        <button @click="searchQuery = ''; selectedCategory = ''; applyFilters()"
                            class="text-[#54b0af] hover:text-[#459a99] font-medium">
                            {{ t('messages.Hapus pencarian') }}
                        </button>
                    </div>
                </div>

                <!-- Grid Modul -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-12">
                    <div v-for="item in modul.data" :key="item.id"
                        class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                        <a :href="`/pembelajaran/${item.slug}`" class="block">
                            <div class="aspect-video bg-gradient-to-br from-[#54b0af]/20 to-[#FCB415]/20 relative overflow-hidden">
                                <img 
                                    v-if="item.thumbnail" 
                                    :src="`/storage/${item.thumbnail}`" 
                                    :alt="item.title"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                                />
                                <div v-else class="flex items-center justify-center h-full">
                                    <div class="bg-white/80 rounded-xl w-20 h-20 flex items-center justify-center shadow-md">
                                        <svg class="w-12 h-12 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-[#002b44] text-lg line-clamp-2 mb-2">{{ item.title }}</h3>
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                    {{ item.deskripsi || 'Tidak ada deskripsi.' }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-[#002b44]/60">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ item.category?.nama_kategori || 'Umum' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        {{ item.view_count }} {{ t('messages.views') }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!modul.data || modul.data.length === 0"
                    class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 p-20 text-center">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="text-xl font-medium text-gray-600">
                        {{ search || category ? t('messages.no_results_found') : t('messages.no_data_available') }}
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="modul.data && modul.data.length > 0"
                    class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-4 mt-8">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            {{ t('messages.menampilkan') }} {{ modul.from }} - {{ modul.to }} {{ t('messages.dari') }} {{ modul.total }} modul
                        </div>

                        <div class="flex items-center gap-1">
                            <!-- Previous -->
                            <component :is="modul.prev_page_url ? 'a' : 'span'" :href="modul.prev_page_url"
                                class="p-2 rounded-lg transition-all"
                                :class="modul.prev_page_url
                                    ? 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </component>

                            <!-- Page Numbers -->
                            <template v-for="(link, i) in modul.links" :key="i">
                                <component v-if="link.url && !isNaN(link.label)" :is="'a'" :href="link.url"
                                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                                    :class="link.active
                                        ? 'bg-[#54b0af] text-white shadow-md'
                                        : 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'">
                                    {{ link.label }}
                                </component>
                                <span v-else-if="link.label === '...'" class="px-3 py-1.5 text-sm text-gray-500">
                                    ...
                                </span>
                            </template>

                            <!-- Next -->
                            <component :is="modul.next_page_url ? 'a' : 'span'" :href="modul.next_page_url"
                                class="p-2 rounded-lg transition-all"
                                :class="modul.next_page_url
                                    ? 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </component>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="mt-8 bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-[#54b0af]/10 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-[#002b44] mb-2">{{ t('messages.tentang modul pembelajaran') }}</h4>
                            <p class="text-gray-600 leading-relaxed">
                                {{ t('messages.tentang modul pembelajaran deskripsi') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
/* Custom scrollbar untuk grid horizontal jika overflow */
.grid {
    scrollbar-width: thin;
    scrollbar-color: #54b0af #f1f1f1;
}

.grid::-webkit-scrollbar {
    height: 8px;
}

.grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.grid::-webkit-scrollbar-thumb {
    background: #54b0af;
    border-radius: 4px;
}

.grid::-webkit-scrollbar-thumb:hover {
    background: #459a99;
}
</style>