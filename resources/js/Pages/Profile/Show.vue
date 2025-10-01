<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

defineProps({
    user: Object
});
</script>

<template>

    <Head title="My Profile" />

    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 dark:text-white">My Profile</h1>
                    <p class="text-slate-600 dark:text-slate-400 mt-1">View and manage your account information</p>
                </div>
                <Link :href="route('profile.edit')"
                    class="px-4 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors flex items-center space-x-2">
                <font-awesome-icon icon="edit" class="w-4 h-4" />
                <span>Edit Profile</span>
                </Link>
            </div>

            <!-- Profile Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <!-- Cover -->
                <div class="h-32 bg-gradient-to-r from-blue-500 to-purple-500"></div>

                <!-- Profile Content -->
                <div class="px-8 pb-8">
                    <!-- Photo -->
                    <div class="flex justify-center -mt-16 mb-6">
                        <div class="w-32 h-32 rounded-full border-4 border-white dark:border-slate-800 shadow-lg overflow-hidden flex items-center justify-center">
                            <img 
                                :src="user.profile?.photo ? `/storage/${user.profile.photo}` : '/default.jpg'"
                                :alt="user.name" 
                                class="w-full h-full object-cover"
                            >
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">{{ user.name }}</h2>
                        <p class="text-slate-600 dark:text-slate-400 mt-1">{{ user.email }}</p>
                        <div class="flex justify-center gap-2 mt-3">
                            <span v-for="role in user.roles" :key="role.id"
                                class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                                {{ role.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="space-y-12">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Phone -->
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <font-awesome-icon icon="phone" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Phone Number</p>
                                    <p class="text-slate-800 dark:text-slate-200 font-medium mt-1">
                                        {{ user.profile?.phone || 'Not set' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <font-awesome-icon icon="envelope" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Email Address</p>
                                    <p class="text-slate-800 dark:text-slate-200 font-medium mt-1">{{ user.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex items-start space-x-4">
                            <div
                                class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <font-awesome-icon icon="map-marker-alt" class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Address</p>
                                <p class="text-slate-800 dark:text-slate-200 font-medium mt-1">
                                    {{ user.profile?.address || 'Not set' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>