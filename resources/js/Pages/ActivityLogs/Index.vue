<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    activities: Object,
    filters: Object,
});

const filters = ref({
    event: props.filters?.event || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    router.get('/activity-logs', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { event: '', date_from: '', date_to: '' };
    router.get('/activity-logs');
};

const getEventColor = (description) => {
    if (description.includes('created')) return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
    if (description.includes('updated')) return 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
    if (description.includes('deleted')) return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
    if (description.includes('login')) return 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300';
    if (description.includes('logout')) return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
    return 'bg-slate-100 dark:bg-slate-700/50 text-slate-800 dark:text-slate-300';
};
</script>

<template>

    <Head title="Activity Logs" />

    <AdminLayout>
        <template #title>Activity Logs</template>

        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">System Activity Logs</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Track all user activities and changes</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 md:p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Event</label>
                    <input v-model="filters.event" type="text" placeholder="e.g., login, created"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 placeholder:text-slate-400 dark:placeholder:text-slate-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Date From</label>
                    <input v-model="filters.date_from" type="date"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Date To</label>
                    <input v-model="filters.date_to" type="date"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400" />
                </div>
                <div class="flex items-end gap-2">
                    <button @click="applyFilters"
                        class="flex-1 px-4 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                        Apply
                    </button>
                    <button @click="clearFilters"
                        class="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Event</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Subject</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Date</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="activity in activities.data" :key="activity.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <td class="px-6 py-4">
                                <span
                                    :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', getEventColor(activity.description)]">
                                    {{ activity.description }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="activity.causer" class="text-sm">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ activity.causer.name }}</div>
                                    <div class="text-slate-500 dark:text-slate-400">{{ activity.causer.email }}</div>
                                </div>
                                <span v-else class="text-sm text-slate-400 dark:text-slate-500 italic">System</span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="activity.subject_type" class="text-sm text-slate-900 dark:text-slate-200">
                                    {{ activity.subject_type.split('\\').pop() }} #{{ activity.subject_id }}
                                </div>
                                <span v-else class="text-sm text-slate-400 dark:text-slate-500">-</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                {{ new Date(activity.created_at).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Link :href="`/activity-logs/${activity.id}`"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50">
                                View Details
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="activities.links" class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }} results
                </div>
                <div class="flex gap-2">
                    <template v-for="link in activities.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" :class="[
                            'px-3 py-1.5 text-sm rounded-lg transition-colors',
                            link.active
                                ? 'bg-blue-600 dark:bg-blue-500 text-white'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]" v-html="link.label" preserve-scroll />
                        <span v-else
                            :class="'px-3 py-1.5 text-sm rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed'"
                            v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>