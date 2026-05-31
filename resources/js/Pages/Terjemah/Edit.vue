<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    terjemah: { type: Object, required: true },
});

const form = useForm({
    teks_indonesia:      props.terjemah.teks_indonesia,
    terjemahan_pengguna: props.terjemah.terjemahan_pengguna,
});

const submit = () => {
    form.put(route('terjemah.update', props.terjemah.id));
};
</script>

<template>
    <Head title="Edit Pengujian" />
    <AdminLayout>
        <template #title>Edit Pengujian</template>

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Edit Pengujian</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    <span v-if="terjemah.status === 4" class="text-red-600 dark:text-red-400 font-medium">
                        Data ini sebelumnya ditolak. Perbaiki lalu kirim ulang.
                    </span>
                    <span v-else>Edit teks dan terjemahan sebelum divalidasi.</span>
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
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Teks Bahasa Indonesia <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.teks_indonesia" rows="5" required
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-y"
                            :class="{ 'border-red-500': form.errors.teks_indonesia }"></textarea>
                        <p v-if="form.errors.teks_indonesia" class="mt-1.5 text-sm text-red-600">{{ form.errors.teks_indonesia }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Terjemahan Bahasa Melayu Belitung <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.terjemahan_pengguna" rows="5" required
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-y"
                            :class="{ 'border-red-500': form.errors.terjemahan_pengguna }"></textarea>
                        <p v-if="form.errors.terjemahan_pengguna" class="mt-1.5 text-sm text-red-600">{{ form.errors.terjemahan_pengguna }}</p>
                    </div>

                    <div v-if="terjemah.status === 4"
                        class="flex items-start gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-800">
                        <svg class="w-5 h-5 text-yellow-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Setelah disimpan, data akan kembali ke status <strong>Menunggu</strong> untuk divalidasi ulang.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 px-6 py-4 flex justify-end gap-3">
                <Link :href="route('terjemah.index')"
                    class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-sm font-medium rounded-lg transition-colors">
                    Batal
                </Link>
                <button type="submit" :disabled="form.processing"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
