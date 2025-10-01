<template>
  <div class="min-h-screen bg-white dark:bg-slate-900">
    <!-- Navigation -->
    <nav class="border-b border-slate-200 dark:border-slate-700">
      <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-xl font-bold text-slate-900 dark:text-white">
          Laravel Starter
        </a>
        
        <div class="flex items-center gap-4">
          <a href="/login" class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">
            Login
          </a>
          <a href="/register" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Register
          </a>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 dark:border-slate-700 py-8">
      <div class="container mx-auto px-4 text-center text-slate-600 dark:text-slate-400">
        © 2025 Laravel Starter. All rights reserved.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
</script>