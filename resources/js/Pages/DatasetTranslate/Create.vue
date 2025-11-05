<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    bahasa_belitung: '',
    bahasa_indonesia: '',
});

const isSubmitting = ref(false);

const submit = () => {
    if (isSubmitting.value) return;
    
    isSubmitting.value = true;
    
    form.post(route('dataset-translate.store'), {
        onSuccess: () => {
            form.reset();
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <Head title="Tambah Dataset Translate" />

    <AdminLayout>
        <template #title>Tambah Dataset Translate</template>

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
                            <span class="ml-1 text-sm font-medium text-slate-500 dark:text-slate-400 md:ml-2">Tambah Dataset</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Header Section -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">Tambah Dataset Translate</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Tambahkan data training baru untuk sistem translate</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Form Dataset</h3>
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
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                    Tips Penambahan Dataset
                                </h3>
                                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Gunakan huruf kecil untuk konsistensi data</li>
                                        <li>Hindari tanda baca pada data dasar</li>
                                        <li>Pastikan terjemahan akurat dan sesuai konteks</li>
                                        <li>Data akan langsung aktif dan digunakan untuk training</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <Link 
                        :href="route('dataset-translate.index')"
                        class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-all duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </Link>
                    
                    <button
                        type="submit"
                        :disabled="form.processing || isSubmitting"
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="form.processing || isSubmitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing || isSubmitting ? 'Menyimpan...' : 'Simpan Dataset' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Example Section -->
        <div class="mt-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Contoh Data</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Bahasa Belitung</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Bahasa Indonesia</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">aku</td>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">saya</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">kamu</td>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">kamu</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">uma</td>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">ibu</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">apak</td>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">ayah</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">lagi</td>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">sedang</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>