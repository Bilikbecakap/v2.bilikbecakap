<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    preserveScroll: {
        type: Boolean,
        default: true,
    },
});

const pageLinks = computed(() => {
    return (props.data.links || []).filter(link =>
        link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'
    );
});
</script>

<template>
    <div v-if="data.last_page > 1" class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Menampilkan
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ data.from }}</span>
                &ndash;
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ data.to }}</span>
                dari
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ data.total }}</span>
                data
            </p>

            <nav class="flex items-center gap-1">
                <!-- Prev -->
                <Link v-if="data.prev_page_url" :href="data.prev_page_url" :preserve-scroll="preserveScroll"
                    class="inline-flex items-center px-2.5 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <span v-else
                    class="inline-flex items-center px-2.5 py-2 text-sm text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>

                <!-- Page numbers -->
                <template v-for="(link, i) in pageLinks" :key="i">
                    <Link v-if="link.url && !isNaN(link.label)" :href="link.url"
                        :preserve-scroll="preserveScroll" :class="[
                            'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg border transition-colors duration-150',
                            link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700',
                        ]">
                        {{ link.label }}
                    </Link>
                    <span v-else-if="link.label === '...'"
                        class="inline-flex items-center px-2 py-2 text-sm text-slate-400 dark:text-slate-500">
                        &hellip;
                    </span>
                </template>

                <!-- Next -->
                <Link v-if="data.next_page_url" :href="data.next_page_url" :preserve-scroll="preserveScroll"
                    class="inline-flex items-center px-2.5 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
                <span v-else
                    class="inline-flex items-center px-2.5 py-2 text-sm text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            </nav>
        </div>
    </div>
</template>
