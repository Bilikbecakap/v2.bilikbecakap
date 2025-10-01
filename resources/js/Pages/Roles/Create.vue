<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps({
    permissions: Array,
});

const form = useForm({
    name: '',
    permissions: [],
});

const submit = () => {
    form.post('/roles');
};

const { t } = useTranslations();

</script>

<template>
    <Head title="Create Role" />

    <AdminLayout>
        <template #title>Create New Role</template>

        <!-- Breadcrumb -->
        <div class="mb-6 md:mb-8">
            <nav class="flex items-center space-x-2 text-sm">
                <Link href="/roles" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">{{ t('messages.roles') }}</Link>
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 dark:text-white font-medium">{{ t('messages.create_new_role') }}</span>
            </nav>
        </div>

        <!-- Form Container -->
        <div class="max-w-2xl">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <!-- Form Header -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-600 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">{{ t('messages.role_information') }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t('messages.fill_role') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Role Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ t('messages.roles') }} {{ t('messages.name') }}
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-green-500 dark:focus:border-green-400 transition-colors duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500 dark:focus:ring-red-400 focus:border-red-500 dark:focus:border-red-400': form.errors.name }"
                            placeholder="e.g., manager, editor, etc."
                        />
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Permissions Selection -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                            {{ t('messages.permissions') }}
                        </label>
                        <div v-if="permissions.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-xl p-4 bg-slate-50 dark:bg-slate-700/30">
                            <div v-for="permission in permissions" :key="permission.id" class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200 bg-white dark:bg-slate-800">
                                <input
                                    :id="`permission-${permission.id}`"
                                    v-model="form.permissions"
                                    :value="permission.name"
                                    type="checkbox"
                                    class="w-4 h-4 text-green-600 dark:text-green-500 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500 dark:focus:ring-green-400 focus:ring-2"
                                />
                                <label :for="`permission-${permission.id}`" class="ml-3 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">{{ permission.name }}</label>
                            </div>
                        </div>
                        <div v-else class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.081 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">No permissions available</h4>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">Please run the permission seeder first to create available permissions.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-6 border-t border-slate-200 dark:border-slate-700 gap-3">
                        <Link 
                            href="/roles" 
                            class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                        >
                            {{ t('messages.cancel') }}
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-green-600 to-blue-600 text-white font-medium text-sm rounded-xl hover:from-green-700 hover:to-blue-700 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md"
                        >
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>{{ t('messages.create_role') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>