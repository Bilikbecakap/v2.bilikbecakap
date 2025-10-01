<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps({
    roles: Array,
});

const { can } = usePermissions();
const { t } = useTranslations();

const deleteRole = (id) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(`/roles/${id}`);
    }
};
</script>

<template>

    <Head title="Roles" />

    <AdminLayout>
        <template #title>Roles Management</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">{{ t('messages.all_roles') }}</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ t('messages.manage_roles') }}</p>
                </div>
                <Link v-if="can('create roles')" href="/roles/create"
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-blue-600 text-white font-medium text-sm rounded-xl hover:from-green-700 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ t('messages.add_new_role') }}

                </Link>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">{{ t('messages.roles_list') }}</h3>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.roles') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.permissions') }}</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.created') }}</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="role in roles" :key="role.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ role.name }}</div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ t('messages.roles') }} ID: {{ role.id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="role.permissions && role.permissions.length > 0"
                                    class="flex flex-wrap gap-1">
                                    <span v-for="permission in role.permissions.slice(0, 3)" :key="permission.id"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                        {{ permission.name }}
                                    </span>
                                    <span v-if="role.permissions.length > 3"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                                        +{{ role.permissions.length - 3 }} more
                                    </span>
                                </div>
                                <span v-else class="text-xs text-slate-400 italic">No permissions assigned</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                {{ new Date(role.created_at).toLocaleDateString('id-ID', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                }) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <Link v-if="can('view roles')" :href="`/roles/${role.id}`"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors duration-150"
                                        title="View Details">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ t('messages.view') }}
                                    </Link>
                                    <Link v-if="can('edit roles')" :href="`/roles/${role.id}/edit`"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-150"
                                        title="Edit Role">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    {{ t('messages.edit') }}
                                    </Link>
                                    <button v-if="can('delete roles')" @click="deleteRole(role.id)"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                                        title="Delete Role">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        {{ t('messages.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="roles.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">No roles found</h3>
                    <p class="text-slate-500 mb-6">Get started by creating your first role.</p>
                    <Link href="/roles/create"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white font-medium text-sm rounded-xl hover:from-green-700 hover:to-blue-700 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ t('messages.add_new_role') }}
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>