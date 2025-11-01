<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  artikel: Object,
});

const form = useForm({
  nama_kategori: props.artikel.nama_kategori,
  deskripsi: props.artikel.deskripsi,
  is_active: props.artikel.is_active,
  urutan: props.artikel.urutan,
});

const submit = () => {
  form.put(route("data-master.artikel.update", props.artikel.id), {
    onSuccess: () => {
      // Handle success
    },
  });
};
</script>

<template>
  <Head title="Edit Master Artikel" />

  <AdminLayout>
    <template #title>Edit Master Artikel</template>

    <!-- Header Section -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">
            Edit Kategori Artikel
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Ubah informasi kategori artikel
          </p>
        </div>

        <div class="flex gap-3">
          <Link
            :href="route('data-master.artikel.index')"
            class="inline-flex items-center px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors duration-150"
          >
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            Kembali
          </Link>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700"
    >
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
          Form Edit Kategori Artikel
        </h3>
      </div>

      <form @submit.prevent="submit" class="p-6 space-y-6">
        <!-- Nama Kategori -->
        <div>
          <label
            for="nama_kategori"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            Nama Kategori <span class="text-red-500">*</span>
          </label>
          <input
            id="nama_kategori"
            v-model="form.nama_kategori"
            type="text"
            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
            placeholder="Masukkan nama kategori"
          />
          <div
            v-if="form.errors.nama_kategori"
            class="mt-1 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.nama_kategori }}
          </div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label
            for="deskripsi"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            Deskripsi
          </label>
          <textarea
            id="deskripsi"
            v-model="form.deskripsi"
            rows="4"
            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
            placeholder="Masukkan deskripsi kategori"
          ></textarea>
          <div
            v-if="form.errors.deskripsi"
            class="mt-1 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.deskripsi }}
          </div>
        </div>

        <!-- Urutan -->
        <div>
          <label
            for="urutan"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            Urutan <span class="text-red-500">*</span>
          </label>
          <input
            id="urutan"
            v-model.number="form.urutan"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white"
            placeholder="0"
          />
          <div
            v-if="form.errors.urutan"
            class="mt-1 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.urutan }}
          </div>
        </div>

        <!-- Status -->
        <div>
          <label
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            Status
          </label>
          <div class="flex items-center">
            <input
              id="is_active"
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 rounded"
            />
            <label
              for="is_active"
              class="ml-2 block text-sm text-slate-700 dark:text-slate-300"
            >
              Aktif
            </label>
          </div>
          <div
            v-if="form.errors.is_active"
            class="mt-1 text-sm text-red-600 dark:text-red-400"
          >
            {{ form.errors.is_active }}
          </div>
        </div>

        <!-- Submit Button -->
        <div
          class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700"
        >
          <Link
            :href="route('data-master.artikel.index')"
            class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Update</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
