<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { computed, ref, watch } from 'vue';

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

// Bulk action states
const selectedItems = ref([]);
const selectAll = ref(false);
const showBulkActions = ref(false);

// Modal states untuk konfirmasi
const showDeleteModal = ref(false);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const selectedKamus = ref(null);
const modalAction = ref(null); // 'single', 'bulk'
const bulkActionData = ref(null);

// Computed untuk row numbers
const getRowNumber = (index) => {
    const currentPage = props.kamus.current_page || 1;
    const perPage = props.kamus.per_page || 15;
    return (currentPage - 1) * perPage + index + 1;
};

// Computed untuk bulk actions
const allItemsSelected = computed(() => {
    return props.kamus.data && props.kamus.data.length > 0 && 
           selectedItems.value.length === props.kamus.data.length;
});

const someItemsSelected = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < props.kamus.data.length;
});

const canDeleteSelected = computed(() => {
    return selectedItems.value.every(id => {
        const item = props.kamus.data.find(k => k.id === id);
        return item && item.can_delete;
    });
});

const selectedItemsInfo = computed(() => {
    const items = selectedItems.value.map(id => 
        props.kamus.data.find(k => k.id === id)
    ).filter(Boolean);
    
    return {
        total: items.length,
        canDelete: items.filter(item => item.can_delete).length,
        cannotDelete: items.filter(item => !item.can_delete).length,
        activeItems: items.filter(item => item.status === 1).length,
        pendingItems: items.filter(item => item.status === 3).length,
        inactiveItems: items.filter(item => item.status === 2).length,
    };
});

