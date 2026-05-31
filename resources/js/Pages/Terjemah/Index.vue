<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    data:                    Object,
    search:                  String,
    status:                  String,
    currentUserId:           Number,
    hasValidasiPermission:   Boolean,
    hasFinalisasiPermission: Boolean,
});

const searchQuery  = ref(props.search || '');
const deleteTarget = ref(null);

const statusConfig = {
    1: { label: 'Menunggu',            cls: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300', dot: 'bg-yellow-400' },
    2: { label: 'Menunggu Finalisasi', cls: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',   dot: 'bg-blue-400' },
    3: { label: 'Tervalidasi',         cls: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300', dot: 'bg-green-400' },
    4: { label: 'Ditolak',             cls: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',    dot: 'bg-red-400' },
};

const statusOptions = [
    { value: '',  label: 'Semua Status' },
    { value: '1', label: 'Menunggu' },
    { value: '2', label: 'Menunggu Finalisasi' },
    { value: '3', label: 'Tervalidasi' },
    { value: '4', label: 'Ditolak' },
];

const doSearch = () => {
    const p = {};
    if (searchQuery.value) p.search = searchQuery.value;
    if (props.status) p.status = props.status;
    router.get(route('terjemah.index'), p, { preserveState: true, replace: true });
};

const filterStatus = (val) => {
    const p = {};
    if (val) p.status = val;
    if (searchQuery.value) p.search = searchQuery.value;
    router.get(route('terjemah.index'), p, { preserveState: true, replace: true });
};

const clearFilter = () => {
    searchQuery.value = '';
    router.get(route('terjemah.index'), {}, { preserveState: true, replace: true });
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('terjemah.destroy', deleteTarget.value.id), {
        onFinish: () => { deleteTarget.value = null; }
    });
};

const truncate = (text, n = 70) => text && text.length > n ? text.slice(0, n) + '…' : (text || '-');
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
const rowNo = (i) => (props.data.current_page - 1) * props.data.per_page + i + 1;

const canEdit   = (item) => item.created_by === props.currentUserId && [1, 4].includes(item.status);
const canDelete = (item) => props.hasFinalisasiPermission || (item.created_by === props.currentUserId && [1, 4].includes(item.status));
const canTinjau = (item) =>
    (props.hasValidasiPermission   && item.status === 1) ||
    (props.hasFinalisasiPermission && item.status === 2);
const showDetail = (item) => item.created_by === props.currentUserId || item.status === 3
    || props.hasValidasiPermission || props.hasFinalisasiPermission;
</script>

<template>
    <Head title="Testing Penerjemah" />
    <AdminLayout>
        <template #title>Testing Penerjemah</template>

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Testing Penerjemah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Uji dan validasi terjemahan Bahasa Melayu Belitung</p>
            </div>
            <Link :href="route('terjemah.create')"
                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Pengujian
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="mb-5 flex flex-col sm:flex-row gap-3">
            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input v-model="searchQuery" @keyup.enter="doSearch" type="text"
                    placeholder="Cari teks atau terjemahan..."
                    class="w-full pl-9 pr-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
            </div>
            <!-- Status Filter -->
            <select :value="status || ''" @change="filterStatus($event.target.value)"
                class="px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors min-w-[170px]">
                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
            <!-- Search Button -->
            <button @click="doSearch"
                class="px-4 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors whitespace-nowrap">
                Cari
            </button>
            <!-- Reset -->
            <button v-if="search || status" @click="clearFilter"
                class="px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Reset
            </button>
        </div>

        <!-- Active filter tags -->
        <div v-if="search || status" class="mb-4 flex flex-wrap gap-2">
            <span v-if="search" class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs rounded-full">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                "{{ search }}"
            </span>
            <span v-if="status" :class="['inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full', statusConfig[parseInt(status)]?.cls]">
                {{ statusConfig[parseInt(status)]?.label }}
            </span>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Table Header Bar -->
            <div class="px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Daftar Pengujian
                </h3>
                <span class="text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-700 px-2.5 py-1 rounded-full">
                    {{ data.total || 0 }} entri
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide w-10">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Teks Indonesia</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Terjemahan Melayu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide w-40">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide w-36">Pengirim & Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in data.data" :key="item.id"
                            class="border-b border-slate-100 dark:border-slate-700/60 hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors duration-100">
                            <!-- No -->
                            <td class="px-4 py-3.5 text-xs text-slate-400 font-mono align-top pt-4">
                                {{ rowNo(i) }}
                            </td>

                            <!-- Teks Indonesia -->
                            <td class="px-4 py-3.5 align-top max-w-xs">
                                <p class="text-sm text-slate-800 dark:text-slate-200 leading-snug line-clamp-2">
                                    {{ item.teks_indonesia }}
                                </p>
                            </td>

                            <!-- Terjemahan -->
                            <td class="px-4 py-3.5 align-top max-w-xs">
                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-snug line-clamp-2">
                                    {{ item.terjemahan_pengguna }}
                                </p>
                                <span v-if="item.validasi?.terjemahan_koreksi"
                                    class="inline-flex items-center mt-1.5 gap-1 px-2 py-0.5 rounded-full text-xs bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400 flex-shrink-0"></span>
                                    Ada koreksi
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 align-top">
                                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium', statusConfig[item.status]?.cls]">
                                    <span :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', statusConfig[item.status]?.dot]"></span>
                                    {{ statusConfig[item.status]?.label }}
                                </span>
                            </td>

                            <!-- Pengirim & Tanggal -->
                            <td class="px-4 py-3.5 align-top">
                                <p class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ item.creator?.name || '-' }}
                                    <span v-if="item.created_by === currentUserId" class="text-purple-500">(Saya)</span>
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ formatDate(item.created_at) }}</p>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 align-top">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Validasi / Finalisasi -->
                                    <Link v-if="canTinjau(item)" :href="route('terjemah.tinjauan', item.id)"
                                        :class="[
                                            'inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                            item.status === 1
                                                ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                                : 'bg-blue-600 hover:bg-blue-700 text-white'
                                        ]" :title="item.status === 1 ? 'Validasi' : 'Finalisasi'">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ item.status === 1 ? 'Validasi' : 'Finalisasi' }}
                                    </Link>

                                    <!-- Lihat detail -->
                                    <Link v-else-if="showDetail(item)" :href="route('terjemah.tinjauan', item.id)"
                                        class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </Link>

                                    <!-- Edit -->
                                    <Link v-if="canEdit(item)" :href="route('terjemah.edit', item.id)"
                                        class="p-1.5 text-amber-500 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </Link>

                                    <!-- Hapus -->
                                    <button v-if="canDelete(item)" @click="deleteTarget = item"
                                        class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty -->
                <div v-if="!data.data?.length" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-base font-medium text-slate-600 dark:text-slate-400">Belum ada data pengujian</p>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">
                        {{ search || status ? 'Coba ubah filter pencarian.' : 'Klik "Tambah Pengujian" untuk memulai.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="data.data?.length && data.total > data.per_page"
            class="mt-4 flex items-center justify-between">
            <span class="text-sm text-slate-500 dark:text-slate-400">
                Menampilkan {{ data.from }}–{{ data.to }} dari {{ data.total }}
            </span>
            <div class="flex gap-1">
                <template v-for="(link, i) in data.links" :key="i">
                    <Link v-if="link.url && !isNaN(link.label)" :href="link.url"
                        :class="[
                            'px-3 py-1.5 text-sm rounded-lg transition-colors border',
                            link.active
                                ? 'bg-purple-600 text-white border-purple-600'
                                : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700'
                        ]">{{ link.label }}</Link>
                    <Link v-else-if="link.url && link.label.includes('Previous')" :href="link.url"
                        class="px-3 py-1.5 text-sm rounded-lg bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        ←
                    </Link>
                    <Link v-else-if="link.url && link.label.includes('Next')" :href="link.url"
                        class="px-3 py-1.5 text-sm rounded-lg bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        →
                    </Link>
                </template>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Hapus Pengujian</h3>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                        Data ini akan dihapus permanen beserta riwayat validasinya. Tindakan ini tidak bisa dibatalkan.
                    </p>
                    <div class="flex justify-end gap-2">
                        <button @click="deleteTarget = null"
                            class="px-4 py-2 text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button @click="confirmDelete"
                            class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
