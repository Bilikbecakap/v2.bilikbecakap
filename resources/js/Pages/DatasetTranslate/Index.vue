<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    datasets: Object, // Pagination object
    sort: String,
    direction: String,
    search: String,
});

const { can } = usePermissions();

// Local search state
const searchQuery = ref(props.search || '');

// Bulk action states
const selectedItems = ref([]);
const selectAll = ref(false);
const showBulkActions = ref(false);

// Delete modal state
const showDeleteModal = ref(false);
const selectedDataset = ref(null);

// Computed untuk row numbers
const getRowNumber = (index) => {
    const currentPage = props.datasets.current_page || 1;
    const perPage = props.datasets.per_page || 15;
    return (currentPage - 1) * perPage + index + 1;
};

// Computed untuk bulk actions
const allItemsSelected = computed(() => {
    return props.datasets.data && props.datasets.data.length > 0 && 
           selectedItems.value.length === props.datasets.data.length;
});

const someItemsSelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < props.datasets.data.length;
});

const selectedItemsInfo = computed(() => {
    return {
        total: selectedItems.value.length,
    };
});

// Watch for selectAll changes
watch(selectAll, (newVal) => {
    if (newVal) {
        selectedItems.value = props.datasets.data.map(item => item.id);
    } else {
        selectedItems.value = [];
    }
});

// Watch for selectedItems changes
watch(selectedItems, (newVal) => {
    selectAll.value = newVal.length > 0 && allItemsSelected.value;
    showBulkActions.value = newVal.length > 0;
}, { deep: true });

// Bulk action functions
const toggleSelectAll = () => {
    selectAll.value = !selectAll.value;
};

const toggleSelectItem = (itemId) => {
    const index = selectedItems.value.indexOf(itemId);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(itemId);
    }
};

const clearSelection = () => {
    selectedItems.value = [];
    selectAll.value = false;
    showBulkActions.value = false;
};

const bulkDelete = () => {
    if (selectedItems.value.length === 0) return;
    
    const itemsToDelete = selectedItems.value.map(id => 
        props.datasets.data.find(d => d.id === id)
    ).filter(Boolean);
    
    const itemNames = itemsToDelete
        .slice(0, 3)
        .map(item => `"${item.bahasa_belitung}"`)
        .join(', ');
    
    const moreItems = itemsToDelete.length > 3 ? ` dan ${itemsToDelete.length - 3} lainnya` : '';
    
    if (confirm(`Apakah Anda yakin ingin menghapus ${selectedItems.value.length} dataset (${itemNames}${moreItems})?`)) {
        router.post(route('dataset-translate.bulk-delete'), {
            ids: selectedItems.value
        }, {
            onSuccess: () => {
                clearSelection();
            }
        });
    }
};

