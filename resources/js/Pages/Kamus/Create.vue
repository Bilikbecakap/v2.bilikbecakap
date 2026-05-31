<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onUnmounted } from 'vue';

const form = useForm({
    kata: '',
    pos: '',
    definisi: '',
    catatan_validasi: '',
    audio: null,
    contoh: [],
});

const posOptions = [
    { value: '',    label: '— Pilih kelas kata (opsional) —' },
    { value: 'a',    label: 'Adjektiva — kata sifat' },
    { value: 'adv',  label: 'Adverbia — kata keterangan' },
    { value: 'n',    label: 'Nomina — kata benda' },
    { value: 'num',  label: 'Numeralia — kata bilangan' },
    { value: 'p',    label: 'Partikel' },
    { value: 'pron', label: 'Pronomina — kata ganti' },
    { value: 'v',    label: 'Verba — kata kerja' },
];

// --- Contoh kalimat ---
const addContoh = () => {
    form.contoh.push({ kalimat: '', arti: '' });
};

const removeContoh = (index) => {
    form.contoh.splice(index, 1);
};

// --- Audio recording states ---
const isRecording = ref(false);
const isPaused = ref(false);
const recordingTime = ref(0);
const audioBlob = ref(null);
const audioUrl = ref(null);
const mediaRecorder = ref(null);
const recordingTimer = ref(null);
const audioChunks = ref([]);

const uploadMethod = ref('upload');
const audioFileName = ref('');

const formattedTime = computed(() => {
    const minutes = Math.floor(recordingTime.value / 60);
    const seconds = recordingTime.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

const startRecording = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        let options = {};
        if (MediaRecorder.isTypeSupported('audio/mp4')) {
            options = { mimeType: 'audio/mp4' };
        } else if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
            options = { mimeType: 'audio/webm;codecs=opus' };
        } else if (MediaRecorder.isTypeSupported('audio/wav')) {
            options = { mimeType: 'audio/wav' };
        }
        mediaRecorder.value = new MediaRecorder(stream, options);
        audioChunks.value = [];
        mediaRecorder.value.ondataavailable = (event) => { audioChunks.value.push(event.data); };
        mediaRecorder.value.onstop = () => {
            const mimeType = MediaRecorder.isTypeSupported('audio/mp4') ? 'audio/mp4' : 'audio/wav';
            const extension = mimeType === 'audio/mp4' ? '.m4a' : '.wav';
            audioBlob.value = new Blob(audioChunks.value, { type: mimeType });
            audioUrl.value = URL.createObjectURL(audioBlob.value);
            const file = new File([audioBlob.value], `recording_${Date.now()}${extension}`, { type: mimeType });
            form.audio = file;
            audioFileName.value = file.name;
            stream.getTracks().forEach(track => track.stop());
        };
        mediaRecorder.value.start();
        isRecording.value = true;
        recordingTime.value = 0;
        recordingTimer.value = setInterval(() => { recordingTime.value++; }, 1000);
    } catch (error) {
        alert('Gagal mengakses mikrofon. Pastikan Anda memberikan izin akses mikrofon.');
    }
};

const pauseRecording = () => {
    if (mediaRecorder.value?.state === 'recording') {
        mediaRecorder.value.pause();
        isPaused.value = true;
        clearInterval(recordingTimer.value);
    }
};

const resumeRecording = () => {
    if (mediaRecorder.value?.state === 'paused') {
        mediaRecorder.value.resume();
        isPaused.value = false;
        recordingTimer.value = setInterval(() => { recordingTime.value++; }, 1000);
    }
};

const stopRecording = () => {
    if (mediaRecorder.value && ['recording', 'paused'].includes(mediaRecorder.value.state)) {
        mediaRecorder.value.stop();
        isRecording.value = false;
        isPaused.value = false;
        clearInterval(recordingTimer.value);
    }
};

const clearRecording = () => {
    if (audioUrl.value) URL.revokeObjectURL(audioUrl.value);
    audioUrl.value = null;
    audioBlob.value = null;
    form.audio = null;
    audioFileName.value = '';
    recordingTime.value = 0;
    if (isRecording.value) stopRecording();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const allowedMimeTypes = ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/mpeg', 'audio/mp4', 'audio/x-wav', 'audio/wave', 'audio/m4a'];
    const allowedExtensions = ['.mp3', '.wav', '.ogg', '.m4a', '.mp4'];
    const ext = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
    if (!allowedMimeTypes.includes(file.type) && !allowedExtensions.includes(ext)) {
        alert('Format file tidak didukung. Gunakan MP3, WAV, OGG, atau M4A.');
        event.target.value = '';
        return;
    }
    if (file.size > 10240 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 10MB.');
        event.target.value = '';
        return;
    }
    let processedFile = file;
    if (ext === '.mp3' && !file.type.includes('mp3')) processedFile = new File([file], file.name, { type: 'audio/mpeg' });
    else if (ext === '.wav' && !file.type.includes('wav')) processedFile = new File([file], file.name, { type: 'audio/wav' });
    else if (ext === '.ogg' && !file.type.includes('ogg')) processedFile = new File([file], file.name, { type: 'audio/ogg' });
    else if (ext === '.m4a' && !file.type.includes('m4a')) processedFile = new File([file], file.name, { type: 'audio/mp4' });
    form.audio = processedFile;
    audioFileName.value = processedFile.name;
    if (audioUrl.value) { URL.revokeObjectURL(audioUrl.value); audioUrl.value = null; audioBlob.value = null; }
};

