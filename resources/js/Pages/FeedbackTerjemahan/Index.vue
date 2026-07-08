<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    feedbacks: Object,
    filters: Object,
});

const filters = ref({
    tipe: props.filters.tipe || "",
    arah: props.filters.arah || "",
});

const showDeleteModal = ref(false);
const selectedFeedback = ref(null);
const showDetailModal = ref(false);
const detailFeedback = ref(null);

const applyFilters = () => {
    router.get(route("admin.feedback-terjemahan.index"), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.value.tipe = "";
    filters.value.arah = "";
    applyFilters();
};

const openDetail = (feedback) => {
    detailFeedback.value = feedback;
    showDetailModal.value = true;
};

const confirmDelete = (feedback) => {
    selectedFeedback.value = feedback;
    showDeleteModal.value = true;
};

const doDelete = () => {
    if (selectedFeedback.value) {
        router.delete(route("admin.feedback-terjemahan.destroy", selectedFeedback.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedFeedback.value = null;
            },
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const arahLabel = (arah) => {
    const map = {
        indonesia_to_belitung: "Indonesia → Belitung",
        belitung_to_indonesia: "Belitung → Indonesia",
        belitung_to_english: "Belitung → English",
        english_to_belitung: "English → Belitung",
    };
    return map[arah] || arah;
};
</script>

<template>
    <Head title="Feedback Terjemahan" />
    <AdminLayout>
        <template #title>Feedback Terjemahan</template>

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
                        Feedback Terjemahan
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        Tinjau masukan pengguna tentang akurasi terjemahan
                    </p>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Total: <span class="font-semibold text-slate-700 dark:text-white">{{ feedbacks.total }}</span> feedback
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipe</label>
                        <select
                            v-model="filters.tipe"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="akurat">Akurat ✓</option>
                            <option value="kurang_tepat">Kurang Tepat ✗</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Arah Terjemahan</label>
                        <select
                            v-model="filters.arah"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white"
                        >
                            <option value="">Semua Arah</option>
                            <option value="indonesia_to_belitung">Indonesia → Belitung</option>
                            <option value="belitung_to_indonesia">Belitung → Indonesia</option>
                            <option value="belitung_to_english">Belitung → English</option>
                            <option value="english_to_belitung">English → Belitung</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="resetFilters"
                            class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">Tipe</th>
                            <th class="text-left px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">Arah</th>
                            <th class="text-left px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">Teks Input</th>
                            <th class="text-left px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">Terjemahan Asli</th>
                            <th class="text-left px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">Koreksi</th>
                            <th class="text-left px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">Tanggal</th>
                            <th class="text-left px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="fb in feedbacks.data"
                            :key="fb.id"
                            class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <span
                                    :class="fb.tipe === 'akurat'
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                        : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                                >
                                    <span>{{ fb.tipe === 'akurat' ? '✓ Akurat' : '✗ Kurang Tepat' }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ arahLabel(fb.arah_terjemahan) }}
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300 max-w-[160px]">
                                <p class="truncate">{{ fb.teks_input }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300 max-w-[160px]">
                                <p class="truncate">{{ fb.terjemahan_asli }}</p>
                            </td>
                            <td class="px-6 py-4 max-w-[160px]">
                                <p v-if="fb.terjemahan_benar" class="truncate text-blue-700 dark:text-blue-400">{{ fb.terjemahan_benar }}</p>
                                <span v-else class="text-slate-400 text-xs">—</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ formatDate(fb.created_at) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="openDetail(fb)"
                                        class="px-3 py-1.5 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition-colors"
                                    >
                                        Detail
                                    </button>
                                    <button
                                        @click="confirmDelete(fb)"
                                        class="px-3 py-1.5 text-xs bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg transition-colors"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="feedbacks.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                Belum ada feedback yang masuk.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-slate-200 dark:border-slate-700">
                <Pagination :data="feedbacks" />
            </div>
        </div>

        <!-- Detail Modal -->
        <Transition name="modal">
            <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showDetailModal = false">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Detail Feedback</h3>
                        <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="detailFeedback" class="p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <span
                                :class="detailFeedback.tipe === 'akurat'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'"
                                class="px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ detailFeedback.tipe === 'akurat' ? '✓ Akurat' : '✗ Kurang Tepat' }}
                            </span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ arahLabel(detailFeedback.arah_terjemahan) }}</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Teks Input</p>
                                <p class="text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-700 p-3 rounded-lg text-sm">{{ detailFeedback.teks_input }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Terjemahan Sistem</p>
                                <p class="text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-700 p-3 rounded-lg text-sm">{{ detailFeedback.terjemahan_asli }}</p>
                            </div>
                            <div v-if="detailFeedback.terjemahan_benar">
                                <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide mb-1">Koreksi dari Pengguna</p>
                                <p class="text-blue-800 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg text-sm">{{ detailFeedback.terjemahan_benar }}</p>
                            </div>
                            <div v-if="detailFeedback.keterangan">
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Keterangan</p>
                                <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 p-3 rounded-lg text-sm italic">{{ detailFeedback.keterangan }}</p>
                            </div>
                            <div class="text-xs text-slate-400 flex items-center gap-4 pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span>IP: {{ detailFeedback.ip_address || '—' }}</span>
                                <span>{{ formatDate(detailFeedback.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Delete Confirmation Modal -->
        <Transition name="modal">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showDeleteModal = false">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Hapus Feedback</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Yakin ingin menghapus feedback ini? Tindakan ini tidak bisa dibatalkan.</p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="px-4 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button @click="doDelete" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
</style>
