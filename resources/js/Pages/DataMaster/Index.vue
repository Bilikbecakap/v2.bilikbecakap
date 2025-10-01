<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useTranslations } from '@/composables/useTranslations';

const { can } = usePermissions();
const { t } = useTranslations();

const masterMenus = [
    {
        id: 1,
        title: 'Master Gambar',
        description: 'Mengatur sub kategori gambar untuk view di halaman utama',
        icon: 'image',
        color: 'from-purple-500 to-pink-500',
        href: '#',
        disabled: true
    },
    {
        id: 2,
        title: 'Master Artikel/Blog',
        description: 'Mengatur sub kategori artikel untuk mengorganisir artikel',
        icon: 'document',
        color: 'from-blue-500 to-cyan-500',
        href: '#',
        disabled: true
    },
    {
        id: 3,
        title: 'Master Kamus',
        description: 'Untuk mengatur sub kategori kamus dan mengorganisir kamus',
        icon: 'book',
        color: 'from-green-500 to-emerald-500',
        href: '#',
        disabled: true
    },
    {
        id: 4,
        title: 'Master Modul',
        description: 'Mengatur kategori dan sub-sub modul pembelajaran',
        icon: 'folder',
        color: 'from-orange-500 to-red-500',
        href: '#',
        disabled: true
    },
    {
        id: 5,
        title: 'Master Kategori',
        description: 'Mengatur kategori konten untuk sistem',
        icon: 'tag',
        color: 'from-indigo-500 to-purple-500',
        href: '#',
        disabled: true
    },
    {
        id: 6,
        title: 'Master Media',
        description: 'Mengelola media dan file multimedia',
        icon: 'play',
        color: 'from-pink-500 to-rose-500',
        href: '#',
        disabled: true
    }
];

const getIcon = (iconName) => {
    const icons = {
        image: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        book: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        folder: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
        tag: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        play: 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    };
    return icons[iconName] || icons.folder;
};
</script>

<template>
    <Head title="Data Master" />

    <AdminLayout>
        <template #title>Data Master</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">Data Master Management</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Kelola semua data master untuk sistem</p>
                </div>
            </div>
        </div>

        <!-- Master Menus Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="menu in masterMenus" :key="menu.id"
                :class="[
                    'bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-all duration-200',
                    menu.disabled 
                        ? 'opacity-60 cursor-not-allowed' 
                        : 'hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 cursor-pointer'
                ]">
                <div class="p-6">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div :class="[
                            'w-14 h-14 rounded-xl flex items-center justify-center',
                            'bg-gradient-to-r', menu.color
                        ]">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(menu.icon)" />
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-2 flex items-center">
                            {{ menu.title }}
                            <span v-if="menu.disabled" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                Coming Soon
                            </span>
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ menu.description }}
                        </p>
                    </div>

                    <!-- Button -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                        <button
                            :disabled="menu.disabled"
                            :class="[
                                'w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
                                menu.disabled
                                    ? 'bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 cursor-not-allowed'
                                    : 'bg-gradient-to-r ' + menu.color + ' text-white hover:shadow-md'
                            ]">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            {{ menu.disabled ? 'Belum Tersedia' : 'Kelola Data' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>