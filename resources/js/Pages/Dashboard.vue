<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const getCurrentTime = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
};
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <template #title>Dashboard</template>

        <!-- Simple & Elegant Header -->
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

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
            <!-- Total Users Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Users</p>
                        <p class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mt-2">1,247</p>
                        <div class="flex items-center mt-2">
                            <span class="text-sm text-green-600 dark:text-green-400 font-medium">+12%</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400 ml-2">vs last month</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Roles Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Active Roles</p>
                        <p class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mt-2">24</p>
                        <div class="flex items-center mt-2">
                            <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">+3</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400 ml-2">new this week</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Permissions Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6 hover:shadow-md transition-shadow duration-200 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Permissions</p>
                        <p class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mt-2">156</p>
                        <div class="flex items-center mt-2">
                            <span class="text-sm text-purple-600 dark:text-purple-400 font-medium">Active</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400 ml-2">across system</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>