<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { computed, ref } from 'vue';

const props = defineProps({
    kamus: Object, // Changed from Array to Object for pagination
    sort: String,
    direction: String,
    search: String,
    status: String,
    hasValidationPermission: Boolean,
    currentUserId: Number
});

const { can } = usePermissions();

// Local search state
const searchQuery = ref(props.search || '');

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

// Perbaikan filter status - hilangkan parameter kosong dari URL
const filterByStatus = (statusValue) => {
    const params = {};
    
    // Hanya tambahkan parameter status jika tidak kosong
    if (statusValue && statusValue.trim() !== '') {
        params.status = statusValue;
    }
    
    // Keep the current search if active
    if (searchQuery.value) {
        params.search = searchQuery.value;
    }
    
    // Keep current sort if active
    if (props.sort) {
        params.sort = props.sort;
        params.direction = props.direction;
    }
    
    router.get(route('kamus.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const performSearch = () => {
    const params = {};
    
    // Hanya tambahkan parameter search jika tidak kosong
    if (searchQuery.value && searchQuery.value.trim() !== '') {
        params.search = searchQuery.value.trim();
    }
    
    // Keep current status filter if active
    if (props.status && props.status.trim() !== '') {
        params.status = props.status;
    }
    
    // Keep current sort if active
    if (props.sort) {
        params.sort = props.sort;
        params.direction = props.direction;
    }
    
    router.get(route('kamus.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    router.get(route('kamus.index'), {}, {
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
    
    // Keep the current filters if active
    if (searchQuery.value && searchQuery.value.trim() !== '') {
        params.search = searchQuery.value.trim();
    }
    if (props.status && props.status.trim() !== '') {
        params.status = props.status;
    }
    
    router.get(route('kamus.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Status options for filter
const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: '1', label: 'Aktif' },
    { value: '3', label: 'Menunggu' },
    { value: '2', label: 'Tidak Aktif' }
];
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

        <!-- Filter Section -->
        <div class="mb-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Filter & Pencarian</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Pencarian
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="searchQuery"
                                @keyup.enter="performSearch"
                                type="text"
                                placeholder="Cari berdasarkan bahasa Melayu atau Indonesia..."
                                class="block w-full pl-10 pr-10 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button
                                    @click="performSearch"
                                    class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors duration-200"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Status
                        </label>
                        <select
                            :value="status || ''"
                            @change="filterByStatus($event.target.value)"
                            class="block w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>
                
                <!-- Active Filters Info -->
                <div v-if="search || status || (sort === 'created_at')" class="mt-4 flex flex-wrap gap-2">
                    <!-- Search Filter Info -->
                    <div v-if="search" class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-lg text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Pencarian: "{{ search }}"
                    </div>
                    
                    <!-- Status Filter Info -->
                    <div v-if="status" class="inline-flex items-center px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z" />
                        </svg>
                        Status: {{ statusOptions.find(opt => opt.value === status)?.label }}
                    </div>

                    <!-- Sort Info -->
                    <div v-if="sort === 'created_at'" class="inline-flex items-center px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 rounded-lg text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        Urutan: {{ direction === 'asc' ? 'Terlama ke Terbaru' : 'Terbaru ke Terlama' }}
                    </div>

                    <!-- Clear All Button -->
                    <button 
                        @click="clearFilters"
                        class="inline-flex items-center px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-sm transition-colors duration-200"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset Semua
                    </button>
                </div>
            </div>
        </div>

        <!-- Kamus Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Daftar Kamus</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Total: {{ kamus.total || 0 }} data
                    </div>
                </div>
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
                            <th v-if="can('validasi kamus')" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-32">Dibuat Oleh</th>
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
                            <td v-if="can('validasi kamus')" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                <div class="flex items-center">
                                    <span>{{ item.creator?.name || '-' }}</span>
                                    <!-- Indicator untuk data milik user yang login -->
                                    <span 
                                        v-if="item.create_by === currentUserId"
                                        class="ml-2 inline-flex items-center px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs rounded-full"
                                        title="Data yang Anda buat"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">

                                    <!-- Edit Button - tampil jika user punya permission edit DAN (punya validasi permission ATAU owner) -->
                                    <Link 
                                        v-if="can('edit kamus') && (item.can_edit)"
                                        :href="`/kamus/${item.id}/edit`"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 rounded-lg hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors duration-150"
                                        title="Edit Kamus"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>

                                    <!-- Info tooltip untuk edit jika tidak bisa edit -->
                                    <div 
                                        v-else-if="can('edit kamus') && !item.can_edit"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 rounded-lg cursor-not-allowed"
                                        title="Hanya dapat mengedit kamus yang Anda buat sendiri"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>

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

                                    <!-- Delete Button - tampil jika user punya permission delete DAN (punya validasi permission ATAU owner) -->
                                    <button 
                                        v-if="can('delete kamus') && (item.can_delete)"
                                        @click="deleteKamus(item.id, item.bahasa_melayu)" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                                        title="Hapus Kamus"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>

                                    <!-- Info tooltip untuk delete jika tidak bisa delete -->
                                    <div 
                                        v-else-if="can('delete kamus') && !item.can_delete"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 rounded-lg cursor-not-allowed"
                                        title="Hanya dapat menghapus kamus yang Anda buat sendiri"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>

                                    <!-- Jika tidak ada action yang bisa dilakukan -->
                                    <span 
                                        v-if="!can('edit kamus') && !can('delete kamus') && !can('validasi kamus')"
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        -
                                    </span>
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
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">
                        {{ search || status ? 'Tidak ada kamus yang sesuai dengan filter' : 'Belum ada kamus' }}
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                        {{ search || status ? 'Coba ubah filter pencarian atau status untuk melihat hasil yang berbeda.' : 'Mulai dengan menambahkan kamus pertama Anda.' }}
                    </p>
                    <div class="flex justify-center gap-3">
                        <button 
                            v-if="search || status"
                            @click="clearFilters"
                            class="inline-flex items-center px-4 py-2 bg-slate-600 text-white font-medium text-sm rounded-lg hover:bg-slate-700 transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset Filter
                        </button>
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