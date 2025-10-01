<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    user: Object
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.profile?.phone || '',
    address: props.user.profile?.address || '',
    photo: null,
    password: '',
    password_confirmation: '',
    _method: 'PATCH'
});

const photoPreview = ref(
    props.user.profile?.photo
        ? `/storage/${props.user.profile.photo}`
        : '/default.jpg'
);

const handlePhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('profile.update'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        }
    });
};
</script>

<template>

    <Head title="Edit Profile" />

    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Edit Profile</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">Update your account information</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Profile Photo -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Profile Photo</h3>
                    <div class="flex items-center space-x-6">
                        <div
                            class="w-24 h-24 rounded-full overflow-hidden flex items-center justify-center flex-shrink-0 border-2 border-slate-200 dark:border-slate-600">
                            <img :src="photoPreview" alt="Preview" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <input type="file" id="photo" @change="handlePhotoChange" accept="image/*" class="hidden">
                            <label for="photo"
                                class="inline-block px-4 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 cursor-pointer transition-colors">
                                Choose Photo
                            </label>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">JPG, PNG or GIF. Max size 2MB</p>
                            <p v-if="form.errors.photo" class="text-sm text-red-600 dark:text-red-400 mt-1">{{ form.errors.photo }}</p>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Name</label>
                            <input v-model="form.name" type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400">
                            <p v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email</label>
                            <input v-model="form.email" type="email" readonly
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400 cursor-not-allowed focus:ring-0 focus:border-slate-300 dark:focus:border-slate-600">
                            <p v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400 mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Phone</label>
                            <input v-model="form.phone" type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400">
                            <p v-if="form.errors.phone" class="text-sm text-red-600 dark:text-red-400 mt-1">{{ form.errors.phone }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Address</label>
                            <textarea v-model="form.address" rows="3"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"></textarea>
                            <p v-if="form.errors.address" class="text-sm text-red-600 dark:text-red-400 mt-1">{{ form.errors.address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Change Password</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">New Password</label>
                            <input v-model="form.password" type="password"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400">
                            <p v-if="form.errors.password" class="text-sm text-red-600 dark:text-red-400 mt-1">{{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400">
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Leave blank if you don't want to change password</p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4">
                    <Link :href="route('profile.show')"
                        class="px-6 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>