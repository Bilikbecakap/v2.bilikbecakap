<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';

const props = defineProps({
    kamus: Object, // Changed from Array to Object for pagination
    letters: Array,
    selectedLetter: String,
    sort: String,
    direction: String
});

const { can } = usePermissions();

// Computed untuk row numbers
const getRowNumber = (index) => {
    const currentPage = props.kamus.current_page || 1;
    const perPage = props.kamus.per_page || 15;
    return (currentPage - 1) * perPage + index + 1;
};

const deleteKamus = (id, bahasa_melayu) => {
    if (confirm(`Apakah Anda yakin ingin menghapus kamus "${bahasa_melayu}"?`)) {
        router.delete(`/kamus/${id}`);
    }
};

const approveKamus = (id) => {
    if (confirm('Apakah Anda yakin ingin menyetujui kamus ini?')) {
        router.patch(`/kamus/${id}/approve`);
    }
};

const rejectKamus = (id) => {
    if (confirm('Apakah Anda yakin ingin menolak kamus ini?')) {
        router.patch(`/kamus/${id}/reject`);
    }
};

const getStatusBadge = (status) => {
    const statusConfig = {
        1: { text: 'Aktif', class: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' },
        2: { text: 'Tidak Aktif', class: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' },
        3: { text: 'Menunggu', class: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' }
    };
    return statusConfig[status] || { text: 'Unknown', class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300' };
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};

const playAudio = (audioPath) => {
    if (audioPath) {
        // Handle different audio path formats
        let audioUrl;
        if (audioPath.startsWith('http')) {
            // Full URL
            audioUrl = audioPath;
        } else if (audioPath.startsWith('/storage/')) {
            // Already has /storage/ prefix
            audioUrl = audioPath;
        } else if (audioPath.startsWith('storage/')) {
            // Missing leading slash
            audioUrl = `/${audioPath}`;
        } else {
            // Just filename, add full storage path
            audioUrl = `/storage/${audioPath}`;
        }
        
        const audio = new Audio(audioUrl);
        audio.play().catch(error => {
            console.error('Error playing audio:', error);
            console.log('Attempted to play:', audioUrl);
            alert('Gagal memutar audio. File mungkin tidak ditemukan atau format tidak didukung.');
        });
    }
};

const truncateText = (text, maxLength = 50) => {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const filterByLetter = (letter) => {
    router.get(route('kamus.index'), { letter }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const sortByDate = (direction) => {
    const params = {
        sort: 'created_at',
        direction: direction
    };
    
    // Keep the current letter filter if active
    if (props.selectedLetter) {
        params.letter = props.selectedLetter;
    }
    
    router.get(route('kamus.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};
</script>

<template>
    <Head title="Kamus" />

    <AdminLayout>
        <template #title>Kamus Management</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">Semua Kamus</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Kelola data kamus Melayu - Indonesia</p>
                </div>
                <div class="flex gap-3">
                    <Link 
                        v-if="can('validasi kamus')"
                        href="/kamus-validate" 
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-orange-600 to-red-600 text-white font-medium text-sm rounded-xl hover:from-orange-700 hover:to-red-700 transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Validasi Kamus
                    </Link>
                    <Link 
                        v-if="can('create kamus')"
                        href="/kamus/create" 
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Kamus
                    </Link>
                </div>
            </div>
        </div>

        <!-- A-Z Filter Section -->
        <div class="mb-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Filter Huruf</h3>
            </div>
            
            <div class="p-6">
                <div class="flex flex-wrap gap-2">
                    <!-- All Button -->
                    <button
                        @click="filterByLetter('')"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                            selectedLetter === '' || selectedLetter === null
                                ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-sm'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]"
                    >
                        Semua
                    </button>
                    
                    <!-- Letter Buttons A-Z -->
                    <button
                        v-for="letter in letters"
                        :key="letter"
                        @click="filterByLetter(letter)"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                            selectedLetter === letter
                                ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-sm'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]"
                    >
                        {{ letter }}
                    </button>
                </div>
                
                <!-- Active Filter Info -->
                <div v-if="selectedLetter || (sort === 'created_at')" class="mt-4 flex flex-col space-y-2">
                    <!-- Letter Filter Info -->
                    <div v-if="selectedLetter" class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z" />
                        </svg>
                        Menampilkan kata yang dimulai dengan huruf: 
                        <span class="font-semibold text-blue-600 dark:text-blue-400 ml-1">{{ selectedLetter }}</span>
                        <button 
                            @click="filterByLetter('')"
                            class="ml-3 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline font-medium"
                        >
                            Reset filter
                        </button>
                    </div>
                    
                    <!-- Sort Info -->
                    <div v-if="sort === 'created_at'" class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        Diurutkan berdasarkan tanggal: 
                        <span class="font-semibold text-green-600 dark:text-green-400 ml-1">
                            {{ direction === 'asc' ? 'Terlama ke Terbaru' : 'Terbaru ke Terlama' }}
                        </span>
                        <button 
                            @click="router.get(route('kamus.index'), selectedLetter ? { letter: selectedLetter } : {})"
                            class="ml-3 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 underline font-medium"
                        >
                            Reset urutan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kamus Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Daftar Kamus</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Bahasa Melayu</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-72">Bahasa Indonesia</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-24">Audio</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-24">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-32">Dibuat Oleh</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-32">
                                <button 
                                    @click="sortByDate(sort === 'created_at' && direction === 'asc' ? 'desc' : 'asc')"
                                    class="flex items-center space-x-1 hover:text-slate-800 dark:hover:text-slate-200 transition-colors duration-150 group"
                                >
                                    <span>Tanggal</span>
                                    <div class="flex flex-col">
                                        <svg 
                                            :class="[
                                                'w-3 h-3 transition-colors duration-150',
                                                sort === 'created_at' && direction === 'asc' 
                                                    ? 'text-blue-600 dark:text-blue-400' 
                                                    : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'
                                            ]" 
                                            fill="currentColor" 
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                        <svg 
                                            :class="[
                                                'w-3 h-3 -mt-1 transition-colors duration-150',
                                                sort === 'created_at' && direction === 'desc' 
                                                    ? 'text-blue-600 dark:text-blue-400' 
                                                    : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'
                                            ]" 
                                            fill="currentColor" 
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-48">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="(item, index) in kamus.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-slate-500 dark:text-slate-400">{{ getRowNumber(index) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-teal-500 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-sm font-medium text-slate-900 dark:text-white">{{ item.bahasa_melayu }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-72">
                                    <span class="text-sm text-slate-900 dark:text-white block truncate" :title="item.bahasa_indonesia">
                                        {{ truncateText(item.bahasa_indonesia, 60) }}
                                    </span>
                                        <p v-if="item.keterangan" class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ truncateText(item.keterangan, 80) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="item.audio" class="flex items-center">
                                    <button 
                                        @click="playAudio(item.audio)"
                                        class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center hover:from-green-600 hover:to-emerald-600 transition-colors duration-200 shadow-sm hover:shadow-md"
                                        title="Putar Audio"
                                    >
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                                <span v-else class="text-xs text-slate-400 dark:text-slate-500">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                    getStatusBadge(item.status).class
                                ]">
                                    {{ getStatusBadge(item.status).text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                {{ item.creator?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">

                                    <!-- Edit Button -->
                                    <Link 
                                        v-if="can('edit kamus')"
                                        :href="`/kamus/${item.id}/edit`"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 rounded-lg hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors duration-150"
                                        title="Edit Kamus"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>

                                    <!-- Approve Button (hanya untuk status menunggu dan user dengan permission validasi) -->
                                    <button 
                                        v-if="can('validasi kamus') && item.status === 3"
                                        @click="approveKamus(item.id)"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-150"
                                        title="Setujui Kamus"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>

                                    <!-- Reject Button (hanya untuk status menunggu dan user dengan permission validasi) -->
                                    <button 
                                        v-if="can('validasi kamus') && item.status === 3"
                                        @click="rejectKamus(item.id)"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30 rounded-lg hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-colors duration-150"
                                        title="Tolak Kamus"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button 
                                        v-if="can('delete kamus')"
                                        @click="deleteKamus(item.id, item.bahasa_melayu)" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                                        title="Hapus Kamus"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="!kamus.data || kamus.data.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">Belum ada kamus</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Mulai dengan menambahkan kamus pertama Anda.</p>
                    <Link 
                        v-if="can('create kamus')"
                        href="/kamus/create"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Kamus
                    </Link>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="kamus.data && kamus.data.length > 0" class="mt-8 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Menampilkan {{ kamus.from }} sampai {{ kamus.to }} dari {{ kamus.total }} data
                </div>
                
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    <Link 
                        v-if="kamus.prev_page_url"
                        :href="kamus.prev_page_url"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </Link>
                    
                    <!-- Page Numbers -->
                    <div class="hidden sm:flex items-center space-x-1">
                        <template v-for="(link, index) in kamus.links" :key="index">
                            <Link 
                                v-if="link.url && !isNaN(link.label)"
                                :href="link.url"
                                :class="[
                                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                                    link.active 
                                        ? 'bg-blue-600 text-white border border-blue-600' 
                                        : 'text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700'
                                ]"
                            >
                                {{ link.label }}
                            </Link>
                            <span 
                                v-else-if="link.label === '...'"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400"
                            >
                                ...
                            </span>
                        </template>
                    </div>

                    <!-- Next Button -->
                    <Link 
                        v-if="kamus.next_page_url"
                        :href="kamus.next_page_url"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                    >
                        Next
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>