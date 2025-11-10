<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    kontak: Object,
});

// Format tanggal
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Hapus pesan
const deleteKontak = () => {
    if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        router.delete(`/admin/kontak-masuk/${props.kontak.id}`);
    }
};
</script>

<template>
    <Head :title="`Pesan dari ${kontak.nama}`" />

    <AdminLayout>
        <template #title>Detail Pesan Kontak</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8 flex items-center justify-between">
            <div>
                <Link href="/admin/kontak-masuk" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium mb-4 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Daftar
                </Link>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
                    {{ kontak.nama }}
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                    {{ formatDate(kontak.created_at) }}
                </p>
            </div>
            <button @click="deleteKontak" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded-xl transition-colors duration-150 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Pesan
            </button>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <!-- Left Column - Message Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Message Card -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8">
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
                        {{ kontak.subjek }}
                    </h3>
                    
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed text-base">
                            {{ kontak.pesan }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Contact Info -->
            <div class="space-y-6">
                <!-- Contact Information Card -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773c.151.301.394.534.69.743 1.197.73 2.220.723 3.231.236 1.938-1.027 3.261-4.649 3.374-6.976a1 1 0 01.986-.836h2.153a1 1 0 011 1v12a1 1 0 01-1 1h-2.153a1 1 0 01-.986-.836l-.74-4.435a1 1 0 01.54-1.06l1.548-.773c-.151-.301-.394-.534-.69-.743-1.197-.73-2.22-.723-3.231-.236-1.938 1.027-3.261 4.649-3.374 6.976a1 1 0 01-.986.836H3a1 1 0 01-1-1V3z" />
                        </svg>
                        Informasi Kontak
                    </h4>

                    <div class="space-y-4">
                        <!-- Nama -->
                        <div>
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Nama Lengkap
                            </label>
                            <p class="text-sm text-slate-900 dark:text-white mt-2 font-medium">
                                {{ kontak.nama }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Email
                            </label>
                            <a :href="`mailto:${kontak.email}`" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-2 block font-medium">
                                {{ kontak.email }}
                            </a>
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Nomor Telepon
                            </label>
                            <div v-if="kontak.nomor_telepon" class="mt-2">
                                <a :href="`tel:${kontak.nomor_telepon}`" class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                    {{ kontak.nomor_telepon }}
                                </a>
                            </div>
                            <p v-else class="text-sm text-slate-500 dark:text-slate-400 mt-2 italic">
                                -
                            </p>
                        </div>

                        <!-- Tanggal Diterima -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Tanggal Diterima
                            </label>
                            <p class="text-sm text-slate-900 dark:text-white mt-2 font-medium">
                                {{ formatDate(kontak.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl shadow-sm border border-blue-200 dark:border-blue-900/30 p-6">
                    <h4 class="text-sm font-bold text-blue-900 dark:text-blue-200 mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 8a1 1 0 000 2h6a1 1 0 000-2H8z" clip-rule="evenodd" />
                        </svg>
                        Aksi Cepat
                    </h4>

                    <div class="space-y-2">
                        <a :href="`mailto:${kontak.email}`" class="block px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-medium text-sm rounded-lg transition-colors duration-150 text-center">
                            Balas Email
                        </a>
                        <a v-if="kontak.nomor_telepon" :href="`tel:${kontak.nomor_telepon}`" class="block px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-medium text-sm rounded-lg transition-colors duration-150 text-center">
                            Hubungi Telepon
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>