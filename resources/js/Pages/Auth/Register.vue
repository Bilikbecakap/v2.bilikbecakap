<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Daftar - Bilikbecakap" />

    <FrontendLayout>
        <section class="py-20 pt-32 min-h-screen flex items-center justify-center relative overflow-hidden">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/background/laut-pantai.png" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <div class="container mx-auto px-6 relative z-20">
                <div class="max-w-md mx-auto">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <h1 class="text-4xl font-bold text-[#002b44] mb-2 drop-shadow-lg">
                            Buat Akun Baru
                        </h1>
                        <p class="text-[#002b44]/80">Bergabunglah dengan komunitas Bilikbecakap</p>
                    </div>

                    <!-- Register Card -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name Field -->
                            <div>
                                <InputLabel for="name" value="Nama Lengkap" class="text-[#002b44] font-semibold mb-2" />
                                <TextInput 
                                    id="name" 
                                    type="text"
                                    class="mt-1 block w-full bg-white border-2 border-gray-200 text-[#002b44] placeholder:text-gray-400 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent transition-all duration-200"
                                    v-model="form.name" 
                                    required 
                                    autofocus 
                                    autocomplete="name"
                                    placeholder="Masukkan nama lengkap" 
                                />
                                <InputError class="mt-2 text-red-500" :message="form.errors.name" />
                            </div>

                            <!-- Email Field -->
                            <div>
                                <InputLabel for="email" value="Email" class="text-[#002b44] font-semibold mb-2" />
                                <TextInput 
                                    id="email" 
                                    type="email"
                                    class="mt-1 block w-full bg-white border-2 border-gray-200 text-[#002b44] placeholder:text-gray-400 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent transition-all duration-200"
                                    v-model="form.email" 
                                    required 
                                    autocomplete="username"
                                    placeholder="nama@email.com" 
                                />
                                <InputError class="mt-2 text-red-500" :message="form.errors.email" />
                            </div>

                            <!-- Password Field -->
                            <div>
                                <InputLabel for="password" value="Password" class="text-[#002b44] font-semibold mb-2" />
                                <div class="relative">
                                    <TextInput 
                                        id="password" 
                                        :type="showPassword ? 'text' : 'password'"
                                        class="mt-1 block w-full bg-white border-2 border-gray-200 text-[#002b44] placeholder:text-gray-400 px-4 py-3 pr-12 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent transition-all duration-200"
                                        v-model="form.password" 
                                        required 
                                        autocomplete="new-password"
                                        placeholder="Minimal 8 karakter" 
                                    />
                                    <button 
                                        type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-[#54b0af] transition-colors"
                                    >
                                        <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <InputError class="mt-2 text-red-500" :message="form.errors.password" />
                            </div>

                            <!-- Password Confirmation Field -->
                            <div>
                                <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-[#002b44] font-semibold mb-2" />
                                <div class="relative">
                                    <TextInput 
                                        id="password_confirmation" 
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        class="mt-1 block w-full bg-white border-2 border-gray-200 text-[#002b44] placeholder:text-gray-400 px-4 py-3 pr-12 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:border-transparent transition-all duration-200"
                                        v-model="form.password_confirmation" 
                                        required 
                                        autocomplete="new-password"
                                        placeholder="Ulangi password" 
                                    />
                                    <button 
                                        type="button" 
                                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-[#54b0af] transition-colors"
                                    >
                                        <svg v-if="!showPasswordConfirmation" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <InputError class="mt-2 text-red-500" :message="form.errors.password_confirmation" />
                            </div>

                            <!-- Submit Button -->
                            <div class="space-y-4">
                                <button 
                                    type="submit"
                                    class="w-full bg-[#54b0af] hover:bg-[#459a99] text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
                                    :disabled="form.processing"
                                >
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ form.processing ? 'Mendaftar...' : 'Daftar Sekarang' }}</span>
                                </button>
                            </div>
                        </form>

                        <!-- Divider -->
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white text-gray-500">atau</span>
                            </div>
                        </div>

                        <!-- Link to Login -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600">
                                Sudah punya akun?
                                <Link 
                                    href="/login" 
                                    class="text-[#54b0af] hover:text-[#459a99] font-semibold transition-colors"
                                >
                                    Masuk di sini
                                </Link>
                            </p>
                        </div>

                        <!-- Back to Home -->
                        <div class="text-center mt-4">
                            <Link 
                                href="/"
                                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-[#54b0af] font-medium transition-colors duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Kembali ke Beranda
                            </Link>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-8 text-center">
                        <p class="text-sm text-[#002b44] drop-shadow">
                            Dengan mendaftar, Anda menyetujui
                            <a href="#" class="text-[#002b44] font-semibold hover:text-[#54b0af] transition-colors">Syarat & Ketentuan</a>
                            dan
                            <a href="#" class="text-[#002b44] font-semibold hover:text-[#54b0af] transition-colors">Kebijakan Privasi</a>
                            kami
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
input:focus {
    box-shadow: 0 0 0 3px rgba(84, 176, 175, 0.1);
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>