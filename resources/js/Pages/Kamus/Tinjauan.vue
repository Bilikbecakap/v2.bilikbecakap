<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    kamus: Object,
    validasiDisetujui: Array,
    validasiDitolak: Array,
    validasiUser: Object,
    sudahValidasi: Boolean,
    bisaValidasi: Boolean,
    bisaFinalisasi: Boolean,
    hasValidationPermission: Boolean,
    hasFinalizePermission: Boolean,
    totalDisetujui: Number,
    targetValidasi: Number,
});

const showValidasiForm = ref(false);
const showFinalisasiForm = ref(false);

const formValidasi = useForm({
    action: '',
    catatan: '',
});

const formFinalisasi = useForm({
    action: '',
    catatan: '',
});

const submitValidasi = (action) => {
    formValidasi.action = action;
    formValidasi.post(route('kamus.validasi', props.kamus.id), {
        onSuccess: () => {
            showValidasiForm.value = false;
        }
    });
};

const submitFinalisasi = (action) => {
    formFinalisasi.action = action;
    formFinalisasi.post(route('kamus.finalisasi', props.kamus.id), {
        onSuccess: () => {
            showFinalisasiForm.value = false;
        }
    });
};

const playAudio = (audioPath) => {
    if (!audioPath) return;
    const audioUrl = audioPath.startsWith('http') ? audioPath : `/serve-media/${audioPath}`;
    const audio = new Audio(audioUrl);
    audio.play().catch(() => alert('Gagal memutar audio.'));
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const statusConfig = {
    1: { text: 'Aktif', class: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' },
    2: { text: 'Tidak Aktif', class: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' },
    3: { text: 'Menunggu Validasi', class: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' },
    4: { text: 'Menunggu Finalisasi', class: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' },
};

const getStatusBadge = (status) => statusConfig[status] || { text: 'Unknown', class: 'bg-gray-100 text-gray-800' };

const validasiProgress = computed(() => {
    return Math.min((props.totalDisetujui / props.targetValidasi) * 100, 100);
});
</script>

<template>
    <Head title="Tinjauan Kamus" />

    <AdminLayout>
        <template #title>Tinjauan Kamus</template>

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">Tinjauan Kamus</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        Review dan validasi data kamus sebelum dipublikasikan
                    </p>
                </div>
                <Link
                    :href="route('kamus.index')"
                    class="inline-flex items-center px-4 py-2.5 bg-slate-600 text-white font-medium text-sm rounded-xl hover:bg-slate-700 transition-all duration-200 shadow-sm"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kolom Kiri: Detail Kamus -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Detail Kamus Card -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Detail Kamus</h3>
                        <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', getStatusBadge(kamus.status).class]">
                            {{ getStatusBadge(kamus.status).text }}
                        </span>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- Bahasa Melayu -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Bahasa Melayu</label>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ kamus.bahasa_melayu }}</p>
                        </div>

                        <!-- Bahasa Indonesia -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Bahasa Indonesia</label>
                            <p class="text-base text-slate-800 dark:text-slate-200">{{ kamus.bahasa_indonesia }}</p>
                        </div>

                        <!-- Keterangan -->
                        <div v-if="kamus.keterangan">
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Keterangan</label>
                            <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 rounded-lg px-4 py-3">{{ kamus.keterangan }}</p>
                        </div>

                        <!-- Audio -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Audio Pengucapan</label>
                            <div v-if="kamus.audio">
                                <button
                                    @click="playAudio(kamus.audio)"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-sm font-medium rounded-lg hover:from-green-600 hover:to-emerald-600 transition-colors duration-200 shadow-sm"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Putar Audio
                                </button>
                            </div>
                            <span v-else class="text-sm text-slate-400 dark:text-slate-500 italic">Tidak ada audio</span>
                        </div>

                        <!-- Meta -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Dibuat Oleh</label>
                                <p class="text-sm text-slate-700 dark:text-slate-300">{{ kamus.creator?.name || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Dibuat</label>
                                <p class="text-sm text-slate-700 dark:text-slate-300">{{ formatDate(kamus.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Validasi -->
                <div
                    v-if="bisaValidasi"
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
                >
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-yellow-50 dark:bg-yellow-900/10">
                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Aksi Validasi
                        </h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                            Berikan keputusan validasi Anda untuk kamus ini
                        </p>
                    </div>

                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Catatan <span class="text-slate-500 font-normal">(opsional)</span>
                            </label>
                            <textarea
                                v-model="formValidasi.catatan"
                                rows="3"
                                placeholder="Tambahkan catatan untuk keputusan validasi Anda..."
                                class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
                            ></textarea>
                            <p v-if="formValidasi.errors.catatan" class="mt-1 text-sm text-red-600">{{ formValidasi.errors.catatan }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button
                                @click="submitValidasi('setuju')"
                                :disabled="formValidasi.processing"
                                class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-medium text-sm rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-200 dark:focus:ring-green-800 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Setujui
                            </button>
                            <button
                                @click="submitValidasi('tolak')"
                                :disabled="formValidasi.processing"
                                class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white font-medium text-sm rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 dark:focus:ring-red-800 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tolak
                            </button>
                        </div>

                        <p v-if="formValidasi.errors.error" class="mt-3 text-sm text-red-600 dark:text-red-400">
                            {{ formValidasi.errors.error }}
                        </p>
                    </div>
                </div>

                <!-- Info sudah validasi -->
                <div
                    v-else-if="hasValidationPermission && sudahValidasi && kamus.status === 3"
                    class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-5"
                >
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Anda sudah melakukan validasi</p>
                            <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                                Keputusan Anda:
                                <span :class="validasiUser?.action === 'setuju' ? 'text-green-700 dark:text-green-400 font-semibold' : 'text-red-700 dark:text-red-400 font-semibold'">
                                    {{ validasiUser?.action === 'setuju' ? 'Disetujui' : 'Ditolak' }}
                                </span>
                                <span v-if="validasiUser?.catatan" class="block mt-1 italic">"{{ validasiUser.catatan }}"</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Finalisasi -->
                <div
                    v-if="bisaFinalisasi"
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
                >
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-blue-50 dark:bg-blue-900/10">
                        <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-300 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Finalisasi & Publikasi
                        </h3>
                        <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                            Kamus ini telah melewati 2 validasi. Lakukan finalisasi untuk mempublikasikannya.
                        </p>
                    </div>

                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Catatan <span class="text-slate-500 font-normal">(opsional)</span>
                            </label>
                            <textarea
                                v-model="formFinalisasi.catatan"
                                rows="3"
                                placeholder="Tambahkan catatan finalisasi..."
                                class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
                            ></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button
                                @click="submitFinalisasi('publish')"
                                :disabled="formFinalisasi.processing"
                                class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-lg hover:from-purple-700 hover:to-pink-700 focus:ring-4 focus:ring-purple-200 dark:focus:ring-purple-800 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                Publish Kamus
                            </button>
                            <button
                                @click="submitFinalisasi('tolak')"
                                :disabled="formFinalisasi.processing"
                                class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white font-medium text-sm rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 dark:focus:ring-red-800 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tolak & Kembalikan
                            </button>
                        </div>

                        <p v-if="formFinalisasi.errors.error" class="mt-3 text-sm text-red-600 dark:text-red-400">
                            {{ formFinalisasi.errors.error }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Status Validasi -->
            <div class="space-y-6">

                <!-- Progress Validasi -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <h3 class="text-base font-semibold text-slate-800 dark:text-white">Progres Validasi</h3>
                    </div>

                    <div class="p-6">
                        <!-- Status icon -->
                        <div class="flex items-center justify-center mb-5">
                            <div :class="[
                                'w-16 h-16 rounded-full flex items-center justify-center',
                                kamus.status === 1 ? 'bg-green-100 dark:bg-green-900/30' :
                                kamus.status === 2 ? 'bg-red-100 dark:bg-red-900/30' :
                                kamus.status === 4 ? 'bg-blue-100 dark:bg-blue-900/30' :
                                'bg-yellow-100 dark:bg-yellow-900/30'
                            ]">
                                <!-- Aktif -->
                                <svg v-if="kamus.status === 1" class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <!-- Tidak Aktif / Ditolak -->
                                <svg v-else-if="kamus.status === 2" class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <!-- Menunggu Finalisasi -->
                                <svg v-else-if="kamus.status === 4" class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                <!-- Menunggu -->
                                <svg v-else class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Alur validasi step-by-step -->
                        <div class="space-y-3">
                            <!-- Step 1: Menunggu Validasi -->
                            <div class="flex items-center">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mr-3 text-xs font-bold',
                                    totalDisetujui >= 1 || kamus.status >= 4 ? 'bg-green-500 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300'
                                ]">
                                    <svg v-if="totalDisetujui >= 1 || kamus.status >= 4" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span v-else>1</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">Validasi Pertama</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ totalDisetujui >= 1 ? 'Selesai' : 'Menunggu' }}
                                    </p>
                                </div>
                            </div>

                            <div class="ml-4 h-4 border-l-2 border-dashed border-slate-300 dark:border-slate-600"></div>

                            <!-- Step 2: Validasi Kedua -->
                            <div class="flex items-center">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mr-3 text-xs font-bold',
                                    totalDisetujui >= 2 || kamus.status >= 4 ? 'bg-green-500 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300'
                                ]">
                                    <svg v-if="totalDisetujui >= 2 || kamus.status >= 4" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span v-else>2</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">Validasi Kedua</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ totalDisetujui >= 2 ? 'Selesai' : 'Menunggu' }}
                                    </p>
                                </div>
                            </div>

                            <div class="ml-4 h-4 border-l-2 border-dashed border-slate-300 dark:border-slate-600"></div>

                            <!-- Step 3: Finalisasi -->
                            <div class="flex items-center">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mr-3 text-xs font-bold',
                                    kamus.status === 1 ? 'bg-green-500 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300'
                                ]">
                                    <svg v-if="kamus.status === 1" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span v-else>3</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">Finalisasi & Publish</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ kamus.status === 1 ? 'Selesai' : kamus.status === 4 ? 'Menunggu' : 'Belum' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div v-if="kamus.status === 3" class="mt-5">
                            <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400 mb-1">
                                <span>Validasi</span>
                                <span>{{ totalDisetujui }}/{{ targetValidasi }}</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                <div
                                    class="bg-yellow-500 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: validasiProgress + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Validasi -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <h3 class="text-base font-semibold text-slate-800 dark:text-white">Riwayat Validasi</h3>
                    </div>

                    <div class="p-6">
                        <!-- Persetujuan -->
                        <div v-if="validasiDisetujui.length > 0" class="mb-4">
                            <p class="text-xs font-semibold text-green-700 dark:text-green-400 uppercase tracking-wider mb-3">Disetujui ({{ validasiDisetujui.length }})</p>
                            <div class="space-y-3">
                                <div
                                    v-for="v in validasiDisetujui"
                                    :key="v.id"
                                    class="flex items-start p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-900/40"
                                >
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 mr-3">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ v.user?.name || '-' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(v.created_at) }}</p>
                                        <p v-if="v.catatan" class="text-xs text-green-700 dark:text-green-400 mt-1 italic">"{{ v.catatan }}"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Penolakan -->
                        <div v-if="validasiDitolak.length > 0" class="mb-4">
                            <p class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase tracking-wider mb-3">Ditolak ({{ validasiDitolak.length }})</p>
                            <div class="space-y-3">
                                <div
                                    v-for="v in validasiDitolak"
                                    :key="v.id"
                                    class="flex items-start p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-900/40"
                                >
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 mr-3">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ v.user?.name || '-' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(v.created_at) }}</p>
                                        <p v-if="v.catatan" class="text-xs text-red-700 dark:text-red-400 mt-1 italic">"{{ v.catatan }}"</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kosong -->
                        <div v-if="validasiDisetujui.length === 0 && validasiDitolak.length === 0" class="text-center py-6">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada riwayat validasi</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>
