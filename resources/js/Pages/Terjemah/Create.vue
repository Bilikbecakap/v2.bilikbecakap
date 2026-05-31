<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    teks_indonesia:      '',
    terjemahan_pengguna: '',
});

const submit = () => {
    form.post(route('terjemah.store'));
};
</script>

<template>
    <Head title="Tambah Pengujian" />
    <AdminLayout>
        <template #title>Tambah Pengujian</template>

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Tambah Pengujian Terjemahan</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Masukkan teks Bahasa Indonesia dan terjemahannya dalam Bahasa Melayu Belitung untuk divalidasi.
                </p>
            </div>
            <Link :href="route('terjemah.index')"
                class="inline-flex items-center px-4 py-2.5 bg-slate-600 text-white text-sm font-medium rounded-xl hover:bg-slate-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Isi Pengujian</h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Teks Indonesia -->
                    <div>
                        <label for="teks_indonesia" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Teks Bahasa Indonesia <span class="text-red-500">*</span>
                        </label>
                        <textarea id="teks_indonesia" v-model="form.teks_indonesia" rows="5" required
                            placeholder="Masukkan kalimat, paragraf, atau teks panjang dalam Bahasa Indonesia yang ingin diuji terjemahannya..."
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-y"
                            :class="{ 'border-red-500': form.errors.teks_indonesia }"></textarea>
                        <p v-if="form.errors.teks_indonesia" class="mt-1.5 text-sm text-red-600">{{ form.errors.teks_indonesia }}</p>
                    </div>

                    <!-- Terjemahan -->
                    <div>
                        <label for="terjemahan_pengguna" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Terjemahan Bahasa Melayu Belitung <span class="text-red-500">*</span>
                        </label>
                        <textarea id="terjemahan_pengguna" v-model="form.terjemahan_pengguna" rows="5" required
                            placeholder="Masukkan terjemahan dalam Bahasa Melayu Belitung..."
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-y"
                            :class="{ 'border-red-500': form.errors.terjemahan_pengguna }"></textarea>
                        <p v-if="form.errors.terjemahan_pengguna" class="mt-1.5 text-sm text-red-600">{{ form.errors.terjemahan_pengguna }}</p>
                    </div>

                    <!-- Info -->
                    <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Setelah dikirim, data akan menunggu validasi dari validator.
                            Validator akan mengecek dan memberikan koreksi jika ada yang perlu diperbaiki.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 px-6 py-4 flex justify-end gap-3">
                <Link :href="route('terjemah.index')"
                    class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-sm font-medium rounded-lg transition-colors">
                    Batal
                </Link>
                <button type="submit" :disabled="form.processing"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    <span v-if="form.processing">Mengirim...</span>
                    <span v-else>Kirim Pengujian</span>
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
