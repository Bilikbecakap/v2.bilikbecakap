<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    activity: Object,
});

const formatProperties = (properties) => {
    if (!properties) return 'No additional data';
    return JSON.stringify(properties, null, 2);
};
</script>

<template>
    <Head title="Activity Detail" />

    <AdminLayout>
        <template #title>Activity Log Detail</template>

        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-sm">
                <Link href="/activity-logs" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Activity Logs</Link>
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 dark:text-white font-medium">Detail #{{ activity.id }}</span>
            </nav>
        </div>

        <!-- Activity Detail Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Activity Information</h3>
            </div>
            
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ID -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Activity ID</dt>
                        <dd class="text-sm font-mono text-slate-900 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/50 px-2 py-1 rounded inline-block">
                            {{ activity.id }}
                        </dd>
                    </div>

                    <!-- Event -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Event</dt>
                        <dd>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                {{ activity.description }}
                            </span>
                        </dd>
                    </div>

                    <!-- Causer -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Performed By</dt>
                        <dd v-if="activity.causer" class="text-sm text-slate-900 dark:text-slate-200">
                            <div class="font-medium">{{ activity.causer.name }}</div>
                            <div class="text-slate-500 dark:text-slate-400">{{ activity.causer.email }}</div>
                        </dd>
                        <dd v-else class="text-sm text-slate-400 dark:text-slate-500 italic">System</dd>
                    </div>

                    <!-- Subject -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Subject</dt>
                        <dd v-if="activity.subject_type" class="text-sm text-slate-900 dark:text-slate-200">
                            <div class="font-medium">{{ activity.subject_type.split('\\').pop() }}</div>
                            <div class="text-slate-500 dark:text-slate-400">ID: {{ activity.subject_id }}</div>
                        </dd>
                        <dd v-else class="text-sm text-slate-400 dark:text-slate-500">-</dd>
                    </div>

                    <!-- Log Name -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Log Name</dt>
                        <dd class="text-sm text-slate-900 dark:text-slate-200">{{ activity.log_name || 'default' }}</dd>
                    </div>

                    <!-- Event -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Event Type</dt>
                        <dd class="text-sm text-slate-900 dark:text-slate-200">{{ activity.event || '-' }}</dd>
                    </div>

                    <!-- Created At -->
                    <div>
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Date & Time</dt>
                        <dd class="text-sm text-slate-900 dark:text-slate-200">
                            {{ new Date(activity.created_at).toLocaleString('id-ID', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            }) }}
                        </dd>
                    </div>

                    <!-- Batch UUID -->
                    <div v-if="activity.batch_uuid">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Batch UUID</dt>
                        <dd class="text-sm font-mono text-slate-900 dark:text-slate-200">{{ activity.batch_uuid }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Properties Card -->
        <div v-if="activity.properties" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Additional Properties</h3>
            </div>
            
            <div class="p-6">
                <pre class="bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-200 p-4 rounded-lg overflow-x-auto text-sm border border-slate-200 dark:border-slate-700">{{ formatProperties(activity.properties) }}</pre>
            </div>
        </div>

        <!-- Back Button -->
        <div>
            <Link 
                href="/activity-logs" 
                class="inline-flex items-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Activity Logs
            </Link>
        </div>
    </AdminLayout>
</template>