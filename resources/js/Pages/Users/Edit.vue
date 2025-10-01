<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { computed } from 'vue';

const { t } = useTranslations();

const props = defineProps({
    user: Object,
    roles: Array,
});

const page = usePage();
const currentUser = page.props.auth.user;

// Filter roles
const availableRoles = computed(() => {
    if (currentUser.roles?.includes('super-admin')) {
        return props.roles;
    }
    return props.roles.filter(role => role.name !== 'super-admin');
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles ? props.user.roles.map(role => role.name) : [],
});

const submit = () => {
    form.put(`/users/${props.user.id}`);
};
</script>

<template>
    <Head title="Edit User" />

    <AdminLayout>
        <template #title>Edit User</template>

        <!-- Breadcrumb -->
        <div class="mb-6 md:mb-8">
            <nav class="flex items-center space-x-2 text-sm">
                <Link href="/users" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">Users</Link>
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 dark:text-white font-medium">Edit {{ t('messages.users') }}</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Edit {{ t('messages.user_information') }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Update user details and permissions</p>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ t('messages.name') }}
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500 dark:focus:ring-red-400 focus:border-red-500 dark:focus:border-red-400': form.errors.name }"
                        />
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ t('messages.email') }}
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500 dark:focus:ring-red-400 focus:border-red-500 dark:focus:border-red-400': form.errors.email }"
                        />
                        <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ t('messages.password') }}
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500 dark:focus:ring-red-400 focus:border-red-500 dark:focus:border-red-400': form.errors.password }"
                        />
                        <div v-if="form.errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.password }}
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ t('messages.blank_confirm') }}
                        </p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ t('messages.confirm') }} {{ t('messages.password') }}
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                        />
                    </div>

                    <!-- Roles Selection -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                            {{ t('messages.roles') }}
                        </label>
                        <div class="space-y-2">
                            <div v-for="role in availableRoles" :key="role.id"
                                class="flex items-center p-3 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                                <input
                                    :id="`role-${role.id}`"
                                    v-model="form.roles"
                                    :value="role.name"
                                    type="checkbox"
                                    class="w-4 h-4 text-blue-600 dark:text-blue-500 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-2"
                                />
                                <label :for="`role-${role.id}`" class="ml-3 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">{{ role.name }}</label>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-6 border-t border-slate-200 dark:border-slate-700 gap-3">
                        <Link 
                            href="/users" 
                            class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                        >
                            {{ t('messages.cancel') }}
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-green-600 to-blue-600 text-white font-medium text-sm rounded-xl hover:from-green-700 hover:to-blue-700 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md"
                        >
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>{{ t('messages.update') }} {{ t('messages.users') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>