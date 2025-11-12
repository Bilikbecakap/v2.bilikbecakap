<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const form = useForm({
    nama: '',
    email: '',
    nomor_telepon: '',
    subjek: '',
    pesan: '',
});

const showSuccess = ref(false);
const { t } = useTranslations();

const submit = () => {
    form.post(route('kontak.store'), {
        onSuccess: () => {
            form.reset();
            showSuccess.value = true;
            // Hilangkan alert setelah 5 detik
            setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        }
    });
};
</script>

<template>

    <Head title="Kontak Kami" />

    <FrontendLayout>


        <!-- Hero Section -->
        <section
            class="relative bg-gradient-to-br from-[#54b0af]/10 via-white to-[#002b44]/5 py-16 overflow-hidden min-h-[300px] md:min-h-[400px] flex items-center justify-center">
            <!-- Background -->
            <div class="absolute inset-0 z-0">
                <img src="/background/breadcrumb.jpg" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[rgba(252,228,179,0.2)]"></div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute -top-10 right-0 w-96 h-96 bg-[#54b0af]/20 rounded-full blur-3xl z-10"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl z-10"></div>

            <!-- Content -->
            <div class="max-w-4xl mx-auto text-center space-y-4 relative z-10">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-[#002b44] leading-tight">
                    {{ t('messages.hubungi kami') }}
                </h1>
                <p class="text-lg text-gray-700 max-w-2xl mx-auto">
                    {{ t('messages.hubungi kami deskripsi') }}
                </p>
            </div>

            <!-- Wave Divider -->
            <div class="absolute bottom-0 left-0 right-0 overflow-hidden leading-[0]">
                <svg class="relative block w-full h-[120px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120"
                    preserveAspectRatio="none">
                    <path d="M0,64 C360,160 1080,0 1440,96 L1440,120 L0,120 Z" fill="#F9FAFB"></path>
                </svg>
            </div>
        </section>
        <!-- Main Section -->
        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                    <!-- Left Side - Contact Info -->
                    <div class="space-y-8">
                        <!-- Header -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-2">
                                <span class="text-4xl">🔔</span>
                                <span class="text-[#54b0af] font-semibold text-lg">{{ t('messages.hubungi kami') }}</span>
                            </div>
                            <h1 class="text-4xl md:text-5xl font-bold text-[#002b44] leading-tight">
                                {{ t('messages.jangan ragu') }}
                            </h1>
                            <p class="text-gray-600 text-lg leading-relaxed">
                                {{ t('messages.jangan ragu deskripsi') }}
                            </p>
                        </div>

                        <!-- Contact Details -->
                        <div class="space-y-6">
                            <!-- Email -->
                            <div>
                                <a href="mailto:kbkmsenyubuk@gmail.com"
                                    class="text-[#002b44] font-semibold text-lg hover:text-[#54b0af] transition underline">
                                    kbkmsenyubuk@gmail.com
                                </a>
                            </div>

                            <!-- Address -->
                            <div class="text-gray-700 text-lg">
                                Senyubuk, Belitung Timur, Indonesia
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Form -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                        <h2 class="text-3xl font-bold text-[#002b44] mb-8">Send Us Message</h2>

                        <!-- Success Alert -->
                        <div v-if="showSuccess"
                            class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg animate-fade-in">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-green-800 font-medium">Pesan berhasil dikirim!</p>
                                    <p class="text-green-700 text-sm mt-1">Terima kasih telah menghubungi kami. Kami
                                        akan segera merespons pesan Anda.</p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Row 1: Nama & Telepon -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nama -->
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">
                                        Full Name
                                    </label>
                                    <input v-model="form.nama" type="text" placeholder="Your name"
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:bg-white transition"
                                        :class="{ 'border-red-500': form.errors.nama }">
                                    <p v-if="form.errors.nama" class="mt-1 text-sm text-red-600">{{ form.errors.nama }}
                                    </p>
                                </div>

                                <!-- Telepon -->
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">
                                        Phone
                                    </label>
                                    <input v-model="form.nomor_telepon" type="tel" placeholder="+62 812 3456 7890"
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:bg-white transition"
                                        :class="{ 'border-red-500': form.errors.nomor_telepon }">
                                    <p v-if="form.errors.nomor_telepon" class="mt-1 text-sm text-red-600">{{
                                        form.errors.nomor_telepon }}</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">
                                    Your Email
                                </label>
                                <input v-model="form.email" type="email" placeholder="your@email.com"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:bg-white transition"
                                    :class="{ 'border-red-500': form.errors.email }">
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Subjek -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">
                                    Subject
                                </label>
                                <input v-model="form.subjek" type="text" placeholder="Subject"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:bg-white transition"
                                    :class="{ 'border-red-500': form.errors.subjek }">
                                <p v-if="form.errors.subjek" class="mt-1 text-sm text-red-600">{{ form.errors.subjek }}
                                </p>
                            </div>

                            <!-- Pesan -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">
                                    Comments
                                </label>
                                <textarea v-model="form.pesan" placeholder="Your message here..." rows="5"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af] focus:bg-white transition resize-none"
                                    :class="{ 'border-red-500': form.errors.pesan }"></textarea>
                                <p v-if="form.errors.pesan" class="mt-1 text-sm text-red-600">{{ form.errors.pesan }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <button type="submit" :disabled="form.processing"
                                    class="w-full bg-[#54b0af] hover:bg-[#3d8b8a] text-white font-bold py-4 rounded-full transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed text-lg">
                                    {{ form.processing ? 'Sending...' : 'Send a Message' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-in;
}
</style>