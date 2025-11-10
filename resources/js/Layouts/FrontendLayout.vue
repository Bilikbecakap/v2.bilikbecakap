<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();
const page = usePage();

// Ambil locale dari props
const currentLocale = computed(() => page.props.locale || 'id');

// Deteksi halaman aktif
const currentPath = computed(() => page.url);

// Daftar route yang ingin diberi active state
const isActive = (path) => {
    if (path === '/') return currentPath.value === '/';
    return currentPath.value.startsWith(path);
};

const showLainnyaDropdown = ref(false);
const showMobileMenu = ref(false);
const showLanguageDropdown = ref(false);
const isScrolled = ref(false);

const languages = {
    id: { name: "Bahasa Indonesia", short: "IDN" },
    en: { name: "English", short: "ENG" },
    mb: { name: "Melayu Belitung", short: "MYB" },
};

const switchLanguage = (newLocale) => {
    showLanguageDropdown.value = false;
    router.visit(`/language/${newLocale}`, {
        preserveState: false,
        preserveScroll: true,
    });
};

const toggleLanguageDropdown = () => {
    showLanguageDropdown.value = !showLanguageDropdown.value;
};

// Handle scroll untuk navbar
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Header -->
        <header :class="[
            'fixed top-0 left-0 right-0 z-50 transition-all duration-300 border-b border-gray-200',
            isScrolled ? 'bg-white shadow-md' : 'bg-white'
        ]">
            <nav class="container mx-auto px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center gap-3">
                        <img src="/logo/bilikbecakap.png" alt="Bilik Bercakap"
                            class="h-10 sm:h-12 w-auto object-contain">
                        </Link>
                    </div>

                    <!-- Desktop Menu: Hanya tampil di lg (≥1024px) -->
                    <!-- Desktop Menu: Hanya tampil di lg (≥1024px) -->
                    <div class="hidden lg:flex items-center space-x-6 absolute left-1/2 transform -translate-x-1/2">
                        <Link href="/" :class="[
                            'font-medium transition-colors duration-200',
                            isActive('/') ? 'text-[#54b0af] font-bold' : 'text-gray-700 hover:text-[#54b0af]'
                        ]">
                        {{ t("messages.home") }}
                        </Link>
                        <Link href="/kamus" :class="[
                            'font-medium transition-colors duration-200',
                            isActive('/kamus') ? 'text-[#54b0af] font-bold' : 'text-gray-700 hover:text-[#54b0af]'
                        ]">
                        Kamus
                        </Link>
                        <Link href="/penerjemah" :class="[
                            'font-medium transition-colors duration-200',
                            isActive('/penerjemah') ? 'text-[#54b0af] font-bold' : 'text-gray-700 hover:text-[#54b0af]'
                        ]">
                        Penerjemah
                        </Link>
                        <Link href="/pembelajaran" :class="[
                            'font-medium transition-colors duration-200',
                            isActive('/pembelajaran') ? 'text-[#54b0af] font-bold' : 'text-gray-700 hover:text-[#54b0af]'
                        ]">
                        Pembelajaran
                        </Link>
                        <Link href="/artikel" :class="[
                            'font-medium transition-colors duration-200',
                            isActive('/artikel') ? 'text-[#54b0af] font-bold' : 'text-gray-700 hover:text-[#54b0af]'
                        ]">
                        Artikel
                        </Link>

                        <!-- Dropdown Lainnya (Desktop) -->
                        <div class="relative" @mouseenter="showLainnyaDropdown = true"
                            @mouseleave="showLainnyaDropdown = false">
                            <button
                                class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200 flex items-center gap-1">
                                Lainnya
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <transition enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                <div v-show="showLainnyaDropdown"
                                    class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2">
                                    <Link href="/kuis" :class="[
                                        'block px-4 py-2 text-sm transition-colors duration-200',
                                        isActive('/quiz') ? 'bg-[#54b0af] text-white font-medium' : 'text-gray-700 hover:bg-[#54b0af] hover:text-white'
                                    ]">
                                    Quiz
                                    </Link>
                                    <Link href="/tentang" :class="[
                                        'block px-4 py-2 text-sm transition-colors duration-200',
                                        isActive('/tentang') ? 'bg-[#54b0af] text-white font-medium' : 'text-gray-700 hover:bg-[#54b0af] hover:text-white'
                                    ]">
                                    Tentang
                                    </Link>
                                    <Link href="/kontak" :class="[
                                        'block px-4 py-2 text-sm transition-colors duration-200',
                                        isActive('/kontak') ? 'bg-[#54b0af] text-white font-medium' : 'text-gray-700 hover:bg-[#54b0af] hover:text-white'
                                    ]">
                                    Kontak
                                    </Link>
                                    <Link href="/galeri" :class="[
                                        'block px-4 py-2 text-sm transition-colors duration-200',
                                        isActive('/galeri') ? 'bg-[#54b0af] text-white font-medium' : 'text-gray-700 hover:bg-[#54b0af] hover:text-white'
                                    ]">
                                    Galeri
                                    </Link>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- Right Side: Auth + Language (Desktop - lg+) -->
                    <div class="hidden lg:flex items-center gap-3">
                        <!-- Auth -->
                        <Link v-if="!$page.props.auth?.user" :href="route('login')"
                            class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-white text-[#54b0af] border-2 border-[#54b0af] hover:bg-[#54b0af] hover:text-white">
                        Masuk
                        </Link>
                        <Link v-else :href="route('dashboard')"
                            class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-[#54b0af] text-white hover:bg-[#459a99] flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Dashboard
                        </Link>

                        <!-- Language -->
                        <div class="relative">
                            <button @click="toggleLanguageDropdown"
                                class="px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-[#54b0af] text-white hover:bg-[#459a99] border-2 border-[#54b0af]">
                                {{ languages[currentLocale].short }}
                            </button>

                            <transition enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                <div v-show="showLanguageDropdown" @click.stop
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2">
                                    <button v-for="(lang, code) in languages" :key="code" @click="switchLanguage(code)"
                                        :class="[
                                            'flex items-center justify-between w-full px-3 py-2 text-sm transition-colors duration-200',
                                            currentLocale === code ? 'bg-[#54b0af] text-white' : 'text-gray-700 hover:bg-gray-50'
                                        ]">
                                        <span>{{ lang.short }}</span>
                                        <svg v-if="currentLocale === code" class="w-4 h-4" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- Mobile: Hamburger + Language (tampil di < lg) -->
                    <div class="flex lg:hidden items-center gap-2">
                        <!-- Language (Mobile) -->
                        <div class="relative">
                            <button @click="toggleLanguageDropdown"
                                class="px-2.5 py-1.5 rounded-lg font-medium text-xs transition-all duration-200 bg-[#54b0af] text-white hover:bg-[#459a99]">
                                {{ languages[currentLocale].short }}
                            </button>

                            <transition enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                <div v-show="showLanguageDropdown" @click.stop
                                    class="absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-lg border border-gray-100 py-2 z-[70]">
                                    <button v-for="(lang, code) in languages" :key="code" @click="switchLanguage(code)"
                                        :class="[
                                            'flex items-center justify-between w-full px-3 py-2 text-sm transition-colors duration-200',
                                            currentLocale === code ? 'bg-[#54b0af] text-white' : 'text-gray-700 hover:bg-gray-50'
                                        ]">
                                        <span>{{ lang.short }}</span>
                                        <svg v-if="currentLocale === code" class="w-4 h-4" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </transition>
                        </div>

                        <!-- Hamburger -->
                        <button @click="showMobileMenu = !showMobileMenu"
                            class="text-gray-700 transition-colors duration-200 p-1">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Mobile Menu Sidebar -->
        <transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-x-full"
            enter-to-class="translate-x-0" leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-x-0" leave-to-class="translate-x-full">
            <div v-show="showMobileMenu"
                class="fixed top-0 right-0 w-64 h-full bg-white shadow-2xl z-[60] lg:hidden overflow-y-auto">
                <div class="flex justify-end p-4 border-b border-gray-100">
                    <button @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-6 flex flex-col space-y-4">
                    <!-- Auth Buttons (Mobile) -->
                    <div class="pb-4 border-b border-gray-200">
                        <Link v-if="!$page.props.auth?.user" :href="route('login')" @click="showMobileMenu = false"
                            class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 bg-[#54b0af] text-white hover:bg-[#459a99]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Masuk
                        </Link>
                        <Link v-else :href="route('dashboard')" @click="showMobileMenu = false"
                            class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg font-medium text-sm transition-all duration-200 bg-[#54b0af] text-white hover:bg-[#459a99]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Dashboard
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <Link href="/" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    {{ t("messages.home") }}
                    </Link>
                    <Link href="/kamus" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Kamus
                    </Link>
                    <Link href="/penerjemah" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Penerjemah
                    </Link>
                    <Link href="/pembelajaran" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Pembelajaran
                    </Link>
                    <Link href="/artikel" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Artikel
                    </Link>

                    <div class="border-t border-gray-200 my-2"></div>

                    <Link href="/kuis" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Quiz
                    </Link>
                    <Link href="/tentang" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Tentang
                    </Link>
                    <link href="/kontak" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Kontak
                    </link>
                    <Link href="/galeri" @click="showMobileMenu = false"
                        class="text-gray-700 hover:text-[#54b0af] font-medium transition-colors duration-200">
                    Galeri
                    </Link>
                </div>
            </div>
        </transition>

        <!-- Overlay -->
        <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-show="showMobileMenu" @click="showMobileMenu = false"
                class="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"></div>
        </transition>

        <!-- Main Content -->
        <main class="pt-16">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-[#002b44] text-white pt-16 pb-8">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                    <!-- Kolom 1: Logo & Deskripsi -->
                    <div>
                        <img src="/logo/bilikbecakap-putih.png" alt="Bilik Bercakap"
                            class="h-16 w-auto object-contain mb-4">
                        <p class="text-gray-300 text-sm leading-relaxed mb-6">
                            Platform pelestarian bahasa daerah Melayu Belitung dengan berbagai fitur sebagai
                            kebudayaan daerah.
                        </p>
                        <!-- Social Media -->
                        <div class="flex items-center gap-4">
                            <a href="#"
                                class="w-10 h-10 bg-[#54b0af] rounded-full flex items-center justify-center hover:bg-[#459a99] transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="#"
                                class="w-10 h-10 bg-[#54b0af] rounded-full flex items-center justify-center hover:bg-[#459a99] transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                            <a href="#"
                                class="w-10 h-10 bg-[#54b0af] rounded-full flex items-center justify-center hover:bg-[#459a99] transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                </svg>
                            </a>
                            <a href="#"
                                class="w-10 h-10 bg-[#54b0af] rounded-full flex items-center justify-center hover:bg-[#459a99] transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Kolom 2: Fitur -->
                    <div>
                        <h3 class="text-xl font-semibold mb-6">Fitur</h3>
                        <ul class="space-y-3">
                            <li>
                                <a href="/kamus"
                                    class="text-gray-300 hover:text-[#54b0af] transition-colors duration-200 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    Kamus Digital
                                </a>
                            </li>
                            <li>
                                <a href="/pembelajaran"
                                    class="text-gray-300 hover:text-[#54b0af] transition-colors duration-200 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    Pembelajaran
                                </a>
                            </li>
                            <li>
                                <a href="/penerjemah"
                                    class="text-gray-300 hover:text-[#54b0af] transition-colors duration-200 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    Penerjemah
                                </a>
                            </li>
                            <li>
                                <a href="/artikel"
                                    class="text-gray-300 hover:text-[#54b0af] transition-colors duration-200 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    Artikel
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Kolom 3: Get in Touch -->
                    <div>
                        <h3 class="text-xl font-semibold mb-6">Get in Touch</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-[#54b0af] mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-gray-300 text-sm">
                                    Senyubuk, Belitung Timur,<br>Indonesia
                                </span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-[#54b0af] mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <a href="mailto:kbkmsenyubuk@gmail.com"
                                    class="text-gray-300 text-sm hover:text-[#54b0af] transition-colors duration-200">
                                    kbkmsenyubuk@gmail.com
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Kolom 4: Support By -->
                    <div>
                        <h3 class="text-xl font-semibold mb-6">Support By</h3>
                        <div class="flex items-center gap-4">
                            <img src="/logo/kemendikbud.png" alt="Support A" class="h-16 w-auto object-contain">
                            <img src="/logo/kemahbudaya.png" alt="Support B" class="h-16 w-auto object-contain">
                            <img src="/logo/beltim.png" alt="Support C" class="h-16 w-auto object-contain">
                        </div>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="border-t border-gray-700 pt-6 mt-8">
                    <p class="text-center text-gray-400 text-sm">
                        © {{ new Date().getFullYear() }} Bilik Bercakap. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #54b0af;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #459a99;
}

/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: #54b0af #f1f1f1;
}
</style>