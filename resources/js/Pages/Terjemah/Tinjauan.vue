<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    terjemah:                Object,
    bisaValidasi:            Boolean,
    bisaFinalisasi:          Boolean,
    sudahDivalidasi:         Boolean,
    hasValidasiPermission:   Boolean,
    hasFinalisasiPermission: Boolean,
    isOwner:                 Boolean,
});

const formValidasi = useForm({
    terjemahan_koreksi: '',
    catatan: '',
});

const formFinalisasi = useForm({
    action:  '',
    catatan: '',
});

const submitValidasi = () => {
    formValidasi.post(route('terjemah.validasi', props.terjemah.id));
};

const submitFinalisasi = (action) => {
    formFinalisasi.action = action;
    formFinalisasi.post(route('terjemah.finalisasi', props.terjemah.id));
};

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
});

const statusConfig = {
    1: { label: 'Menunggu Validasi',   cls: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' },
    2: { label: 'Menunggu Finalisasi', cls: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' },
    3: { label: 'Tervalidasi',         cls: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' },
    4: { label: 'Ditolak',             cls: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' },
};

const stepDone = (step) => {
    if (step === 1) return props.terjemah.status >= 2;
    if (step === 2) return props.terjemah.status === 3;
    return false;
};

// Jumlah kolom teks (2 atau 3 tergantung ada koreksi atau sedang validasi)
const showKoreksiCol = computed(() =>
    props.terjemah.validasi?.terjemahan_koreksi || props.bisaValidasi
);
</script>

<template>
    <Head title="Tinjauan Pengujian" />
    <AdminLayout>
        <template #title>Tinjauan Pengujian</template>

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Detail Pengujian</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Review dan validasi terjemahan Bahasa Melayu Belitung</p>
            </div>
            <Link :href="route('terjemah.index')"
                class="inline-flex items-center px-4 py-2.5 bg-slate-600 text-white text-sm font-medium rounded-xl hover:bg-slate-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </Link>
        </div>

        <!-- ===== PANEL PERBANDINGAN TEKS (kiri → tengah → kanan) ===== -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
            <!-- Panel Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Perbandingan Teks</h3>
                    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', statusConfig[terjemah.status]?.cls]">
                        {{ statusConfig[terjemah.status]?.label }}
                    </span>
                </div>
                <div class="flex gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <span>Oleh: <strong class="text-slate-700 dark:text-slate-300">{{ terjemah.creator?.name || '-' }}</strong></span>
                    <span>{{ formatDate(terjemah.created_at) }}</span>
                </div>
            </div>

            <!-- 3-Kolom Perbandingan -->
            <div :class="[
                'grid divide-x divide-slate-200 dark:divide-slate-700',
                showKoreksiCol ? 'grid-cols-1 md:grid-cols-3' : 'grid-cols-1 md:grid-cols-2'
            ]">
                <!-- Kolom 1: Teks Bahasa Indonesia (Original) -->
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-bold flex-shrink-0">A</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bahasa Indonesia</span>
                        <span class="text-xs text-slate-400">(asli)</span>
                    </div>
                    <div class="min-h-[120px] p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                        <p class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">{{ terjemah.teks_indonesia }}</p>
                    </div>
                </div>

                <!-- Kolom 2: Terjemahan Pengguna -->
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-200 dark:bg-blue-800 text-blue-700 dark:text-blue-300 text-xs font-bold flex-shrink-0">B</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Terjemahan Pengguna</span>
                        <span class="text-xs text-slate-400">(Melayu Belitung)</span>
                    </div>
                    <div class="min-h-[120px] p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">{{ terjemah.terjemahan_pengguna }}</p>
                    </div>
                </div>

                <!-- Kolom 3: Koreksi Validator (jika ada / jika sedang validasi) -->
                <div v-if="showKoreksiCol" class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-orange-200 dark:bg-orange-800 text-orange-700 dark:text-orange-300 text-xs font-bold flex-shrink-0">C</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Koreksi Validator</span>
                        <span v-if="!terjemah.validasi?.terjemahan_koreksi && bisaValidasi" class="text-xs text-slate-400">(opsional)</span>
                    </div>

                    <!-- Sudah ada koreksi -->
                    <div v-if="terjemah.validasi?.terjemahan_koreksi"
                        class="min-h-[120px] p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                        <p class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">{{ terjemah.validasi.terjemahan_koreksi }}</p>
                    </div>

                    <!-- Belum ada koreksi, bukan validator -->
                    <div v-else-if="!bisaValidasi"
                        class="min-h-[120px] p-4 bg-slate-50 dark:bg-slate-700/30 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center">
                        <p class="text-sm text-slate-400 dark:text-slate-500 italic text-center">Tidak ada koreksi — terjemahan sudah benar</p>
                    </div>

                    <!-- Textarea untuk validator mengisi koreksi -->
                    <div v-else>
                        <textarea v-model="formValidasi.terjemahan_koreksi" rows="5"
                            placeholder="Isi terjemahan yang benar jika ada kesalahan. Kosongkan jika sudah benar."
                            class="block w-full px-4 py-3 border border-orange-300 dark:border-orange-700 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-colors text-sm resize-y min-h-[120px]"></textarea>
                    </div>
                </div>
            </div>

            <!-- Catatan Validator (jika ada) -->
            <div v-if="terjemah.validasi?.catatan"
                class="px-6 pb-5 pt-0">
                <div class="bg-slate-50 dark:bg-slate-700/40 rounded-lg px-4 py-3 flex items-start gap-2">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <p class="text-sm text-slate-600 dark:text-slate-400 italic">
                        <span class="font-medium not-italic text-slate-700 dark:text-slate-300">Catatan:</span>
                        "{{ terjemah.validasi.catatan }}"
                    </p>
                </div>
            </div>
        </div>

        <!-- ===== AREA AKSI + PROGRES ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Form Validasi / Finalisasi / Info -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Form Validasi (Validator) -->
                <div v-if="bisaValidasi"
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-yellow-200 dark:border-yellow-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/10 flex items-center gap-3">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Aksi Validasi</h3>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-0.5">
                                Isi koreksi di kolom C (atas) jika ada yang perlu diperbaiki, lalu tambahkan catatan jika perlu.
                            </p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Catatan <span class="text-slate-400 font-normal text-xs">(opsional)</span>
                            </label>
                            <textarea v-model="formValidasi.catatan" rows="2"
                                placeholder="Tambahkan catatan untuk pengirim jika perlu..."
                                class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-colors text-sm resize-none"></textarea>
                        </div>
                        <p v-if="formValidasi.errors.error" class="text-sm text-red-600">{{ formValidasi.errors.error }}</p>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-400">
                                Koreksi kosong = terjemahan sudah benar
                            </p>
                            <button @click="submitValidasi" :disabled="formValidasi.processing"
                                class="px-6 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 shadow-sm">
                                {{ formValidasi.processing ? 'Menyimpan...' : 'Simpan Validasi' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info: sudah divalidasi, menunggu finalisasi -->
                <div v-else-if="hasValidasiPermission && sudahDivalidasi && terjemah.status === 2"
                    class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                    <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-700 dark:text-green-400">
                        Validasi sudah dilakukan oleh <strong>{{ terjemah.validasi?.validator?.name || '-' }}</strong>. Menunggu Finalisasi terakhir.
                    </p>
                </div>

                <!-- Form Finalisasi (Super-admin) -->
                <div v-if="bisaFinalisasi"
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-blue-200 dark:border-blue-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/10 flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Finalisasi & Publikasi</h3>
                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">Data sudah divalidasi. Publikasikan atau tolak kembali ke pengguna.</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Catatan <span class="text-slate-400 font-normal text-xs">(opsional, ditampilkan ke pengguna jika ditolak)</span>
                            </label>
                            <textarea v-model="formFinalisasi.catatan" rows="2"
                                placeholder="Catatan untuk pengirim jika ditolak..."
                                class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none text-sm"></textarea>
                        </div>
                        <p v-if="formFinalisasi.errors.error" class="text-sm text-red-600">{{ formFinalisasi.errors.error }}</p>
                        <div class="flex gap-3">
                            <button @click="submitFinalisasi('publish')" :disabled="formFinalisasi.processing"
                                class="flex-1 px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all disabled:opacity-50 shadow-sm">
                                Publikasikan
                            </button>
                            <button @click="submitFinalisasi('tolak')" :disabled="formFinalisasi.processing"
                                class="flex-1 px-6 py-2.5 bg-white dark:bg-slate-700 text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700 text-sm font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-50">
                                Tolak & Kembalikan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info untuk pemilik yang ditolak -->
                <div v-if="isOwner && terjemah.status === 4"
                    class="flex flex-col sm:flex-row sm:items-center gap-4 p-5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-red-700 dark:text-red-400">Data ini ditolak</p>
                        <p class="text-xs text-red-600 dark:text-red-500 mt-1">Perbaiki terjemahan Anda lalu kirim ulang untuk divalidasi kembali.</p>
                    </div>
                    <Link :href="route('terjemah.edit', terjemah.id)"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit & Kirim Ulang
                    </Link>
                </div>
            </div>

            <!-- Kolom Kanan: Progres + Info Validasi -->
            <div class="space-y-5">

                <!-- Progress Steps -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Progres Alur</h3>
                    </div>
                    <div class="p-5">
                        <!-- Status icon -->
                        <div class="flex justify-center mb-5">
                            <div :class="[
                                'w-14 h-14 rounded-full flex items-center justify-center shadow-inner',
                                terjemah.status === 3 ? 'bg-green-100 dark:bg-green-900/40' :
                                terjemah.status === 4 ? 'bg-red-100 dark:bg-red-900/40' :
                                terjemah.status === 2 ? 'bg-blue-100 dark:bg-blue-900/40' :
                                'bg-yellow-100 dark:bg-yellow-900/40'
                            ]">
                                <svg v-if="terjemah.status === 3" class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg v-else-if="terjemah.status === 4" class="w-7 h-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg v-else-if="terjemah.status === 2" class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <svg v-else class="w-7 h-7 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Steps -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div :class="['w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold',
                                    stepDone(1) ? 'bg-green-500 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400']">
                                    <svg v-if="stepDone(1)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span v-else>1</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">Validasi</p>
                                    <p class="text-xs text-slate-500">{{ stepDone(1) ? 'Selesai' : 'Menunggu' }}</p>
                                </div>
                            </div>
                            <div class="ml-4 h-5 border-l-2 border-dashed border-slate-300 dark:border-slate-600"></div>
                            <div class="flex items-center gap-3">
                                <div :class="['w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold',
                                    stepDone(2) ? 'bg-green-500 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400']">
                                    <svg v-if="stepDone(2)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span v-else>2</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">Finalisasi</p>
                                    <p class="text-xs text-slate-500">{{ stepDone(2) ? 'Selesai' : terjemah.status === 2 ? 'Menunggu' : 'Belum' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Validasi -->
                <div v-if="terjemah.validasi"
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Info Validasi</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between items-start">
                            <span class="text-xs text-slate-500">Validator</span>
                            <span class="text-xs font-medium text-slate-800 dark:text-slate-200 text-right">{{ terjemah.validasi.validator?.name || '-' }}</span>
                        </div>
                        <div class="flex justify-between items-start">
                            <span class="text-xs text-slate-500">Validasi pada</span>
                            <span class="text-xs text-slate-600 dark:text-slate-400 text-right leading-tight">{{ formatDate(terjemah.validasi.created_at) }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Ada koreksi?</span>
                                <span :class="[
                                    'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                    terjemah.validasi.terjemahan_koreksi
                                        ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'
                                        : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                ]">
                                    {{ terjemah.validasi.terjemahan_koreksi ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
