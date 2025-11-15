<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';


const props = defineProps({
    artikel: Object,
    otherArticles: Array,
    kategoriList: Array,
    locale: String,
    komentars: Array,
});

const copyLinkStatus = ref(false);
const { t } = useTranslations();

const form = useForm({
    nama: '',
    kontak: '',
    isi_komentar: '',
    commentable_type: 'App\\Models\\Artikel',
    commentable_id: props.artikel.id,
});

const notification = ref({
    show: false,
    message: '',
    type: 'success',
});

const submitKomentar = () => {
    form.post('/komentar', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('nama', 'kontak', 'isi_komentar');
            showNotification('Komentar berhasil dikirim, admin akan segera membaca pesan kamu.');
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0]?.[0] || 'Gagal mengirim komentar.';
            showNotification(firstError, 'error');
        }
    });
};

const showNotification = (message, type = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const getTitle = computed(() => {
    return props.artikel?.judul || 'Artikel';
});

const getContent = computed(() => {
    return props.artikel?.konten || '';
});

const getCurrentURL = () => {
    return window.location.href;
};

const getShareText = () => {
    return `${getTitle.value} - Bilikbecakap`;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const truncateText = (text, maxWords = 25) => {
    if (!text) return '';
    const plainText = text.replace(/<[^>]*>/g, '');
    const words = plainText.split(' ').filter(word => word.trim().length > 0);
    return words.slice(0, maxWords).join(' ') + (words.length > maxWords ? '...' : '');
};

const getExcerpt = (article) => {
    const konten = article.konten_indonesia || article.konten_melayu || article.konten_english;
    return truncateText(konten, 25);
};

// ✅ Share ke Facebook
const shareToFacebook = () => {
    const url = getCurrentURL();
    const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    window.open(facebookUrl, 'facebook-share', 'width=600,height=400');
};

// ✅ Share ke X (Twitter)
const shareToX = () => {
    const url = getCurrentURL();
    const text = getShareText();
    const xUrl = `https://x.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
    window.open(xUrl, 'x-share', 'width=600,height=400');
};

// ✅ Share ke WhatsApp
const shareToWhatsApp = () => {
    const url = getCurrentURL();
    const text = `${getShareText()} ${url}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
    window.open(whatsappUrl, 'whatsapp-share', 'width=600,height=400');
};

// ✅ Copy Link ke Clipboard
const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(getCurrentURL());
        copyLinkStatus.value = true;
        setTimeout(() => {
            copyLinkStatus.value = false;
        }, 2000);
    } catch (err) {
        console.error('Gagal copy link:', err);
    }
};
</script>

