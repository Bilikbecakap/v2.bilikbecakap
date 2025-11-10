<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useTranslations } from '@/composables/useTranslations';
import { ref } from 'vue';

const props = defineProps({
    kontaks: Object,
});

const { can } = usePermissions();
const { t } = useTranslations();

const selectedIds = ref([]);
const selectAll = ref(false);

// Toggle semua checkbox
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = props.kontaks.data.map(k => k.id);
    } else {
        selectedIds.value = [];
    }
};

// Toggle single checkbox
const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(id);
    }
    selectAll.value = selectedIds.value.length === props.kontaks.data.length;
};

// Hapus satu pesan
const deleteKontak = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        router.delete(`/admin/kontak-masuk/${id}`);
    }
};

// Hapus multiple pesan
const bulkDelete = () => {
    if (selectedIds.value.length === 0) {
        alert('Silakan pilih pesan yang ingin dihapus');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin menghapus ${selectedIds.value.length} pesan?`)) {
        router.post('/admin/kontak-masuk/bulk-delete', {
            ids: selectedIds.value,
        });
    }
};

// Format tanggal
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Pesan Kontak Masuk" />

    <AdminLayout>
        <template #title>Pesan Kontak Masuk</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
                        Pesan Kontak Masuk
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        Kelola pesan dari pengunjung website
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Pesan</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ kontaks.total }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Di Halaman Ini</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ kontaks.data.length }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Dipilih</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ selectedIds.length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedIds.length > 0" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900 rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 8a1 1 0 000 2h6a1 1 0 000-2H8z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-300">
                    {{ selectedIds.length }} pesan dipilih
                </span>
            </div>
            <button @click="bulkDelete" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Terpilih
            </button>
        </div>

        <!-- Messages Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Daftar Pesan</h3>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Nama
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Subjek
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Telepon
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="kontak in kontaks.data" :key="kontak.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input 
                                    type="checkbox" 
                                    :checked="selectedIds.includes(kontak.id)"
                                    @change="toggleSelect(kontak.id)"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900 dark:text-white">{{ kontak.nama }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a :href="`mailto:${kontak.email}`" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ kontak.email }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-900 dark:text-slate-200 max-w-xs truncate">
                                    {{ kontak.subjek }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a v-if="kontak.nomor_telepon" :href="`tel:${kontak.nomor_telepon}`" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ kontak.nomor_telepon }}
                                </a>
                                <span v-else class="text-sm text-slate-400 dark:text-slate-500">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ formatDate(kontak.created_at) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <Link :href="`/admin/kontak-masuk/${kontak.id}`" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors duration-150" title="Lihat Detail">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat
                                    </Link>

                                    <button @click="deleteKontak(kontak.id)" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150" title="Hapus">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="kontaks.data.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 text-slate-400 dark:text-slate-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">Tidak ada pesan</h3>
                    <p class="text-slate-500 dark:text-slate-400">Belum ada pesan kontak yang masuk.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="kontaks.last_page > 1" class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Menampilkan {{ kontaks.from }} - {{ kontaks.to }} dari {{ kontaks.total }} pesan
                </div>
                <div class="flex space-x-2">
                    <Link v-if="kontaks.prev_page_url" :href="kontaks.prev_page_url" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600">
                        ← Sebelumnya
                    </Link>
                    
                    <Link v-if="kontaks.next_page_url" :href="kontaks.next_page_url" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600">
                        Selanjutnya →
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>