const playAudio = () => {
    const src = audioUrl.value || (form.audio instanceof File ? URL.createObjectURL(form.audio) : null);
    if (!src) return;
    const audio = new Audio(src);
    audio.play().catch(() => {});
    if (!audioUrl.value) audio.onended = () => URL.revokeObjectURL(src);
};

const removeAudio = () => {
    form.audio = null;
    audioFileName.value = '';
    if (audioUrl.value) { URL.revokeObjectURL(audioUrl.value); audioUrl.value = null; audioBlob.value = null; }
    const fileInput = document.getElementById('audio-upload');
    if (fileInput) fileInput.value = '';
};

const switchUploadMethod = (method) => {
    uploadMethod.value = method;
    if (method === 'upload') clearRecording();
    else removeAudio();
};

const submit = () => {
    form.post(route('kamus.store'), {
        forceFormData: true,
        onSuccess: () => { if (audioUrl.value) URL.revokeObjectURL(audioUrl.value); }
    });
};

onUnmounted(() => {
    clearInterval(recordingTimer.value);
    if (audioUrl.value) URL.revokeObjectURL(audioUrl.value);
});
</script>

<template>
    <Head title="Tambah Kamus" />

    <AdminLayout>
        <template #title>Tambah Kamus</template>

        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-white">Tambah Kamus Baru</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Tambahkan kata baru ke dalam Kamus Melayu Belitung</p>
                </div>
                <Link href="/admin/kamus" class="inline-flex items-center px-4 py-2.5 bg-slate-600 text-white font-medium text-sm rounded-xl hover:bg-slate-700 transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </Link>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Informasi Dasar -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Informasi Dasar</h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Kata Melayu Belitung -->
                    <div>
                        <label for="kata" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Kata Melayu Belitung <span class="text-red-500">*</span>
                        </label>
                        <input id="kata" v-model="form.kata" type="text" required
                            placeholder="Masukkan kata dalam Bahasa Melayu Belitung"
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.kata }">
                        <p v-if="form.errors.kata" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.kata }}</p>
                    </div>

                    <!-- Kelas Kata (POS) -->
                    <div>
                        <label for="pos" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Kelas Kata <span class="text-slate-500">(Opsional)</span>
                        </label>
                        <select id="pos" v-model="form.pos"
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.pos }">
                            <option v-for="opt in posOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <p v-if="form.errors.pos" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.pos }}</p>
                    </div>

                    <!-- Definisi / Arti -->
                    <div>
                        <label for="definisi" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Definisi / Arti <span class="text-red-500">*</span>
                        </label>
                        <textarea id="definisi" v-model="form.definisi" rows="3" required
                            placeholder="Masukkan definisi atau arti dalam Bahasa Indonesia"
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.definisi }"></textarea>
                        <p v-if="form.errors.definisi" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.definisi }}</p>
                    </div>

                    <!-- Catatan Validasi -->
                    <div>
                        <label for="catatan_validasi" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Catatan Validasi <span class="text-slate-500">(Opsional)</span>
                        </label>
                        <textarea id="catatan_validasi" v-model="form.catatan_validasi" rows="2"
                            placeholder="Tambahkan catatan untuk proses validasi"
                            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.catatan_validasi }"></textarea>
                        <p v-if="form.errors.catatan_validasi" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.catatan_validasi }}</p>
                    </div>
                </div>
            </div>

            <!-- Contoh Kalimat -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Contoh Kalimat</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Opsional. Bisa lebih dari satu contoh.</p>
                    </div>
                    <button type="button" @click="addContoh"
                        class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Contoh
                    </button>
                </div>

                <div class="p-6">
                    <div v-if="form.contoh.length === 0" class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm">
                        Belum ada contoh kalimat. Klik "Tambah Contoh" untuk menambahkan.
                    </div>

                    <div v-for="(item, index) in form.contoh" :key="index"
                        class="mb-4 p-4 border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700/30">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Contoh {{ index + 1 }}</span>
                            <button type="button" @click="removeContoh(index)"
                                class="p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                    Kalimat dalam Bahasa Melayu Belitung <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="item.kalimat" rows="2"
                                    placeholder="Contoh kalimat dalam Bahasa Melayu Belitung"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors duration-200 resize-none"
                                    :class="{ 'border-red-500': form.errors[`contoh.${index}.kalimat`] }"></textarea>
                                <p v-if="form.errors[`contoh.${index}.kalimat`]" class="mt-1 text-xs text-red-600">{{ form.errors[`contoh.${index}.kalimat`] }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                    Arti Kalimat (Bahasa Indonesia) <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="item.arti" rows="2"
                                    placeholder="Terjemahan kalimat dalam Bahasa Indonesia"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors duration-200 resize-none"
                                    :class="{ 'border-red-500': form.errors[`contoh.${index}.arti`] }"></textarea>
                                <p v-if="form.errors[`contoh.${index}.arti`]" class="mt-1 text-xs text-red-600">{{ form.errors[`contoh.${index}.arti`] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audio Pengucapan -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Audio Pengucapan</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Tambahkan audio pengucapan kata Melayu Belitung</p>
                </div>
                <div class="p-6">
                    <div class="mb-6 flex gap-4">
                        <button type="button" @click="switchUploadMethod('upload')"
                            :class="['flex-1 py-3 px-4 rounded-lg font-medium text-sm transition-all duration-200', uploadMethod === 'upload' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600']">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Upload File
                            </div>
                        </button>
                        <button type="button" @click="switchUploadMethod('record')"
                            :class="['flex-1 py-3 px-4 rounded-lg font-medium text-sm transition-all duration-200', uploadMethod === 'record' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600']">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                                Rekam Audio
                            </div>
                        </button>
                    </div>

                    <!-- Upload File -->
                    <div v-if="uploadMethod === 'upload'">
                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <div class="mb-4">
                                <label for="audio-upload" class="cursor-pointer">
                                    <span class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">Pilih file audio</span>
                                    <span class="text-slate-600 dark:text-slate-400"> atau drag & drop di sini</span>
                                </label>
                                <input id="audio-upload" type="file" accept="audio/*,.mp3,.wav,.ogg,.m4a,.mp4" @change="handleFileUpload" class="hidden">
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">MP3, WAV, OGG, M4A (Maksimal 10MB)</p>
                        </div>
                    </div>

                    <!-- Rekam Audio -->
                    <div v-if="uploadMethod === 'record'">
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-6 text-center">
                            <div class="w-20 h-20 mx-auto bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center mb-4">
                                <svg :class="['w-8 h-8 text-white', isRecording && !isPaused ? 'animate-pulse' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </div>
                            <div v-if="isRecording || audioUrl" class="text-2xl font-mono text-slate-800 dark:text-white mb-4">{{ formattedTime }}</div>
                            <div class="mb-4">
                                <span v-if="!isRecording && !audioUrl" class="text-slate-600 dark:text-slate-400">Siap untuk merekam</span>
                                <span v-else-if="isRecording && !isPaused" class="text-red-600 dark:text-red-400 font-medium">🔴 Sedang merekam...</span>
                                <span v-else-if="isPaused" class="text-yellow-600 dark:text-yellow-400 font-medium">⏸️ Rekaman dijeda</span>
                                <span v-else-if="audioUrl" class="text-green-600 dark:text-green-400 font-medium">✅ Rekaman selesai</span>
                            </div>
                            <div class="flex justify-center gap-3">
                                <button v-if="!isRecording && !audioUrl" type="button" @click="startRecording" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium">Mulai Rekam</button>
                                <button v-if="isRecording && !isPaused" type="button" @click="pauseRecording" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors duration-200">Jeda</button>
                                <button v-if="isRecording && isPaused" type="button" @click="resumeRecording" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">Lanjutkan</button>
                                <button v-if="isRecording" type="button" @click="stopRecording" class="px-6 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors duration-200">Selesai</button>
                                <button v-if="audioUrl" type="button" @click="clearRecording" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <!-- Audio Preview -->
                    <div v-if="form.audio || audioUrl" class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ audioFileName || 'Audio rekaman' }}</p>
                                    <p class="text-xs text-green-600 dark:text-green-400">Audio siap untuk disimpan</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="playAudio" class="p-2 text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-800 rounded-lg transition-colors duration-200" title="Putar Audio">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <button type="button" @click="uploadMethod === 'upload' ? removeAudio() : clearRecording()" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-800 rounded-lg transition-colors duration-200" title="Hapus Audio">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-if="form.errors.audio" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.audio }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 flex flex-col sm:flex-row gap-4 sm:justify-end">
                    <Link href="/admin/kamus" class="inline-flex items-center justify-center px-6 py-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 font-medium text-sm rounded-lg transition-colors duration-200">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium text-sm rounded-lg hover:from-purple-700 hover:to-pink-700 focus:ring-4 focus:ring-purple-200 dark:focus:ring-purple-800 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Kamus' }}
                    </button>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