<template>
    <Head :title="`${getTitle} - Bilikbecakap`" />

    <FrontendLayout>
        <section class="py-12 pt-24 min-h-screen relative overflow-hidden">
            <div class="container mx-auto px-6 relative z-20">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-8">
                    <Link href="/artikel" class="text-[#54b0af] hover:underline">{{ t("messages.articles") }}</Link>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-700 truncate">{{ getTitle }}</span>
                </div>

                <!-- Main Content Grid: 2/3 + 1/3 -->
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Left Column: Article Detail (2/3) -->
                    <div class="lg:col-span-2 space-y-2">
                        <!-- Article Header Card -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg overflow-hidden border border-gray-100">
                            <!-- Hero Image -->
                            <div class="relative overflow-hidden h-96 md:h-[500px] bg-gray-200">
                                <img v-if="artikel.gambar_thumbnail" :src="`/storage/${artikel.gambar_thumbnail}`"
                                    :alt="getTitle" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#54b0af]/20 to-[#54b0af]/10">
                                    <svg class="w-24 h-24 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Article Info -->
                            <div class="p-6 md:p-10 space-y-6">
                                <!-- Meta Information -->
                                <div class="flex flex-wrap items-center gap-3 pb-6 border-b border-gray-200">
                                    <!-- Views -->
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-5 h-5 text-[#54b0af]" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd"
                                                d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-gray-700 font-medium">{{ artikel.views_count || 0 }} {{ t('messages.views') }}</span>
                                    </div>

                                    <!-- Date -->
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-5 h-5 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-700 font-medium">{{ formatDate(artikel.tanggal_publish || artikel.created_at) }}</span>
                                    </div>

                                    <!-- Category Badge -->
                                    <div v-if="artikel.kategori" class="inline-block">
                                        <span class="px-4 py-2 bg-[#54b0af]/10 text-[#54b0af] rounded-full text-xs font-bold uppercase tracking-wide">
                                            {{ artikel.kategori.nama_kategori }}
                                        </span>
                                    </div>

                                    <!-- Author Info -->
                                    <div v-if="artikel.creator" class="flex items-center gap-2 text-sm">
                                        <div class="w-8 h-8 bg-[#54b0af]/10 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#54b0af]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <span class="text-gray-700 font-medium">{{ artikel.creator.name }}</span>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h1 class="text-4xl md:text-5xl font-bold text-[#002b44] leading-tight">
                                    {{ getTitle }}
                                </h1>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg border border-gray-100 p-6 md:p-10">
                            <div class="prose prose-lg max-w-none dark:prose-invert"
                                v-html="getContent">
                            </div>
                        </div>

                        <!-- Meta Keywords -->
                        <div v-if="artikel.meta_keywords" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h3 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                {{ t('messages.keywords') }}
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="(keyword, i) in artikel.meta_keywords.split(',')" :key="i"
                                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                    {{ keyword.trim() }}
                                </span>
                            </div>
                        </div>

                        <!-- Share & Navigation -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg border border-gray-100 p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                                <!-- Share Section -->
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-semibold text-gray-700">{{ t('messages.bagikan artikel') }}:</span>
                                    <div class="flex items-center gap-2">
                                        <!-- Facebook Share -->
                                        <button @click="shareToFacebook"
                                            class="p-2.5 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600 transition-all duration-300 hover:scale-110"
                                            title="Bagikan ke Facebook">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </button>

                                        <!-- X (Twitter) Share -->
                                        <button @click="shareToX"
                                            class="p-2.5 bg-black/5 hover:bg-black/10 rounded-lg text-black transition-all duration-300 hover:scale-110"
                                            title="Bagikan ke X (Twitter)">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.514l-5.106-6.694L2.306 21.75H-1v-3.308l7.227-8.26L-1.667 2.25h6.514l5.106 6.694 6.104-6.694zM17.5 19.25h1.828L6.362 4.25H4.5l13 15z"/>
                                            </svg>
                                        </button>

                                        <!-- WhatsApp Share -->
                                        <button @click="shareToWhatsApp"
                                            class="p-2.5 bg-green-50 hover:bg-green-100 rounded-lg text-green-600 transition-all duration-300 hover:scale-110"
                                            title="Bagikan ke WhatsApp">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.958 1.37l-.355.214-3.66-.96.978 3.59-.235.374a9.861 9.861 0 1513.815-4.794" />
                                            </svg>
                                        </button>

                                        <!-- Copy Link -->
                                        <button @click="copyToClipboard"
                                            :class="[
                                                'p-2.5 rounded-lg transition-all duration-300 hover:scale-110',
                                                copyLinkStatus 
                                                    ? 'bg-green-50 text-green-600' 
                                                    : 'bg-gray-100 hover:bg-gray-200 text-gray-600'
                                            ]"
                                            :title="copyLinkStatus ? 'Link Tersalin!' : 'Salin Link'">
                                            <svg v-if="!copyLinkStatus" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Back to Articles Link -->
                                <Link href="/artikel" class="inline-flex items-center gap-2 text-[#54b0af] hover:text-[#459a99] font-semibold transition-colors whitespace-nowrap">
                                    ← {{ t('messages.kembali ke artikel') }}
                                </Link>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-[#002b44] mb-4">{{ t('messages.komentar') }}</h3>

                            <!-- Form Komentar -->
                            <form @submit.prevent="submitKomentar" class="space-y-3 mb-5">
                               <input v-model="form.nama" type="text" :placeholder="t('messages.your_name')"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af]"
                                    required />
                                <input v-model="form.kontak" type="text" :placeholder="t('messages.email_or_contact')"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af]" />
                                <textarea v-model="form.isi_komentar" :placeholder="t('messages.write_comment')" rows="3"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#54b0af]"
                                    required></textarea>
                                <button type="submit" :disabled="form.processing"
                                    class="w-full bg-[#54b0af] hover:bg-[#459a99] text-white py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-70">
                                    {{ form.processing ? t('messages.sending') : t('messages.send_comment') }}
                                </button>
                            </form>

                            <!-- Daftar Komentar -->
                            <div v-if="props.komentars?.length" class="space-y-4 max-h-80 overflow-y-auto pr-1">
                                <div v-for="komentar in props.komentars" :key="komentar.id"
                                    class="bg-gray-50/60 dark:bg-slate-100/10 rounded-xl p-3 border border-gray-200/50 dark:border-slate-700/50">
                                    <div class="flex items-start gap-3">
                                        <!-- Avatar Placeholder -->
                                        <div
                                            class="flex-shrink-0 w-8 h-8 rounded-full bg-[#54b0af]/10 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-baseline justify-between gap-2">
                                                <h4 class="font-semibold text-[#002b44] text-sm">{{ komentar.nama }}
                                                </h4>
                                                <time class="text-xs text-gray-500 whitespace-nowrap">
                                                    {{ new Date(komentar.created_at).toLocaleDateString('id-ID', {
                                                        day: 'numeric',
                                                    month: 'short',
                                                    year: 'numeric'
                                                    }) }}
                                                </time>
                                            </div>
                                            <p class="text-gray-700 text-sm mt-1 leading-relaxed">
                                                {{ komentar.isi_komentar }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-gray-500 text-sm italic">
                                {{ t('messages.belum ada komentar') }}.
                            </div>
                        </div>

                        <!-- Notifikasi -->
                        <div v-if="notification.show">
                            <div :class="[
                                'px-4 py-3 rounded-lg text-sm font-medium text-white shadow-md',
                                notification.type === 'success'
                                    ? 'bg-[#54b0af]'
                                    : 'bg-red-500'
                            ]">
                                {{ notification.message }}
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar (1/3) -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Popular Feeds Section -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-[#002b44] mb-6 flex items-center gap-2">
                                <span class="text-[#FCB415]">━━</span> Popular Feeds
                            </h3>
                            <div class="space-y-4">
                                <Link v-for="item in otherArticles?.slice(0, 5)" :key="item.id"
                                    :href="`/artikel/${item.slug}`"
                                    class="flex gap-3 pb-4 border-b border-gray-100 last:border-b-0 hover:opacity-80 transition-opacity group">
                                <!-- Thumbnail -->
                                <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                    <img v-if="item.gambar_thumbnail" :src="`/storage/${item.gambar_thumbnail}`"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="font-semibold text-[#002b44] text-sm line-clamp-2 group-hover:text-[#54b0af]">
                                        {{ item.judul }}
                                    </h4>
                                    <p class="text-xs text-[#002b44] mt-2 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-[#54b0af]" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        {{ new Date(item.tanggal_publish || item.created_at).toLocaleDateString('id-ID',
                                            {
                                                day: 'numeric',
                                                month: 'short', year: 'numeric'
                                            }) }}
                                    </p>
                                </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Categories Section -->
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-[#002b44] mb-4 flex items-center gap-2">
                                <span class="text-[#FCB415]">━━</span> Kategori
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <!-- All Categories Badge -->
                                <Link href="/artikel"
                                    class="px-3 py-2 bg-[#54b0af]/10 text-[#54b0af] hover:bg-[#54b0af] hover:text-white rounded-full text-xs font-semibold uppercase tracking-wide transition-all">
                                    Semua
                                </Link>
                                
                                <!-- Individual Categories Badges -->
                                <Link v-for="cat in kategoriList" :key="cat.id"
                                    :href="`/artikel?kategori=${cat.id}`"
                                    :class="[
                                        'px-3 py-2 rounded-full text-xs font-semibold uppercase tracking-wide transition-all',
                                        artikel.kategori?.id === cat.id 
                                            ? 'bg-[#54b0af] text-white' 
                                            : 'bg-gray-100 text-[#002b44] hover:bg-[#54b0af]/10 hover:text-[#54b0af]'
                                    ]">
                                    {{ cat.nama_kategori }}
                                </Link>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="bg-gradient-to-br from-[#54b0af] to-[#459a99] rounded-lg p-6 text-white space-y-3">
                            <h3 class="font-bold text-lg">Ingin Bergabung?</h3>
                            <p class="text-sm opacity-90">Jadilah bagian dari komunitas Bilikbecakap dan lestarikan bahasa Melayu Belitung bersama kami.</p>
                            <a href="/" class="inline-block w-full text-center bg-white text-[#54b0af] font-semibold py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                Pelajari Lebih Lanjut
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontendLayout>
</template>

<style scoped>
/* Prose styling untuk konten artikel */
:deep(.prose) {
    color: #002b44;
    line-height: 1.8;
}

:deep(.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6) {
    color: #002b44;
    font-weight: 700;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
}

:deep(.prose h2) {
    font-size: 1.875rem;
    border-bottom: 2px solid #54b0af;
    padding-bottom: 0.5rem;
}

:deep(.prose h3) {
    font-size: 1.5rem;
}

:deep(.prose p) {
    margin-bottom: 1rem;
}

:deep(.prose a) {
    color: #54b0af;
    text-decoration: underline;
}

:deep(.prose a:hover) {
    color: #459a99;
}

:deep(.prose ul, .prose ol) {
    margin-left: 2rem;
    margin-bottom: 1rem;
}

:deep(.prose li) {
    margin-bottom: 0.5rem;
}

:deep(.prose blockquote) {
    border-left: 4px solid #54b0af;
    padding-left: 1rem;
    font-style: italic;
    color: #666;
    margin: 1.5rem 0;
}

:deep(.prose code) {
    background: #f0f0f0;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    color: #d63384;
}

:deep(.prose pre) {
    background: #1e293b;
    color: #e2e8f0;
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1rem 0;
}

:deep(.prose table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

:deep(.prose table th) {
    background: #54b0af;
    color: white;
    padding: 0.75rem;
    text-align: left;
}

:deep(.prose table td) {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
}

:deep(.prose img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
}

/* Smooth transitions */
:deep(button) {
    transition: all 0.3s ease;
}
</style>