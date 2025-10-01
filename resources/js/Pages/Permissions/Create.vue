<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post('/permissions', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Permission" />

    <AdminLayout>
        <template #title>Create New Permission</template>

        <div class="max-w-2xl">
            <!-- Info Alert -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-300">
                        <p class="font-medium mb-1">Permission Naming Convention:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Format: <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">action resource</code></li>
                            <li>Examples: <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">view blogs</code>, <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">create products</code></li>
                            <li>Actions: view, create, edit, delete</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Permission Information</h3>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Permission Name <span class="text-red-500 dark:text-red-400">*</span>
                        </label>
                        <input 
                            id="name"
                            v-model="form.name"
                            type="text" 
                            placeholder="e.g., view blogs"
                            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-purple-500 dark:focus:border-purple-400 transition-colors placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            :class="{ 'border-red-500 dark:border-red-600': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Use lowercase with spaces (e.g., "view blogs", "create users")</p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                        <Link 
                            href="/permissions" 
                            class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-200 disabled:opacity-50 shadow-sm hover:shadow-md"
                        >
                            <span v-if="!form.processing">Create Permission</span>
                            <span v-else>Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>