<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    dataset: Object,
});

const form = useForm({
    bahasa_belitung: props.dataset.bahasa_belitung || '',
    bahasa_indonesia: props.dataset.bahasa_indonesia || '',
});

const isSubmitting = ref(false);

const submit = () => {
    if (isSubmitting.value) return;
    
    isSubmitting.value = true;
    
    form.put(route('dataset-translate.update', props.dataset.id), {
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Edit Dataset Translate" />

    <AdminLayout>
        <template #title>Edit Dataset Translate</template>

        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <Link 
                            :href="route('dataset-translate.index')" 
                            class="inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                            Dataset Translate
                        </Link>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-1 text-sm font-medium text-slate-500 dark:text-slate-400 md:ml-2">Edit Dataset</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">Edit Dataset Translate</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Update data training untuk sistem translate</p>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100">
                        Informasi Dataset
                    </h3>
                    <div class="mt-2 text-sm text-blue-800 dark:text-blue-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <span class="font-medium">ID:</span> #{{ dataset.id }}
                            </div>
                            <div>
                                <span class="font-medium">Dibuat:</span> {{ formatDate(dataset.created_at) }}
                            </div>
                            <div v-if="dataset.updated_at && dataset.updated_at !== dataset.created_at" class="sm:col-span-2">
                                <span class="font-medium">Terakhir diupdate:</span> {{ formatDate(dataset.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Form Edit Dataset</h3>
            </div>

            <form @submit.prevent="submit" class="p-6">
                <div class="space-y-6">
                    <!-- Bahasa Belitung Input -->
                    <div>
                        <label for="bahasa_belitung" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Bahasa Belitung <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="bahasa_belitung"
                            v-model="form.bahasa_belitung"
                            type="text"
                            maxlength="255"
                            placeholder="Contoh: aku, kamu, makan, minum"
                            class="block w-full px-4 py-2.5 border rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                            :class="form.errors.bahasa_belitung ? 'border-red-300 dark:border-red-600' : 'border-slate-300 dark:border-slate-600'"
                        >
                        <div class="flex items-center justify-between mt-1">
                            <p v-if="form.errors.bahasa_belitung" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.bahasa_belitung }}
                            </p>
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-auto">
                                {{ form.bahasa_belitung.length }}/255
                            </span>
                        </div>
                    </div>

                    <!-- Bahasa Indonesia Input -->
                    <div>
                        <label for="bahasa_indonesia" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Bahasa Indonesia <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="bahasa_indonesia"
                            v-model="form.bahasa_indonesia"
                            type="text"
                            maxlength="255"
                            placeholder="Contoh: saya, kamu, makan, minum"
                            class="block w-full px-4 py-2.5 border rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                            :class="form.errors.bahasa_indonesia ? 'border-red-300 dark:border-red-600' : 'border-slate-300 dark:border-slate-600'"
                        >
                        <div class="flex items-center justify-between mt-1">
                            <p v-if="form.errors.bahasa_indonesia" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.bahasa_indonesia }}
                            </p>
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-auto">
                                {{ form.bahasa_indonesia.length }}/255
                            </span>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                    Perhatian
                                </h3>
                                <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Perubahan data akan langsung mempengaruhi sistem translate</li>
                                        <li>Pastikan terjemahan tetap akurat setelah diubah</li>
                                        <li>Gunakan huruf kecil untuk konsistensi data</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Changes Preview (if data changed) -->
                    <div v-if="form.isDirty" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                    Ada Perubahan yang Belum Disimpan
                                </h3>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-300 space-y-1">
                                    <div v-if="form.bahasa_belitung !== dataset.bahasa_belitung" class="flex items-start">
                                        <span class="font-medium min-w-[140px]">Bahasa Belitung:</span>
                                        <div class="flex-1">
                                            <div class="line-through text-green-600 dark:text-green-400">{{ dataset.bahasa_belitung }}</div>
                                            <div class="font-medium">{{ form.bahasa_belitung }}</div>
                                        </div>
                                    </div>
                                    <div v-if="form.bahasa_indonesia !== dataset.bahasa_indonesia" class="flex items-start">
                                        <span class="font-medium min-w-[140px]">Bahasa Indonesia:</span>
                                        <div class="flex-1">
                                            <div class="line-through text-green-600 dark:text-green-400">{{ dataset.bahasa_indonesia }}</div>
                                            <div class="font-medium">{{ form.bahasa_indonesia }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <Link 
                            :href="route('dataset-translate.index')"
                            class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali
                        </Link>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Reset Button -->
                        <button
                            v-if="form.isDirty"
                            type="button"
                            @click="form.reset()"
                            class="inline-flex items-center px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </button>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing || isSubmitting || !form.isDirty"
                            class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="form.processing || isSubmitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ form.processing || isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- History Section (Optional) -->
        <div class="mt-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Data Saat Ini</h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            Bahasa Belitung
                        </dt>
                        <dd class="text-base font-medium text-slate-900 dark:text-white">
                            {{ dataset.bahasa_belitung }}
                        </dd>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            Bahasa Indonesia
                        </dt>
                        <dd class="text-base font-medium text-slate-900 dark:text-white">
                            {{ dataset.bahasa_indonesia }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </AdminLayout>
</template>