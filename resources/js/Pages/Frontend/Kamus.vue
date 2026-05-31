<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps({
    kamus: Object,
    search: String,
    letter: String,
    letterCounts: {
        type: Object,
        default: () => ({})
    },
});

const { t } = useTranslations();
const searchQuery = ref(props.search || '');
const selectedLetter = ref(props.letter || '');
const isPlaying = ref(null);
const audioPlayer = ref(null);

const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

// Safe access to letterCounts
const counts = computed(() => props.letterCounts || {});

const handleSearch = () => {
    router.get('/kamus', { 
        search: searchQuery.value,
        letter: selectedLetter.value 
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByLetter = (letter) => {
    if (selectedLetter.value === letter) {
        selectedLetter.value = '';
    } else {
        selectedLetter.value = letter;
    }
    searchQuery.value = '';
    handleSearch();
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedLetter.value = '';
    handleSearch();
};

const playAudio = (kamusId, audioUrl) => {
    if (isPlaying.value === kamusId) {
        audioPlayer.value?.pause();
        isPlaying.value = null;
    } else {
        audioPlayer.value?.pause();
        audioPlayer.value = new Audio(audioUrl);
        audioPlayer.value.play();
        isPlaying.value = kamusId;

        audioPlayer.value.onended = () => {
            isPlaying.value = null;
        };
    }
};
</script>

<template>
    <Head title="Kamus Digital - Bilikbecakap" />

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
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-[#54b0af] mb-4 drop-shadow-sm">
                        {{ t('messages.kamus digital bahasa belitung') }}
                    </h1>
                    <p class="text-lg text-[#002b44]/80 max-w-2xl mx-auto drop-shadow-sm">
                        {{ t('messages.kamus digital bahasa belitung deskripsi') }}
                    </p>
                </div>

                <!-- Search -->
                <div class="max-w-3xl mx-auto mb-8">
                    <form @submit.prevent="handleSearch" class="relative">
                        <input v-model="searchQuery" type="text" :placeholder="t('messages.kamus placeholder')"
                            class="w-full px-6 py-4 pr-14 rounded-2xl border-2 border-white/50 bg-white/90 backdrop-blur-sm text-[#002b44] placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent shadow-lg" />
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-[#FCB415] hover:bg-[#e0a013] text-white p-3 rounded-xl transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>

                    <div v-if="search || letter" class="mt-4 text-center">
                        <p class="text-sm text-[#002b44]/70">
                            <span v-if="search">
                                {{ t('messages.Menampilkan hasil pencarian untuk') }}: <span class="font-semibold">"{{ search }}"</span>
                            </span>
                            <span v-if="letter">
                                {{ search ? ' • ' : '' }}{{ t('messages.huruf') }}: <span class="font-semibold">{{ letter }}</span>
                            </span>
                            <button @click="clearFilters"
                                class="ml-2 text-[#54b0af] hover:text-[#459a99] font-medium">
                                {{ t('messages.Hapus pencarian') }}
                            </button>
                        </p>
                    </div>
                </div>

                <!-- A-Z Filter -->
                <div class="max-w-8xl mx-auto mb-8">
                    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-sm font-semibold text-[#002b44] mb-4 text-center">{{ t('messages.Filter berdasarkan huruf') }}:</h3>
                        <div class="flex flex-wrap justify-center gap-2">
                            <button
                                v-for="letter in alphabet"
                                :key="letter"
                                @click="filterByLetter(letter)"
                                class="min-w-[40px] h-10 px-3 rounded-lg font-semibold text-sm transition-all duration-200 transform hover:scale-105"
                                :class="selectedLetter === letter
                                    ? 'bg-[#54b0af] text-white shadow-lg'
                                    : counts[letter] 
                                        ? 'bg-white text-[#002b44] border-2 border-gray-200 hover:border-[#54b0af] hover:text-[#54b0af]'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed opacity-50'"
                                :disabled="!counts[letter]"
                            >
                                {{ letter }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <!-- HEADER -->
                            <thead>
                                <tr class="bg-[#54b0af] text-white">
                                    <th class="px-12 py-5 text-left text-base font-bold w-1/4">{{ t('messages.bahasa melayu belitung') }}</th>
                                    <th class="px-12 py-5 text-left text-base font-bold w-1/4">{{ t('messages.bahasa indonesia') }}</th>
                                    <th class="px-12 py-5 text-left text-base font-bold w-1/4">Contoh Kalimat</th>
                                    <th class="px-12 py-5 text-center text-base font-bold w-20">{{ t('messages.audio') }}</th>
                                </tr>
                            </thead>
                            <!-- BODY -->
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(item, index) in kamus.data" :key="item.id"
                                    :class="index % 2 === 0 ? 'bg-[#002b44]/5' : 'bg-white'"
                                    class="hover:bg-[#54b0af]/10 transition-colors duration-200">
                                    <td class="px-12 py-6">
                                        <span class="text-[#002b44] font-semibold text-base">{{ item.kata }}</span>
                                    </td>
                                    <td class="px-12 py-6">
                                        <span class="text-gray-700 text-base">{{ item.definisi }}</span>
                                    </td>
                                    <td class="px-12 py-6">
                                        <div v-if="item.contoh && item.contoh.length > 0">
                                            <p class="text-gray-700 text-sm leading-relaxed">{{ item.contoh[0].contoh_kalimat }}</p>
                                            <p class="text-gray-500 text-xs italic mt-1">→ {{ item.contoh[0].arti_contoh_kalimat }}</p>
                                            <span v-if="item.contoh.length > 1" class="text-xs text-[#54b0af] mt-1 block">+{{ item.contoh.length - 1 }} contoh lainnya</span>
                                        </div>
                                        <span v-else class="text-gray-400 text-sm">-</span>
                                    </td>
                                    <td class="px-12 py-6 text-center">
                                        <button
                                            @click="item.audio ? playAudio(item.id, `/serve-media/${item.audio}`) : null"
                                            :disabled="!item.audio"
                                            :title="item.audio ? 'Putar audio' : 'Audio tidak tersedia'"
                                            class="inline-flex items-center justify-center w-12 h-12 rounded-full transition-all duration-200"
                                            :class="item.audio
                                                ? isPlaying === item.id
                                                    ? 'bg-[#e0a013] animate-pulse text-white transform scale-110'
                                                    : 'bg-[#FCB415] hover:bg-[#e0a013] text-white transform hover:scale-110 cursor-pointer'
                                                : 'bg-gray-200 text-gray-400 cursor-not-allowed opacity-60'">
                                            <svg v-if="isPlaying === item.id" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                            </svg>
                                            <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="!kamus.data || kamus.data.length === 0" class="text-center py-20">
                            <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <p class="text-xl font-medium text-gray-600">
                                {{ search || letter ? t('messages.no_results_found') : t('messages.no_data_available') }}
                            </p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="kamus.data && kamus.data.length > 0"
                        class="px-3 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-4">
                            <div class="text-xs sm:text-sm text-gray-600 order-2 sm:order-1 whitespace-nowrap">
                                {{ t('messages.menampilkan') }} {{ kamus.from }} - {{ kamus.to }} {{ t('messages.dari') }} {{ kamus.total }} {{ t('messages.kata') }}
                            </div>

                            <div class="flex items-center gap-0.5 sm:gap-1 overflow-x-auto pb-1 sm:pb-0 order-1 sm:order-2 w-full sm:w-auto justify-center sm:justify-end">
                                <!-- Previous -->
                                <component :is="kamus.prev_page_url ? 'a' : 'span'" :href="kamus.prev_page_url"
                                    class="p-1 sm:p-2 rounded-lg transition-all flex-shrink-0" :class="kamus.prev_page_url
                                        ? 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </component>

                                <!-- Page Numbers -->
                                <template v-for="(link, i) in kamus.links" :key="i">
                                    <component v-if="link.url && !isNaN(link.label)" :is="'a'" :href="link.url"
                                        class="px-1.5 sm:px-3 py-1 sm:py-1.5 rounded text-xs sm:text-sm font-medium transition-all flex-shrink-0"
                                        :class="link.active
                                            ? 'bg-[#54b0af] text-white shadow-md'
                                            : 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'">
                                        {{ link.label }}
                                    </component>
                                    <span v-else-if="link.label === '...'" class="px-0.5 sm:px-2 py-1 sm:py-1.5 text-xs sm:text-sm text-gray-500 flex-shrink-0">
                                        ...
                                    </span>
                                </template>

                                <!-- Next -->
                                <component :is="kamus.next_page_url ? 'a' : 'span'" :href="kamus.next_page_url"
                                    class="p-1 sm:p-2 rounded-lg transition-all flex-shrink-0" :class="kamus.next_page_url
                                        ? 'bg-white text-[#002b44] hover:bg-[#54b0af] hover:text-white border border-gray-300'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </component>
                            </div>
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
                            <h4 class="text-lg font-semibold text-[#002b44] mb-2">{{ t('messages.Tentang Kamus Digital') }}</h4>
                            <p class="text-gray-600 leading-relaxed">
                                {{ t('messages.Tentang Kamus Digital Deskripsi') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #54b0af;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #459a99;
}
</style>