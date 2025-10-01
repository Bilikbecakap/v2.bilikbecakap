<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useTranslations } from '@/composables/useTranslations';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
});

const { can } = usePermissions();
const { t } = useTranslations();

const page = usePage();
const currentUser = page.props.auth.user;

// Function to check if user is super-admin
const isSuperAdmin = (user) => {
    return user.roles?.some(role => role.name === 'super-admin');
};

// Function to check if current user can modify this user
const canModifyUser = (user) => {
    // If target user is super-admin and current user is not super-admin, deny access
    if (isSuperAdmin(user) && !currentUser.roles?.includes('super-admin')) {
        return false;
    }
    return true;
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`);
    }
};
</script>

<template>

    <Head title="Users" />

    <AdminLayout>
        <template #title>Users Management</template>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">{{ t('messages.all_users')
                        }}</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ t('messages.manage_users') }}</p>
                </div>
                <!-- Tombol Create - hanya muncul jika punya permission -->
                <Link v-if="can('create users')" href="/users/create"
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium text-sm rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ t('messages.add_new_user') }}
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ t('messages.total_users')
                            }}</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ users.length }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ t('messages.active_users')
                            }}</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{users.filter(user =>
                            user.email_verified_at).length}}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ t('messages.with_roles') }}
                        </p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{users.filter(user => user.roles
                            &&
                            user.roles.length >
                            0).length}}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">{{ t('messages.users_list') }}</h3>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.users') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.email') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.roles') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.created') }}</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                {{ t('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="user in users" :key="user.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        <img :src="user.profile?.photo ? `/storage/${user.profile.photo}` : '/default.jpg'"
                                            :alt="user.name" class="w-full h-full object-cover">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ user.name }}
                                        </div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">ID: {{ user.id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900 dark:text-slate-200">{{ user.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="user.roles && user.roles.length > 0" class="flex flex-wrap gap-1">
                                    <span v-for="role in user.roles" :key="role.id"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                        {{ role.name }}
                                    </span>
                                </div>
                                <span v-else class="text-xs text-slate-400 dark:text-slate-500 italic">No roles
                                    assigned</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                {{ new Date(user.created_at).toLocaleDateString('id-ID', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                }) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <Link v-if="can('view users')" :href="`/users/${user.id}`"
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

                                    <Link v-if="can('edit users') && canModifyUser(user)"
                                        :href="`/users/${user.id}/edit`"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-150"
                                        title="Edit User">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    {{ t('messages.edit') }}
                                    </Link>

                                    <button v-if="can('delete users') && canModifyUser(user)"
                                        @click="deleteUser(user.id)"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors duration-150"
                                        title="Delete User">
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
                <div v-if="users.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 text-slate-400 dark:text-slate-600 mx-auto mb-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">No users found</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-6">Get started by creating your first user.</p>
                    <Link href="/users/create"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium text-sm rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ t('messages.add_new_user') }}
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>