// Watch for selectAll changes
watch(selectAll, (newVal) => {
    if (newVal) {
        selectedItems.value = props.kamus.data
            .filter(item => item.can_delete)
            .map(item => item.id);
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

// Modal functions untuk delete
const openDeleteModal = (kamus, isBulk = false) => {
    selectedKamus.value = kamus;
    modalAction.value = 'single';
    if (isBulk) {
        modalAction.value = 'bulk';
        bulkActionData.value = {
            count: selectedItems.value.length,
            items: selectedItems.value.map(id => 
                props.kamus.data.find(k => k.id === id)
            ).filter(Boolean)
        };
    }
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (modalAction.value === 'bulk') {
        bulkDelete();
    } else {
        performDelete();
    }
    showDeleteModal.value = false;
};

const performDelete = () => {
    if (selectedKamus.value) {
        router.delete(`/admin/kamus/${selectedKamus.value.id}`);
        selectedKamus.value = null;
    }
};

// Modal functions untuk approve
const openApproveModal = (isBulk = false) => {
    modalAction.value = 'single';
    if (isBulk) {
        modalAction.value = 'bulk';
        bulkActionData.value = {
            count: selectedItems.value.filter(id => {
                const item = props.kamus.data.find(k => k.id === id);
                return item && item.status === 3;
            }).length
        };
    }
    showApproveModal.value = true;
};

const confirmApprove = () => {
    if (modalAction.value === 'bulk') {
        performBulkApprove();
    } else {
        performApprove();
    }
    showApproveModal.value = false;
};

const performApprove = () => {
    if (selectedKamus.value) {
        router.patch(`/admin/kamus/${selectedKamus.value.id}/approve`);
        selectedKamus.value = null;
    }
};

// Modal functions untuk reject
const openRejectModal = (isBulk = false) => {
    modalAction.value = 'single';
    if (isBulk) {
        modalAction.value = 'bulk';
        bulkActionData.value = {
            count: selectedItems.value.filter(id => {
                const item = props.kamus.data.find(k => k.id === id);
                return item && item.status === 3;
            }).length
        };
    }
    showRejectModal.value = true;
};

const confirmReject = () => {
    if (modalAction.value === 'bulk') {
        performBulkReject();
    } else {
        performReject();
    }
    showRejectModal.value = false;
};

const performReject = () => {
    if (selectedKamus.value) {
        router.patch(`/admin/kamus/${selectedKamus.value.id}/reject`);
        selectedKamus.value = null;
    }
};

// Bulk actions
const bulkDelete = () => {
    if (selectedItems.value.length === 0) return;
    router.post('/admin/kamus/bulk-delete', {
        ids: selectedItems.value
    }, {
        onSuccess: () => {
            clearSelection();
        }
    });
};

const performBulkApprove = () => {
    const pendingItems = selectedItems.value.filter(id => {
        const item = props.kamus.data.find(k => k.id === id);
        return item && item.status === 3;
    });
    
    if (pendingItems.length === 0) return;
    
    router.post('/admin/kamus/bulk-approve', {
        ids: pendingItems
    }, {
        onSuccess: () => {
            clearSelection();
        }
    });
};

const performBulkReject = () => {
    const pendingItems = selectedItems.value.filter(id => {
        const item = props.kamus.data.find(k => k.id === id);
        return item && item.status === 3;
    });
    
    if (pendingItems.length === 0) return;
    
    router.post('/admin/kamus/bulk-reject', {
        ids: pendingItems
    }, {
        onSuccess: () => {
            clearSelection();
        }
    });
};

// Old inline functions (kept for reference but now using modals)
const deleteKamus = (id, bahasa_melayu) => {
    const item = props.kamus.data.find(k => k.id === id);
    if (item) {
        selectedKamus.value = item;
        showDeleteModal.value = true;
    }
};

const approveKamus = (id) => {
    const item = props.kamus.data.find(k => k.id === id);
    if (item) {
        selectedKamus.value = item;
        showApproveModal.value = true;
    }
};

const rejectKamus = (id) => {
    const item = props.kamus.data.find(k => k.id === id);
    if (item) {
        selectedKamus.value = item;
        showRejectModal.value = true;
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
        let audioUrl;
        if (audioPath.startsWith('http')) {
            audioUrl = audioPath;
        } else if (audioPath.startsWith('/serve-media/')) {
            audioUrl = audioPath;
        } else if (audioPath.startsWith('/storage/')) {
            // Convert /storage/ to /serve-media/kamus-audio/
            audioUrl = audioPath.replace('/storage/', '/serve-media/kamus-audio/');
        } else if (audioPath.startsWith('storage/')) {
            audioUrl = `/serve-media/kamus-audio/${audioPath.replace('storage/', '')}`;
        } else {
            audioUrl = `/serve-media/kamus-audio/${audioPath}`;
        }
        
        const audio = new Audio(audioUrl);
        audio.play().catch(error => {
            console.error('Error playing audio:', error);
            alert('Gagal memutar audio. File mungkin tidak ditemukan atau format tidak didukung.');
        });
    }
};

const truncateText = (text, maxLength = 50) => {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const filterByStatus = (statusValue) => {
    const params = {};
    
    if (statusValue && statusValue.trim() !== '') {
        params.status = statusValue;
    }
    
    if (searchQuery.value) {
        params.search = searchQuery.value;
    }
    
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
    
    if (searchQuery.value && searchQuery.value.trim() !== '') {
        params.search = searchQuery.value.trim();
    }
    
    if (props.status && props.status.trim() !== '') {
        params.status = props.status;
    }
    
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
    clearSelection();
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

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: '1', label: 'Aktif' },
    { value: '3', label: 'Menunggu' },
    { value: '2', label: 'Tidak Aktif' }
];

// Helpers untuk modal
const getModalTitle = () => {
    if (showDeleteModal.value) return 'Hapus Kamus';
    if (showApproveModal.value) return 'Setujui Kamus';
    if (showRejectModal.value) return 'Tolak Kamus';
    return '';
};

const getModalMessage = () => {
    if (showDeleteModal.value) {
        if (modalAction.value === 'bulk') {
            const items = bulkActionData.value.items.slice(0, 3);
            const itemNames = items.map(item => `"${item.bahasa_melayu}"`).join(', ');
            const moreItems = bulkActionData.value.count > 3 ? ` dan ${bulkActionData.value.count - 3} lainnya` : '';
            return `Apakah Anda yakin ingin menghapus ${bulkActionData.value.count} kamus (${itemNames}${moreItems})? Tindakan ini tidak dapat dibatalkan.`;
        }
        return `Apakah Anda yakin ingin menghapus kamus "${selectedKamus.value?.bahasa_melayu}"? Tindakan ini tidak dapat dibatalkan.`;
    }
    
    if (showApproveModal.value) {
        if (modalAction.value === 'bulk') {
            return `Apakah Anda yakin ingin menyetujui ${bulkActionData.value.count} kamus yang dipilih?`;
        }
        return `Apakah Anda yakin ingin menyetujui kamus "${selectedKamus.value?.bahasa_melayu}"?`;
    }
    
    if (showRejectModal.value) {
        if (modalAction.value === 'bulk') {
            return `Apakah Anda yakin ingin menolak ${bulkActionData.value.count} kamus yang dipilih?`;
        }
        return `Apakah Anda yakin ingin menolak kamus "${selectedKamus.value?.bahasa_melayu}"?`;
    }
    
    return '';
};

const getModalIcon = () => {
    if (showDeleteModal.value) return 'delete';
    if (showApproveModal.value) return 'approve';
    if (showRejectModal.value) return 'reject';
    return '';
};

const getModalButtonColor = () => {
    if (showDeleteModal.value) return 'red';
    if (showApproveModal.value) return 'green';
    if (showRejectModal.value) return 'orange';
    return 'blue';
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
                        v-if="can('create kamus')"
                        href="/admin/kamus/create" 
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
                        
                        <div v-if="selectedItemsInfo.cannotDelete > 0" class="text-xs text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 px-2 py-1 rounded-lg">
                            {{ selectedItemsInfo.cannotDelete }} item tidak dapat dihapus
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <!-- Bulk Actions Buttons -->
                        <div class="flex gap-2">
                            <!-- Bulk Delete -->
                            <button 
                                v-if="can('delete kamus') && selectedItemsInfo.canDelete > 0"
                                @click="openDeleteModal(null, true)"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                                title="Hapus Terpilih"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus ({{ selectedItemsInfo.canDelete }})
                            </button>

                            <!-- Bulk Approve -->
                            <button 
                                v-if="can('validasi kamus') && selectedItemsInfo.pendingItems > 0"
                                @click="openApproveModal(true)"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-150"
                                title="Setujui Terpilih"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Setujui ({{ selectedItemsInfo.pendingItems }})
                            </button>

                            <!-- Bulk Reject -->
                            <button 
                                v-if="can('validasi kamus') && selectedItemsInfo.pendingItems > 0"
                                @click="openRejectModal(true)"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30 rounded-lg hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-colors duration-150"
                                title="Tolak Terpilih"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tolak ({{ selectedItemsInfo.pendingItems }})
                            </button>
                        </div>

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

                <!-- Selection Summary -->
                <div v-if="selectedItemsInfo.total > 0" class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-800">
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span v-if="selectedItemsInfo.activeItems > 0" class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded">
                            {{ selectedItemsInfo.activeItems }} Aktif
                        </span>
                        <span v-if="selectedItemsInfo.pendingItems > 0" class="inline-flex items-center px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded">
                            {{ selectedItemsInfo.pendingItems }} Menunggu
                        </span>
                        <span v-if="selectedItemsInfo.inactiveItems > 0" class="inline-flex items-center px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded">
                            {{ selectedItemsInfo.inactiveItems }} Tidak Aktif
                        </span>
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
                            <!-- Bulk Select Checkbox -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :value="item.id"
                                        :checked="selectedItems.includes(item.id)"
                                        @change="toggleSelectItem(item.id)"
                                        :disabled="!item.can_delete"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                </div>
                            </td>
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

                                    <!-- Edit Button -->
                                    <Link 
                                        v-if="can('edit kamus') && (item.can_edit)"
                                        :href="`/admin/kamus/${item.id}/edit`"
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

                                    <!-- Approve Button -->
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

                                    <!-- Reject Button -->
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
                            href="/admin/kamus/create"
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

        <!-- Delete Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="showDeleteModal = false"
                ></div>
                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                >
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10"
                        >
                            <svg
                                class="h-6 w-6 text-red-600 dark:text-red-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                                />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3
                                class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100"
                                id="modal-title"
                            >
                                Hapus Kamus
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ getModalMessage() }}
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

        <!-- Approve Modal -->
        <div
            v-if="showApproveModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="showApproveModal = false"
                ></div>
                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                >
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 sm:mx-0 sm:h-10 sm:w-10"
                        >
                            <svg
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3
                                class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100"
                                id="modal-title"
                            >
                                Setujui Kamus
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ getModalMessage() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            @click="confirmApprove"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                        >
                            Setujui
                        </button>
                        <button
                            @click="showApproveModal = false"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-150"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="showRejectModal = false"
                ></div>
                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                >
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-900/20 sm:mx-0 sm:h-10 sm:w-10"
                        >
                            <svg
                                class="h-6 w-6 text-orange-600 dark:text-orange-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3
                                class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100"
                                id="modal-title"
                            >
                                Tolak Kamus
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ getModalMessage() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            @click="confirmReject"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-150"
                        >
                            Tolak
                        </button>
                        <button
                            @click="showRejectModal = false"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-150"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>