// Individual action functions
const deleteDataset = (dataset) => {
    selectedDataset.value = dataset;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (selectedDataset.value) {
        router.delete(route('dataset-translate.destroy', selectedDataset.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedDataset.value = null;
            }
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const truncateText = (text, maxLength = 50) => {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const performSearch = () => {
    const params = {};
    
    // Hanya tambahkan parameter search jika tidak kosong
    if (searchQuery.value && searchQuery.value.trim() !== '') {
        params.search = searchQuery.value.trim();
    }
    
    // Keep current sort if active
    if (props.sort) {
        params.sort = props.sort;
        params.direction = props.direction;
    }
    
    router.get(route('dataset-translate.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    clearSelection();
    router.get(route('dataset-translate.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const sortBy = (field) => {
    const newDirection = props.sort === field && props.direction === 'asc' ? 'desc' : 'asc';

    const params = {
        sort: field,
        direction: newDirection
    };

    // Keep the current filters if active
    if (searchQuery.value && searchQuery.value.trim() !== '') {
        params.search = searchQuery.value.trim();
    }

    router.get(route('dataset-translate.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Export
const showExportMenu = ref(false);

const exportData = (format) => {
    showExportMenu.value = false;
    const params = new URLSearchParams({ format });
    if (searchQuery.value && searchQuery.value.trim()) {
        params.set('search', searchQuery.value.trim());
    }
    window.location.href = route('dataset-translate.export') + '?' + params.toString();
};
</script>

<template>
    <Head title="Dataset Translate" />

    <AdminLayout>
        <template #title>Dataset Translate Management</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">Dataset Translate</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Kelola data training untuk sistem translate Belitung - Indonesia</p>
                </div>
                <div class="flex gap-3">
                    <!-- Export Dropdown -->
                    <div class="relative">
                        <button
                            @click="showExportMenu = !showExportMenu"
                            class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-medium text-sm rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 shadow-sm"
                        >
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Backdrop to close dropdown -->
                        <div
                            v-if="showExportMenu"
                            class="fixed inset-0 z-10"
                            @click="showExportMenu = false"
                        ></div>

                        <!-- Dropdown Menu -->
                        <Transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div
                                v-if="showExportMenu"
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 z-20 overflow-hidden"
                            >
                                <button
                                    @click="exportData('csv')"
                                    class="w-full flex items-center px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                                >
                                    <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Export CSV
                                </button>
                                <button
                                    @click="exportData('xlsx')"
                                    class="w-full flex items-center px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                                >
                                    <svg class="w-4 h-4 mr-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Export XLSX
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <Link
                        v-if="can('create dataset')"
                        :href="route('dataset-translate.create')"
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Dataset
                    </Link>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform opacity-0 -translate-y-2"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-2"
        >
            <div v-if="showBulkActions" class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                {{ selectedItemsInfo.total }} item dipilih
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <!-- Bulk Delete -->
                        <button 
                            v-if="can('delete dataset')"
                            @click="bulkDelete"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                            title="Hapus Terpilih"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus ({{ selectedItemsInfo.total }})
                        </button>

                        <!-- Clear Selection -->
                        <button 
                            @click="clearSelection"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                            title="Batal Pilih"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Filter Section -->
        <div class="mb-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Filter & Pencarian</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Search Input -->
                    <div>
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
                                placeholder="Cari berdasarkan bahasa Belitung atau Indonesia..."
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
                </div>
                
                <!-- Active Filters Info -->
                <div v-if="search || sort" class="mt-4 flex flex-wrap gap-2">
                    <!-- Search Filter Info -->
                    <div v-if="search" class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-lg text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Pencarian: "{{ search }}"
                    </div>

                    <!-- Sort Info -->
                    <div v-if="sort" class="inline-flex items-center px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 rounded-lg text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        Urutan: {{ sort === 'bahasa_belitung' ? 'Bahasa Belitung' : sort === 'bahasa_indonesia' ? 'Bahasa Indonesia' : 'Tanggal' }} 
                        ({{ direction === 'asc' ? 'A-Z' : 'Z-A' }})
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

        <!-- Dataset Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Daftar Dataset</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Total: {{ datasets.total || 0 }} data
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <!-- Bulk Select Header -->
                            <th class="px-6 py-4 text-left w-12">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="allItemsSelected"
                                        :indeterminate="someItemsSelected"
                                        @change="toggleSelectAll"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    >
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                <button 
                                    @click="sortBy('bahasa_belitung')"
                                    class="flex items-center space-x-1 hover:text-slate-800 dark:hover:text-slate-200 transition-colors duration-150 group"
                                >
                                    <span>Bahasa Belitung</span>
                                    <div class="flex flex-col">
                                        <svg 
                                            :class="[
                                                'w-3 h-3 transition-colors duration-150',
                                                sort === 'bahasa_belitung' && direction === 'asc' 
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
                                                sort === 'bahasa_belitung' && direction === 'desc' 
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
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                <button 
                                    @click="sortBy('bahasa_indonesia')"
                                    class="flex items-center space-x-1 hover:text-slate-800 dark:hover:text-slate-200 transition-colors duration-150 group"
                                >
                                    <span>Bahasa Indonesia</span>
                                    <div class="flex flex-col">
                                        <svg 
                                            :class="[
                                                'w-3 h-3 transition-colors duration-150',
                                                sort === 'bahasa_indonesia' && direction === 'asc' 
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
                                                sort === 'bahasa_indonesia' && direction === 'desc' 
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
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="(item, index) in datasets.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                            <!-- Bulk Select Checkbox -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :value="item.id"
                                        :checked="selectedItems.includes(item.id)"
                                        @change="toggleSelectItem(item.id)"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    >
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-slate-500 dark:text-slate-400">{{ getRowNumber(index) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="min-w-0">
                                        <span class="text-sm font-medium text-slate-900 dark:text-white">{{ item.bahasa_belitung }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-900 dark:text-white block" :title="item.bahasa_indonesia">
                                    {{ truncateText(item.bahasa_indonesia, 80) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <Link 
                                        v-if="can('edit dataset')"
                                        :href="route('dataset-translate.edit', item.id)"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 rounded-lg hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors duration-150"
                                        title="Edit Dataset"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>

                                    <!-- Delete Button -->
                                    <button 
                                        v-if="can('delete dataset')"
                                        @click="deleteDataset(item)" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                                        title="Hapus Dataset"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>

                                    <!-- Jika tidak ada action yang bisa dilakukan -->
                                    <span 
                                        v-if="!can('edit dataset') && !can('delete dataset')"
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
                <div v-if="!datasets.data || datasets.data.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">
                        {{ search ? 'Tidak ada dataset yang sesuai dengan pencarian' : 'Belum ada dataset' }}
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                        {{ search ? 'Coba ubah kata kunci pencarian untuk melihat hasil yang berbeda.' : 'Mulai dengan menambahkan dataset pertama Anda.' }}
                    </p>
                    <div class="flex justify-center gap-3">
                        <button 
                            v-if="search"
                            @click="clearFilters"
                            class="inline-flex items-center px-4 py-2 bg-slate-600 text-white font-medium text-sm rounded-lg hover:bg-slate-700 transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset Pencarian
                        </button>
                        <Link 
                            v-if="can('create dataset')"
                            :href="route('dataset-translate.create')"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Dataset
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="datasets.data && datasets.data.length > 0" class="mt-8 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Menampilkan {{ datasets.from }} sampai {{ datasets.to }} dari {{ datasets.total }} data
                </div>
                
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    <Link 
                        v-if="datasets.prev_page_url"
                        :href="datasets.prev_page_url"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </Link>
                    
                    <!-- Page Numbers -->
                    <div class="hidden sm:flex items-center space-x-1">
                        <template v-for="(link, index) in datasets.links" :key="index">
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
                        v-if="datasets.next_page_url"
                        :href="datasets.next_page_url"
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

    <!-- Delete Modal -->
    <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div
                class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"
                aria-hidden="true"
                @click="showDeleteModal = false"
            ></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100" id="modal-title">
                            Hapus Dataset
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Apakah Anda yakin ingin menghapus dataset
                                <strong>"{{ selectedDataset?.bahasa_belitung }}"</strong>?
                                Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button
                        @click="confirmDelete"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                    >
                        Hapus
                    </button>
                    <button
                        @click="showDeleteModal = false"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-150"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>