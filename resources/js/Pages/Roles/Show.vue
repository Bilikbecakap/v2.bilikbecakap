<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps({
    role: Object,
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
    <Head :title="`Role: ${role.name}`" />

    <AdminLayout>
        <template #title>Role Details</template>

        <!-- Breadcrumb -->
        <div class="mb-6 md:mb-8">
            <nav class="flex items-center space-x-2 text-sm">
                <Link href="/roles" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">{{ t('messages.roles') }}</Link>
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 dark:text-white font-medium">{{ role.name }}</span>
            </nav>
        </div>

        <!-- Role Header -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-green-600 via-blue-600 to-purple-700 px-6 py-8">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ role.name }}</h2>
                        <p class="text-green-100 text-lg">{{ t('messages.roles') }} ID: {{ role.id }}</p>
                        <div class="flex items-center mt-3">
                            <span v-if="role.permissions && role.permissions.length > 0" 
                                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ role.permissions.length }} {{ t('messages.permissions') }}
                            </span>
                            <span v-else 
                                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                No Permissions
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <Link 
                            v-if="can('edit-roles')"
                            :href="`/roles/${role.id}/edit`" 
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white/20 backdrop-blur-sm text-white font-medium text-sm rounded-xl hover:bg-white/30 transition-all duration-200 border border-white/30"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Role
                        </Link>
                        <button 
                            v-if="can('delete roles')"
                            @click="deleteRole(role.id)" 
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-red-500/80 backdrop-blur-sm text-white font-medium text-sm rounded-xl hover:bg-red-600/80 transition-all duration-200 border border-red-400/50"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Role Information -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">{{ t('messages.role_information') }}</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Role ID</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-200 font-mono bg-slate-100 dark:bg-slate-700/50 px-2 py-1 rounded">{{ role.id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Role Name</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-200 font-medium">{{ role.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Permissions</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-200">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                        {{ role.permissions ? role.permissions.length : 0 }} permissions
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Status</dt>
                                <dd>
                                    <span v-if="role.permissions && role.permissions.length > 0" 
                                          class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                        Active with Permissions
                                    </span>
                                    <span v-else 
                                          class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                        No Permissions Assigned
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Created Date</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-200">
                                    {{ new Date(role.created_at).toLocaleDateString('id-ID', { 
                                        year: 'numeric', 
                                        month: 'long', 
                                        day: 'numeric' 
                                    }) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Last Updated</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-200">
                                    {{ new Date(role.updated_at).toLocaleDateString('id-ID', { 
                                        year: 'numeric', 
                                        month: 'long', 
                                        day: 'numeric' 
                                    }) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Permissions List -->
            <div>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Permissions</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="role.permissions && role.permissions.length > 0">
                            <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Assigned Permissions</h4>
                            <div class="space-y-2 max-h-80 overflow-y-auto">
                                <div v-for="permission in role.permissions" :key="permission.id" 
                                     class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                    <div class="w-6 h-6 bg-green-600 dark:bg-green-500 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ permission.name }}</div>
                                        <div v-if="permission.description" class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ permission.description }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6">
                            <svg class="w-12 h-12 text-slate-400 dark:text-slate-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-1">No Permissions Assigned</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">This role doesn't have any permissions assigned yet.</p>
                            <Link 
                                v-if="can('edit-roles')"
                                :href="`/roles/${role.id}/edit`" 
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-150"
                            >
                                Assign Permissions
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8">
            <Link 
                href="/roles" 
                class="inline-flex items-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Roles
            </Link>
        </div>
    </AdminLayout>
</template>