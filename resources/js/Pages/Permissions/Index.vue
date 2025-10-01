<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps({
    permissions: Array,
});

const { t } = useTranslations();

const { hasRole } = usePermissions();

const deletePermission = (id, name) => {
    if (confirm(`Are you sure you want to delete permission "${name}"? This may break functionality if it's being used.`)) {
        router.delete(`/permissions/${id}`);
    }
};
</script>

<template>
    <Head title="Permissions" />

    <AdminLayout>
        <template #title>Permissions Management</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">{{ t('messages.all_permissions') }}</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ t('messages.manage_permissions') }}</p>
                </div>
                <Link 
                    href="/permissions/create" 
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-sm hover:shadow-md"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Permission
                </Link>
            </div>
        </div>

        <!-- Permissions Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">{{ t('messages.permissions_list') }}</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Permission Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Guard</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="permission in permissions" :key="permission.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-slate-500 dark:text-slate-400">{{ permission.id }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">{{ permission.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                    {{ permission.guard_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                {{ new Date(permission.created_at).toLocaleDateString('id-ID', { 
                                    year: 'numeric', 
                                    month: 'short', 
                                    day: 'numeric' 
                                }) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button 
                                    @click="deletePermission(permission.id, permission.name)" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                                    title="Delete Permission"
                                >
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>