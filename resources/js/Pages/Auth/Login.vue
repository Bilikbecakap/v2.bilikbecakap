<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Log in - Tatakode" />

        <section class="pt-32 pb-16 px-4 min-h-screen flex items-center justify-center">
            <div class="max-w-md w-full">
                <div class="bg-white/5 border border-white/10 backdrop-blur-sm rounded-lg p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-white mb-2">
                            Sign In
                        </h1>
                        <p class="text-white/70">Welcome back to Tatakode</p>
                    </div>

                    <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="email" value="Email" class="text-white/90" />

                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full bg-white/10 border border-white/20 text-white placeholder:text-white/50 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />

                            <InputError class="mt-2 text-red-400" :message="form.errors.email" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Password" class="text-white/90" />

                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full bg-white/10 border border-white/20 text-white placeholder:text-white/50 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />

                            <InputError class="mt-2 text-red-400" :message="form.errors.password" />
                        </div>

                        <div class="mt-4 block">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-white/70">Remember me</span>
                            </label>
                        </div>

                        <div class="mt-4 flex items-center justify-end">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="rounded-md text-sm text-cyan-400 hover:text-cyan-300 underline focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2"
                            >
                                Forgot your password?
                            </Link>

                            <PrimaryButton
                                class="ms-4 bg-gradient-to-r from-cyan-500 to-purple-500 hover:from-cyan-600 hover:to-purple-600"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Log in
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </AppLayout>
</template>