<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { usePermissions } from "@/composables/usePermissions";
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    stats: Object,
    pendingItems: Object,
    recentActivities: Array,
    chartData: Object,
    statusDistribution: Object,
    recentData: Object,
    topPerformers: Object,
});

const { can, hasRole } = usePermissions();

const getCurrentTime = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadge = (status, type = 'artikel') => {
    if (type === 'kamus') {
        const badges = {
            1: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
            2: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
            3: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        };
        return badges[status] || badges[3];
    }

    const badges = {
        draft: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        pending: 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200',
        published: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        active: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        inactive: 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200',
    };
    return badges[status] || badges.draft;
};

const getStatusText = (status, type = 'artikel') => {
    if (type === 'kamus') {
        const texts = { 1: 'Aktif', 2: 'Ditolak', 3: 'Pending' };
        return texts[status] || 'Pending';
    }

    const texts = {
        draft: 'Draft',
        pending: 'Pending',
        published: 'Published',
        active: 'Active',
        inactive: 'Inactive',
    };
    return texts[status] || status;
};

const getTypeIcon = (type) => {
    const icons = {
        kamus: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        artikel: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        quiz: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    };
    return icons[type] || icons.artikel;
};
</script>

<template>

    <Head title="Dashboard" />

    <AdminLayout>
        <template #title>Dashboard</template>

        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-white">
                        {{ getCurrentTime() }}, {{ user.name }}
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        {{ new Date().toLocaleDateString('id-ID', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        }) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-6 md:mb-8">
            <div
                class="bg-gradient-to-br from-blue-50 via-cyan-50 to-teal-50 dark:from-slate-800 dark:via-slate-800 dark:to-slate-800 rounded-2xl shadow-lg border-2 border-blue-200 dark:border-blue-700 overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 md:p-8">

                    <!-- Content Section -->
                    <div class="lg:col-span-8 flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <img src="/icon.png" alt="Bilik Bercakap Icon" class="w-12 h-12 object-contain">
                            <h2 class="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white">
                                Bilik Bercakap
                            </h2>
                        </div>

                        <h3 class="text-xl md:text-2xl font-semibold text-blue-700 dark:text-blue-400 mb-4">
                            Platform Pembelajaran Bahasa Melayu Belitung
                        </h3>

                        <p class="text-base md:text-lg text-slate-700 dark:text-slate-300 leading-relaxed mb-6">
                            Bilik Bercakap merupakan platform belajar yang mewadahi komunikasi sebagai sarana belajar
                            khususnya bagi masyarakat lokal serta pelestarian bahasa daerah Melayu Belitung Timur.
                        </p>

                        <!-- Tagline -->
                        <div
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-cyan-600 dark:from-blue-700 dark:to-cyan-700 text-white px-6 py-3 rounded-full shadow-lg w-fit">
                            <span class="font-bold text-sm md:text-base tracking-wide">
                                #BersatuMemajukanKebudayaan
                            </span>
                        </div>
                    </div>

                    <!-- Vidio Section -->
                    <div class="lg:col-span-4 flex items-center justify-center">
                        <div class="relative">
                            <!-- Decorative circles -->
                            <div class="absolute -top-4 -left-4 w-24 h-24 bg-blue-200 dark:bg-blue-900/30 rounded-full blur-2xl opacity-60"></div>
                            <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-cyan-200 dark:bg-cyan-900/30 rounded-full blur-2xl opacity-60"></div>
                            
                            <!-- Logo -->
                            <img src="/images/kbkm2023.jpg" alt="Bilik Bercakap Logo" class="relative w-56 h-56 md:w-64 md:h-64 object-contain mx-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
            <!-- Kamus Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <Link v-if="can('view kamus')" :href="route('kamus.index')"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 transition-colors duration-150">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    </Link>
                    <span v-else class="text-slate-400 dark:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Total Kamus</h3>
                <p class="text-3xl font-bold text-slate-900 dark:text-white mb-3">
                    {{ stats.kamus.total.toLocaleString() }}
                </p>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center text-green-600 dark:text-green-400">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                        {{ stats.kamus.aktif }} Aktif
                    </span>
                    <span class="inline-flex items-center text-orange-600 dark:text-orange-400"
                        v-if="stats.kamus.pending > 0">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-1"></span>
                        {{ stats.kamus.pending }} Pending
                    </span>
                </div>
            </div>

            <!-- Quiz Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <Link v-if="can('view quiz')" :href="route('quiz.index')"
                        class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 transition-colors duration-150">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    </Link>
                    <span v-else class="text-slate-400 dark:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Quiz Tersedia</h3>
                <p class="text-3xl font-bold text-slate-900 dark:text-white mb-3">
                    {{ stats.quiz.total.toLocaleString() }}
                </p>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    {{ stats.quiz.active }} Active • {{ stats.quiz.inactive }} Inactive
                </div>
            </div>

            <!-- Artikel Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <Link v-if="can('view artikel')" :href="route('artikel.index')"
                        class="text-green-600 dark:text-green-400 hover:text-green-700 transition-colors duration-150">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    </Link>
                    <span v-else class="text-slate-400 dark:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Total Artikel</h3>
                <p class="text-3xl font-bold text-slate-900 dark:text-white mb-3">
                    {{ stats.artikel.total.toLocaleString() }}
                </p>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center text-green-600 dark:text-green-400">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                        {{ stats.artikel.published }} Published
                    </span>
                    <span class="inline-flex items-center text-orange-600 dark:text-orange-400"
                        v-if="stats.artikel.pending > 0">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-1"></span>
                        {{ stats.artikel.pending }} Pending
                    </span>
                </div>
            </div>

            <!-- Modul Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <Link v-if="can('view modul pembelajaran')" :href="route('modul-pembelajaran.index')"
                        class="text-orange-600 dark:text-orange-400 hover:text-orange-700 transition-colors duration-150">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    </Link>
                    <span v-else class="text-slate-400 dark:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Modul Pembelajaran</h3>
                <p class="text-3xl font-bold text-slate-900 dark:text-white mb-3">
                    {{ stats.modul.total.toLocaleString() }}
                </p>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center text-green-600 dark:text-green-400">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                        {{ stats.modul.published }} Published
                    </span>
                </div>
            </div>
        </div>



        <!-- Content Grid -->
        <div v-if="can('validasi kamus') || can('approve artikel')"
            class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 md:mb-8">
            <!-- Pending Approvals -->
            <div
                class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                        Pending Approvals
                    </h3>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200">
                        {{ pendingItems.kamus.length + pendingItems.artikel.length }} Items
                    </span>
                </div>
                <div class="p-6">
                    <div v-if="pendingItems.kamus.length === 0 && pendingItems.artikel.length === 0"
                        class="text-center py-8">
                        <svg class="w-12 h-12 text-slate-400 dark:text-slate-500 mx-auto mb-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Tidak ada item yang perlu disetujui
                        </p>
                    </div>
                    <div v-else class="space-y-3">
                        <!-- Kamus Pending -->
                        <template v-if="can('validasi kamus')">
                            <div v-for="item in pendingItems.kamus" :key="`kamus-${item.id}`"
                                class="flex items-start p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-orange-300 dark:hover:border-orange-600 transition-colors duration-150">
                                <div class="flex-shrink-0 mr-3">
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                :d="getTypeIcon('kamus')" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <Link :href="item.url"
                                        class="text-sm font-medium text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ item.title }}
                                    </Link>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        oleh {{ item.creator }} • {{ formatDate(item.created_at) }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 ml-2">
                                    Kamus
                                </span>
                            </div>
                        </template>

                        <!-- Artikel Pending -->
                        <template v-if="can('approve artikel')">
                            <div v-for="item in pendingItems.artikel" :key="`artikel-${item.id}`"
                                class="flex items-start p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-orange-300 dark:hover:border-orange-600 transition-colors duration-150">
                                <div class="flex-shrink-0 mr-3">
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                :d="getTypeIcon('artikel')" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <Link :href="item.url"
                                        class="text-sm font-medium text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ item.title }}
                                    </Link>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        oleh {{ item.creator }} • {{ formatDate(item.created_at) }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 ml-2">
                                    Artikel
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
                :class="(can('validasi kamus') || can('approve artikel')) ? '' : 'lg:col-span-3'">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                        Recent Activity
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="activity in recentActivities.slice(0, 8)" :key="activity.id"
                            class="flex items-start">
                            <div class="flex-shrink-0 mr-3">
                                <div
                                    class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-900 dark:text-white">
                                    <span class="font-medium">{{ activity.causer || 'System' }}</span>
                                    {{ activity.description }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ formatDateTime(activity.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <Link v-if="hasRole('super-admin')" :href="route('activity-logs.index')"
                        class="block mt-4 text-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                    Lihat Semua Activity →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="mt-6 md:mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Most Viewed Artikel -->
            <div
                class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl shadow-sm border border-green-200 dark:border-green-700 p-6">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Most Viewed Artikel
                </h3>
                <div class="space-y-3">
                    <component :is="can('view artikel') ? Link : 'div'"
                        v-for="(item, index) in topPerformers.most_viewed_artikel" :key="`top-artikel-${index}`"
                        :href="can('view artikel') ? item.url : undefined" :class="[
                            'flex items-center justify-between p-3 bg-white/50 dark:bg-slate-800/50 rounded-lg transition-colors duration-150',
                            can('view artikel') ? 'hover:bg-white dark:hover:bg-slate-800 cursor-pointer' : 'opacity-60 cursor-not-allowed'
                        ]">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span :class="[
                                'flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold flex-shrink-0',
                                index === 0 ? 'bg-yellow-400 text-yellow-900' : index === 1 ? 'bg-slate-400 text-slate-900' : 'bg-orange-400 text-orange-900'
                            ]">
                                {{ index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-slate-900 dark:text-white line-clamp-1">
                                {{ item.title }}
                            </span>
                        </div>
                        <span
                            class="text-sm font-semibold text-green-600 dark:text-green-400 flex items-center ml-2 flex-shrink-0">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ item.views }}
                        </span>
                    </component>
                </div>
            </div>

            <!-- Most Viewed Modul -->
            <div
                class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl shadow-sm border border-orange-200 dark:border-orange-700 p-6">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Most Viewed Modul
                </h3>
                <div class="space-y-3">
                    <component :is="can('view modul pembelajaran') ? Link : 'div'"
                        v-for="(item, index) in topPerformers.most_viewed_modul" :key="`top-modul-${index}`"
                        :href="can('view modul pembelajaran') ? item.url : undefined" :class="[
                            'flex items-center justify-between p-3 bg-white/50 dark:bg-slate-800/50 rounded-lg transition-colors duration-150',
                            can('view modul pembelajaran') ? 'hover:bg-white dark:hover:bg-slate-800 cursor-pointer' : 'opacity-60 cursor-not-allowed'
                        ]">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span :class="[
                                'flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold flex-shrink-0',
                                index === 0 ? 'bg-yellow-400 text-yellow-900' : index === 1 ? 'bg-slate-400 text-slate-900' : 'bg-orange-400 text-orange-900'
                            ]">
                                {{ index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-slate-900 dark:text-white line-clamp-1">
                                {{ item.title }}
                            </span>
                        </div>
                        <span
                            class="text-sm font-semibold text-orange-600 dark:text-orange-400 flex items-center ml-2 flex-shrink-0">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ item.views }}
                        </span>
                    </component>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>