<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useToast } from 'vue-toastification';

const toast = useToast();
const active = ref('overview');
const expanded = ref({ api: true, web: false, models: false, workflow: false });

const toggle = (g) => { expanded.value[g] = !expanded.value[g]; };
const select = (id) => { active.value = id; };

const copyText = async (text) => {
  try {
    await navigator.clipboard.writeText(text);
    toast.success('Disalin!', { timeout: 1500 });
  } catch { /* ignore */ }
};

const METHOD = {
  GET:    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  POST:   'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  PUT:    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  PATCH:  'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
  DELETE: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const STATUS_COLOR = {
  200: 'text-emerald-600 dark:text-emerald-400',
  201: 'text-emerald-600 dark:text-emerald-400',
  401: 'text-red-500 dark:text-red-400',
  403: 'text-red-500 dark:text-red-400',
  404: 'text-amber-500 dark:text-amber-400',
  422: 'text-amber-500 dark:text-amber-400',
  429: 'text-orange-500 dark:text-orange-400',
  500: 'text-red-600 dark:text-red-400',
};

const STATUS_TEXT = {
  200: 'OK', 201: 'Created', 401: 'Unauthorized', 403: 'Forbidden',
  404: 'Not Found', 422: 'Unprocessable Entity', 429: 'Too Many Requests', 500: 'Server Error',
};

const STEP_COLOR = {
  yellow: { dot: 'bg-amber-400', badge: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' },
  blue:   { dot: 'bg-blue-500',  badge: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' },
  green:  { dot: 'bg-green-500', badge: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' },
  red:    { dot: 'bg-red-500',   badge: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' },
};

// ═══════════════════════════════════════════════════
// Navigation
// ═══════════════════════════════════════════════════
const NAV = [
  { type: 'single', id: 'overview', label: 'Overview' },
  {
    type: 'group', id: 'api', label: 'REST API',
    items: [
      { id: 'api-auth', label: 'Auth' },
      { id: 'api-kamus', label: 'Kamus' },
      { id: 'api-kuis', label: 'Kuis' },
      { id: 'api-penerjemah', label: 'Penerjemah' },
      { id: 'api-pembelajaran', label: 'Pembelajaran' },
      { id: 'api-artikel', label: 'Artikel' },
      { id: 'api-galeri', label: 'Galeri' },
      { id: 'api-kontak', label: 'Kontak' },
    ],
  },
  {
    type: 'group', id: 'web', label: 'Web Routes',
    items: [
      { id: 'web-public', label: 'Public Routes' },
      { id: 'web-admin', label: 'Admin Routes' },
    ],
  },
  {
    type: 'group', id: 'models', label: 'Models & Schema',
    items: [
      { id: 'model-kamus', label: 'Kamus' },
      { id: 'model-quiz', label: 'Quiz' },
      { id: 'model-artikel', label: 'Artikel' },
      { id: 'model-modul', label: 'Modul Pembelajaran' },
      { id: 'model-user', label: 'User' },
    ],
  },
  {
    type: 'group', id: 'workflow', label: 'Validation Flow',
    items: [
      { id: 'workflow-kamus', label: 'Kamus' },
      { id: 'workflow-terjemah', label: 'Terjemah' },
    ],
  },
];

// ═══════════════════════════════════════════════════
// Documentation Data
// ═══════════════════════════════════════════════════
const DOCS = {
  overview: {
    type: 'overview',
    title: 'Bilikbecakap – Developer Overview',
    stack: [
      { label: 'Backend', value: 'Laravel 11 (PHP 8.3)' },
      { label: 'Frontend', value: 'Vue 3 + Inertia.js 2' },
      { label: 'Styling', value: 'Tailwind CSS 3' },
      { label: 'Database', value: 'MySQL (Eloquent ORM)' },
      { label: 'Auth', value: 'Laravel Sanctum (API) + Session (Web)' },
      { label: 'Permission', value: 'Spatie Laravel Permission' },
      { label: 'AI Primary', value: 'Google Gemini API (gemini-2.5-flash)' },
      { label: 'AI Fallback', value: 'OpenAI GPT-4o-mini' },
      { label: 'Rich Editor', value: 'TinyMCE 8 + CKEditor 5' },
    ],
    baseUrl: 'https://bilikbecakap.com/api/v1',
    headers: [
      { key: 'Content-Type', value: 'application/json' },
      { key: 'Accept', value: 'application/json' },
      { key: 'Authorization', value: 'Bearer {token}  ← endpoint yang butuh auth' },
    ],
    envVars: [
      { key: 'AI_PROVIDER', desc: 'Provider AI aktif: gemini atau openai' },
      { key: 'GEMINI_API_KEY', desc: 'API key Google Gemini' },
      { key: 'GEMINI_MODEL', desc: 'Model Gemini, default: gemini-2.5-flash' },
      { key: 'OPENAI_API_KEY', desc: 'API key OpenAI (fallback)' },
      { key: 'OPENAI_MODEL', desc: 'Model OpenAI, default: gpt-4o-mini' },
      { key: 'APP_URL', desc: 'Base URL aplikasi' },
      { key: 'DB_CONNECTION', desc: 'Driver database: mysql' },
      { key: 'SANCTUM_STATEFUL_DOMAINS', desc: 'Domain untuk Sanctum SPA auth' },
      { key: 'SESSION_DRIVER', desc: 'Driver session: database / redis' },
    ],
    locales: ['id — Bahasa Indonesia', 'en — English', 'mb — Bahasa Melayu'],
  },

  // ── REST API ──────────────────────────────────────
  'api-auth': {
    type: 'api',
    title: 'Authentication',
    description: 'Autentikasi menggunakan Laravel Sanctum. Setelah login, kirim token Bearer pada header Authorization untuk endpoint yang dilindungi.',
    endpoints: [
      {
        method: 'POST', path: '/api/v1/login', title: 'Login',
        description: 'Autentikasi pengguna dan dapatkan Sanctum access token.',
        auth: false, rateLimit: '10 req/menit',
        body: [
          { name: 'email', type: 'string', required: true, desc: 'Email pengguna' },
          { name: 'password', type: 'string', required: true, desc: 'Password pengguna' },
        ],
        responses: {
          200: '{\n  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ",\n  "user": {\n    "id": 1,\n    "name": "Admin",\n    "email": "admin@bilikbecakap.com"\n  }\n}',
          401: '{ "message": "These credentials do not match our records." }',
          422: '{\n  "message": "The email field is required.",\n  "errors": { "email": ["The email field is required."] }\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/user', title: 'Get Current User',
        description: 'Mendapatkan data pengguna yang sedang terautentikasi.',
        auth: true, rateLimit: null,
        responses: {
          200: '{\n  "id": 1,\n  "name": "Admin",\n  "email": "admin@bilikbecakap.com",\n  "roles": ["super-admin"],\n  "permissions": ["view kamus", "create kamus", "..."]\n}',
          401: '{ "message": "Unauthenticated." }',
        },
      },
      {
        method: 'POST', path: '/api/v1/logout', title: 'Logout',
        description: 'Menghapus access token aktif (revoke token saat ini).',
        auth: true, rateLimit: null,
        responses: {
          200: '{ "message": "Logged out successfully." }',
          401: '{ "message": "Unauthenticated." }',
        },
      },
    ],
  },

  'api-kamus': {
    type: 'api',
    title: 'Kamus',
    description: 'Akses entri kamus Bahasa Melayu Kutai. Semua endpoint bersifat publik tanpa autentikasi.',
    endpoints: [
      {
        method: 'GET', path: '/api/v1/kamus', title: 'List Kamus',
        description: 'Mendapatkan daftar entri kamus dengan pagination.',
        auth: false, rateLimit: null,
        query: [
          { name: 'search', type: 'string', required: false, desc: 'Cari berdasarkan kata' },
          { name: 'huruf', type: 'string', required: false, desc: 'Filter huruf awal (A–Z)' },
          { name: 'per_page', type: 'integer', required: false, desc: 'Jumlah per halaman (default: 15)' },
          { name: 'page', type: 'integer', required: false, desc: 'Nomor halaman' },
        ],
        responses: {
          200: '{\n  "data": [\n    {\n      "id": 1,\n      "kata": "abang",\n      "arti": "kakak laki-laki",\n      "huruf": "A",\n      "contoh": "Abangku pergi ke pasar.",\n      "audio": "/storage/kamus-audio/abang.mp3",\n      "status": 1\n    }\n  ],\n  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },\n  "meta": { "current_page": 1, "per_page": 15, "total": 285, "last_page": 19 }\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/kamus/{id}', title: 'Get Kamus Detail',
        description: 'Mendapatkan detail satu entri kamus berdasarkan ID.',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'id', type: 'integer', desc: 'ID entri kamus (numerik)' }],
        responses: {
          200: '{\n  "data": {\n    "id": 1,\n    "kata": "abang",\n    "arti": "kakak laki-laki",\n    "huruf": "A",\n    "contoh": "Abangku pergi ke pasar.",\n    "audio": "/storage/kamus-audio/abang.mp3",\n    "status": 1,\n    "created_at": "2024-01-15T08:00:00.000Z"\n  }\n}',
          404: '{ "message": "Not found." }',
        },
      },
    ],
  },

  'api-kuis': {
    type: 'api',
    title: 'Kuis',
    description: 'Endpoint kuis interaktif termasuk mode duel. POST endpoint dibatasi 20 req/menit untuk mencegah spam attempt.',
    endpoints: [
      {
        method: 'GET', path: '/api/v1/kuis', title: 'List Kuis',
        description: 'Mendapatkan semua kuis yang berstatus published.',
        auth: false, rateLimit: null,
        responses: {
          200: '{\n  "data": [\n    {\n      "id": 1,\n      "title": "Kuis Bahasa Melayu Dasar",\n      "slug": "kuis-bahasa-melayu-dasar",\n      "description": "Uji pengetahuan Anda...",\n      "is_duel_enabled": true,\n      "status": "published"\n    }\n  ]\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/kuis/{slug}', title: 'Detail Kuis',
        description: 'Detail kuis dan info total soal.',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug kuis (a-z, 0-9, -)' }],
        responses: {
          200: '{\n  "data": {\n    "id": 1,\n    "title": "Kuis Bahasa Melayu Dasar",\n    "slug": "kuis-bahasa-melayu-dasar",\n    "description": "...",\n    "is_duel_enabled": true,\n    "total_questions": 10\n  }\n}',
          404: '{ "message": "Not found." }',
        },
      },
      {
        method: 'GET', path: '/api/v1/kuis/{slug}/soal', title: 'Soal Kuis',
        description: 'Daftar soal beserta pilihan jawaban (option id tidak mengungkap jawaban benar).',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug kuis' }],
        responses: {
          200: '{\n  "data": [\n    {\n      "id": 1,\n      "question": "Apa arti kata \'abang\'?",\n      "type": "multiple_choice",\n      "options": [\n        { "id": 1, "text": "Kakak laki-laki" },\n        { "id": 2, "text": "Adik perempuan" },\n        { "id": 3, "text": "Paman" },\n        { "id": 4, "text": "Nenek" }\n      ]\n    }\n  ]\n}',
        },
      },
      {
        method: 'POST', path: '/api/v1/kuis/{slug}/mulai', title: 'Mulai Kuis',
        description: 'Membuat attempt baru. Kembalikan attempt_id yang wajib disertakan saat submit.',
        auth: false, rateLimit: '20 req/menit',
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug kuis' }],
        responses: {
          200: '{\n  "attempt_id": 42,\n  "quiz_slug": "kuis-bahasa-melayu-dasar",\n  "started_at": "2025-06-05T10:00:00.000Z"\n}',
          429: '{ "message": "Too Many Requests." }',
        },
      },
      {
        method: 'POST', path: '/api/v1/kuis/{slug}/submit', title: 'Submit Jawaban',
        description: 'Mengirim semua jawaban dan mendapatkan hasil kuis.',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug kuis' }],
        body: [
          { name: 'attempt_id', type: 'integer', required: true, desc: 'ID attempt dari endpoint mulai' },
          { name: 'answers', type: 'array', required: true, desc: 'Array objek { question_id, option_id }' },
        ],
        responses: {
          200: '{\n  "score": 80,\n  "correct": 8,\n  "total": 10,\n  "attempt_id": 42\n}',
          422: '{ "message": "Validation failed.", "errors": { ... } }',
        },
      },
      {
        method: 'POST', path: '/api/v1/kuis/{slug}/mulai-duel', title: 'Mulai Duel',
        description: 'Memulai mode duel. Hanya tersedia jika is_duel_enabled: true.',
        auth: false, rateLimit: '20 req/menit',
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug kuis dengan duel aktif' }],
        responses: {
          200: '{\n  "duel_code": "DUEL-ABC123",\n  "attempt_id": 43\n}',
        },
      },
      {
        method: 'POST', path: '/api/v1/kuis/{slug}/submit-duel', title: 'Submit Duel',
        description: 'Mengirim jawaban duel dan mendapatkan perbandingan skor.',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug kuis' }],
        body: [
          { name: 'attempt_id', type: 'integer', required: true, desc: 'ID attempt duel' },
          { name: 'answers', type: 'array', required: true, desc: 'Array objek { question_id, option_id }' },
        ],
        responses: {
          200: '{\n  "your_score": 80,\n  "opponent_score": 70,\n  "winner": "you"\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/kuis/{slug}/hasil/{attempt_id}', title: 'Hasil Kuis',
        description: 'Mendapatkan detail hasil kuis per attempt.',
        auth: false, rateLimit: null,
        pathParams: [
          { name: 'slug', type: 'string', desc: 'Slug kuis' },
          { name: 'attempt_id', type: 'integer', desc: 'ID attempt' },
        ],
        responses: {
          200: '{\n  "score": 80,\n  "correct": 8,\n  "total": 10,\n  "details": [\n    { "question": "...", "your_answer": "...", "correct_answer": "...", "is_correct": true }\n  ]\n}',
          404: '{ "message": "Not found." }',
        },
      },
    ],
  },

  'api-penerjemah': {
    type: 'api',
    title: 'Penerjemah',
    description: 'Endpoint terjemah menggunakan AI (Gemini primary, OpenAI fallback). Rate limit ketat karena memanggil API eksternal berbayar.',
    endpoints: [
      {
        method: 'POST', path: '/api/v1/penerjemah', title: 'Terjemahkan Teks',
        description: 'Menerjemahkan teks dua arah: Bahasa Indonesia ↔ Bahasa Melayu Kutai menggunakan AI.',
        auth: false, rateLimit: '30 req/menit',
        body: [
          { name: 'text', type: 'string', required: true, desc: 'Teks yang akan diterjemahkan' },
          { name: 'source_lang', type: 'string', required: false, desc: 'Bahasa sumber: id, mb (auto-detect jika kosong)' },
          { name: 'target_lang', type: 'string', required: false, desc: 'Bahasa target: id, mb (default: mb)' },
        ],
        responses: {
          200: '{\n  "original": "Saya makan nasi",\n  "translated": "Aku makan nasi",\n  "source_lang": "id",\n  "target_lang": "mb",\n  "provider": "gemini"\n}',
          429: '{ "message": "Too Many Requests." }',
          500: '{ "message": "AI service unavailable." }',
        },
      },
      {
        method: 'POST', path: '/api/v1/penerjemah/ocr', title: 'OCR — Ekstrak Teks dari Gambar',
        description: 'Mengekstrak teks dari gambar menggunakan Gemini multimodal vision.',
        auth: false, rateLimit: '30 req/menit',
        body: [
          { name: 'image', type: 'file (jpg/png/webp)', required: true, desc: 'File gambar yang mengandung teks, max 10MB' },
        ],
        responses: {
          200: '{\n  "extracted_text": "Teks yang berhasil diekstrak dari gambar...",\n  "provider": "gemini"\n}',
          422: '{ "message": "The image field is required." }',
          429: '{ "message": "Too Many Requests." }',
        },
      },
    ],
  },

  'api-pembelajaran': {
    type: 'api',
    title: 'Pembelajaran',
    description: 'Modul pembelajaran interaktif. Semua endpoint publik.',
    endpoints: [
      {
        method: 'GET', path: '/api/v1/pembelajaran', title: 'List Modul',
        description: 'Semua modul pembelajaran berstatus published.',
        auth: false, rateLimit: null,
        responses: {
          200: '{\n  "data": [\n    {\n      "id": 1,\n      "judul": "Pengenalan Bahasa Melayu Kutai",\n      "slug": "pengenalan-bmk",\n      "deskripsi": "Pelajari dasar-dasar...",\n      "thumbnail": "/storage/modul/cover.jpg",\n      "kategori": { "id": 1, "nama": "Dasar" }\n    }\n  ]\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/pembelajaran/kategori', title: 'List Kategori',
        description: 'Semua kategori modul pembelajaran.',
        auth: false, rateLimit: null,
        responses: {
          200: '{\n  "data": [\n    { "id": 1, "nama": "Dasar", "jumlah_modul": 5 },\n    { "id": 2, "nama": "Lanjutan", "jumlah_modul": 3 }\n  ]\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/pembelajaran/{slug}', title: 'Detail Modul',
        description: 'Detail modul beserta konten HTML lengkap.',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug modul (a-z, 0-9, -)' }],
        responses: {
          200: '{\n  "data": {\n    "id": 1,\n    "judul": "Pengenalan BMK",\n    "slug": "pengenalan-bmk",\n    "deskripsi": "...",\n    "konten": "<p>Konten HTML...</p>",\n    "thumbnail": "/storage/modul/cover.jpg"\n  }\n}',
          404: '{ "message": "Not found." }',
        },
      },
    ],
  },

  'api-artikel': {
    type: 'api',
    title: 'Artikel',
    description: 'Blog / artikel publik yang sudah dipublish.',
    endpoints: [
      {
        method: 'GET', path: '/api/v1/artikel', title: 'List Artikel',
        description: 'Artikel berstatus published dengan pagination.',
        auth: false, rateLimit: null,
        query: [
          { name: 'per_page', type: 'integer', required: false, desc: 'Jumlah per halaman (default: 10)' },
          { name: 'page', type: 'integer', required: false, desc: 'Nomor halaman' },
        ],
        responses: {
          200: '{\n  "data": [\n    {\n      "id": 1,\n      "title": "Sejarah Bahasa Melayu Kutai",\n      "slug": "sejarah-bmk",\n      "excerpt": "...",\n      "thumbnail": "/storage/artikel/cover.jpg",\n      "published_at": "2025-06-01T08:00:00.000Z"\n    }\n  ],\n  "meta": { "current_page": 1, "per_page": 10, "total": 42 }\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/artikel/kategori', title: 'List Kategori',
        description: 'Semua kategori artikel.',
        auth: false, rateLimit: null,
        responses: {
          200: '{\n  "data": [\n    { "id": 1, "nama": "Budaya", "jumlah": 8 },\n    { "id": 2, "nama": "Bahasa", "jumlah": 12 }\n  ]\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/artikel/{slug}', title: 'Detail Artikel',
        description: 'Detail artikel beserta konten HTML lengkap.',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'slug', type: 'string', desc: 'Slug artikel' }],
        responses: {
          200: '{\n  "data": {\n    "id": 1,\n    "title": "Sejarah Bahasa Melayu Kutai",\n    "slug": "sejarah-bmk",\n    "content": "<p>Konten artikel HTML...</p>",\n    "thumbnail": "/storage/artikel/cover.jpg",\n    "author": { "name": "Admin" },\n    "published_at": "2025-06-01T08:00:00.000Z"\n  }\n}',
          404: '{ "message": "Not found." }',
        },
      },
    ],
  },

  'api-galeri': {
    type: 'api',
    title: 'Galeri',
    description: 'Galeri foto publik.',
    endpoints: [
      {
        method: 'GET', path: '/api/v1/galeri', title: 'List Galeri',
        description: 'Semua item galeri.',
        auth: false, rateLimit: null,
        responses: {
          200: '{\n  "data": [\n    {\n      "id": 1,\n      "judul": "Tradisi Adat Kutai",\n      "deskripsi": "...",\n      "gambar": "/storage/galeri/foto1.jpg",\n      "kategori": { "id": 1, "nama": "Tradisi" }\n    }\n  ]\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/galeri/kategori', title: 'List Kategori',
        description: 'Semua kategori galeri.',
        auth: false, rateLimit: null,
        responses: {
          200: '{\n  "data": [\n    { "id": 1, "nama": "Tradisi", "jumlah": 15 }\n  ]\n}',
        },
      },
      {
        method: 'GET', path: '/api/v1/galeri/{id}', title: 'Detail Galeri',
        description: 'Detail item galeri berdasarkan ID.',
        auth: false, rateLimit: null,
        pathParams: [{ name: 'id', type: 'integer', desc: 'ID item galeri (numerik)' }],
        responses: {
          200: '{\n  "data": {\n    "id": 1,\n    "judul": "Tradisi Adat Kutai",\n    "deskripsi": "...",\n    "gambar": "/storage/galeri/foto1.jpg"\n  }\n}',
          404: '{ "message": "Not found." }',
        },
      },
    ],
  },

  'api-kontak': {
    type: 'api',
    title: 'Kontak',
    description: 'Formulir kontak. Submit publik; read kontak masuk memerlukan Sanctum token.',
    endpoints: [
      {
        method: 'POST', path: '/api/v1/kontak', title: 'Submit Kontak',
        description: 'Mengirim pesan melalui formulir kontak.',
        auth: false, rateLimit: '10 req/menit',
        body: [
          { name: 'nama', type: 'string', required: true, desc: 'Nama pengirim' },
          { name: 'email', type: 'string', required: true, desc: 'Email pengirim' },
          { name: 'pesan', type: 'string', required: true, desc: 'Isi pesan' },
        ],
        responses: {
          201: '{ "message": "Pesan berhasil dikirim." }',
          422: '{\n  "message": "Validation failed.",\n  "errors": { "email": ["Email tidak valid."] }\n}',
          429: '{ "message": "Too Many Requests." }',
        },
      },
      {
        method: 'GET', path: '/api/v1/kontak', title: 'List Kontak (Admin)',
        description: 'Semua pesan kontak masuk. Memerlukan autentikasi.',
        auth: true, rateLimit: null,
        responses: {
          200: '{\n  "data": [\n    { "id": 1, "nama": "Budi", "email": "budi@mail.com", "pesan": "...", "created_at": "..." }\n  ]\n}',
          401: '{ "message": "Unauthenticated." }',
        },
      },
      {
        method: 'GET', path: '/api/v1/kontak/{id}', title: 'Detail Kontak (Admin)',
        description: 'Detail satu pesan kontak. Memerlukan autentikasi.',
        auth: true, rateLimit: null,
        pathParams: [{ name: 'id', type: 'integer', desc: 'ID kontak (numerik)' }],
        responses: {
          200: '{\n  "data": { "id": 1, "nama": "Budi", "email": "budi@mail.com", "pesan": "Halo, saya ingin bertanya...", "created_at": "..." }\n}',
          401: '{ "message": "Unauthenticated." }',
          404: '{ "message": "Not found." }',
        },
      },
    ],
  },

  // ── Web Routes ────────────────────────────────────
  'web-public': {
    type: 'web-routes',
    title: 'Public Web Routes',
    description: 'Halaman publik yang dapat diakses tanpa login. Semua merender komponen Vue via Inertia.js.',
    routes: [
      { method: 'GET', path: '/', name: 'landing', component: 'Frontend/LandingPage', desc: 'Halaman beranda utama' },
      { method: 'GET', path: '/kamus', name: 'kamus.public', component: 'Frontend/Kamus', desc: 'Kamus publik dengan search & filter huruf' },
      { method: 'GET', path: '/penerjemah', name: 'penerjemah', component: 'Frontend/Penerjemah', desc: 'Halaman penerjemah interaktif' },
      { method: 'POST', path: '/penerjemah/process', name: 'penerjemah.process', component: '—', desc: 'Proses terjemah dari halaman frontend' },
      { method: 'GET', path: '/pembelajaran', name: 'pembelajaran', component: 'Frontend/Pembelajaran', desc: 'Daftar modul pembelajaran' },
      { method: 'GET', path: '/pembelajaran/{slug}', name: 'pembelajaran.show', component: 'Frontend/PembelajaranDetail', desc: 'Detail modul pembelajaran' },
      { method: 'GET', path: '/artikel', name: 'artikel.index', component: 'Frontend/Blog', desc: 'Daftar artikel / blog' },
      { method: 'GET', path: '/artikel/{slug}', name: 'artikel.show', component: 'Frontend/BlogDetail', desc: 'Detail artikel' },
      { method: 'GET', path: '/kuis', name: 'kuis.index', component: 'Frontend/Kuis', desc: 'Daftar kuis publik' },
      { method: 'GET', path: '/kuis/{slug}', name: 'kuis.show', component: 'Frontend/KuisDetail', desc: 'Detail kuis' },
      { method: 'POST', path: '/kuis/{slug}/begin', name: 'quiz-attempt.begin', component: '—', desc: 'Mulai kuis (Inertia)' },
      { method: 'GET', path: '/kuis/{slug}/do', name: 'quiz-attempt.quiz', component: 'Frontend/KuisQuiz', desc: 'Halaman pengerjaan kuis' },
      { method: 'POST', path: '/kuis/{slug}/do/submit', name: 'quiz-attempt.submit', component: '—', desc: 'Submit jawaban kuis' },
      { method: 'GET', path: '/kuis/{slug}/result', name: 'quiz-attempt.result', component: 'Frontend/KuisResult', desc: 'Halaman hasil kuis' },
      { method: 'POST', path: '/kuis/{slug}/begin-duel', name: 'quiz-attempt.begin-duel', component: '—', desc: 'Mulai mode duel' },
      { method: 'GET', path: '/kuis/{slug}/duel', name: 'quiz-attempt.duel', component: 'Frontend/KuisDuel', desc: 'Halaman pengerjaan duel' },
      { method: 'POST', path: '/kuis/{slug}/submit-duel', name: 'quiz-attempt.submit-duel', component: '—', desc: 'Submit jawaban duel' },
      { method: 'GET', path: '/kuis/{slug}/duel-result', name: 'quiz-attempt.duel-result', component: 'Frontend/KuisDuelResult', desc: 'Hasil duel' },
      { method: 'GET', path: '/galeri', name: 'galeri.public', component: 'Frontend/Gambar', desc: 'Halaman galeri foto' },
      { method: 'GET', path: '/kontak', name: 'kontak.index', component: 'Frontend/Kontak', desc: 'Halaman formulir kontak' },
      { method: 'POST', path: '/kontak', name: 'kontak.store', component: '—', desc: 'Submit formulir kontak' },
      { method: 'GET', path: '/tentang', name: 'tentang', component: 'Frontend/Tentang', desc: 'Halaman tentang' },
      { method: 'GET', path: '/aplikasi', name: 'aplikasi', component: 'Frontend/Aplikasi', desc: 'Informasi aplikasi mobile' },
      { method: 'GET', path: '/language/{locale}', name: 'language.switch', component: '—', desc: 'Ganti bahasa: id / en / mb' },
    ],
  },

  'web-admin': {
    type: 'web-routes',
    title: 'Admin Web Routes',
    description: 'Route panel admin (middleware: auth, verified). Permission berbasis Spatie Laravel Permission.',
    routes: [
      { method: 'GET', path: '/admin/dashboard', name: 'dashboard', component: 'Dashboard', desc: 'Dashboard utama' },
      { method: 'GET', path: '/admin/kamus', name: 'kamus.index', component: 'Kamus/Index', desc: 'Daftar kamus · perm: view kamus' },
      { method: 'GET', path: '/admin/kamus/create', name: 'kamus.create', component: 'Kamus/Create', desc: 'Form tambah entri kamus' },
      { method: 'POST', path: '/admin/kamus', name: 'kamus.store', component: '—', desc: 'Simpan entri kamus baru' },
      { method: 'GET', path: '/admin/kamus/{id}/edit', name: 'kamus.edit', component: 'Kamus/Edit', desc: 'Form edit kamus' },
      { method: 'PUT', path: '/admin/kamus/{id}', name: 'kamus.update', component: '—', desc: 'Update entri kamus' },
      { method: 'DELETE', path: '/admin/kamus/{id}', name: 'kamus.destroy', component: '—', desc: 'Hapus entri kamus' },
      { method: 'GET', path: '/admin/kamus/{id}/tinjauan', name: 'kamus.tinjauan', component: 'Kamus/Tinjauan', desc: 'Halaman tinjauan validasi' },
      { method: 'POST', path: '/admin/kamus/{id}/validasi', name: 'kamus.validasi', component: '—', desc: 'Validasi kamus · perm: validasi kamus' },
      { method: 'POST', path: '/admin/kamus/{id}/finalisasi', name: 'kamus.finalisasi', component: '—', desc: 'Finalisasi kamus · perm: finalisasi kamus' },
      { method: 'PATCH', path: '/admin/kamus/{id}/approve', name: 'kamus.approve', component: '—', desc: 'Approve kamus' },
      { method: 'PATCH', path: '/admin/kamus/{id}/reject', name: 'kamus.reject', component: '—', desc: 'Tolak kamus' },
      { method: 'GET', path: '/admin/terjemah', name: 'terjemah.index', component: 'Terjemah/Index', desc: 'Daftar pengujian terjemah' },
      { method: 'POST', path: '/admin/terjemah', name: 'terjemah.store', component: '—', desc: 'Tambah data uji terjemah' },
      { method: 'POST', path: '/admin/terjemah/{id}/validasi', name: 'terjemah.validasi', component: '—', desc: 'Validasi terjemah' },
      { method: 'POST', path: '/admin/terjemah/{id}/finalisasi', name: 'terjemah.finalisasi', component: '—', desc: 'Finalisasi terjemah' },
      { method: 'GET', path: '/admin/artikel', name: 'admin.artikel.index', component: 'Artikel/Index', desc: 'Daftar artikel · perm: view artikel' },
      { method: 'POST', path: '/admin/artikel', name: 'admin.artikel.store', component: '—', desc: 'Simpan artikel baru' },
      { method: 'PUT', path: '/admin/artikel/{id}', name: 'admin.artikel.update', component: '—', desc: 'Update artikel' },
      { method: 'DELETE', path: '/admin/artikel/{id}', name: 'admin.artikel.destroy', component: '—', desc: 'Hapus artikel' },
      { method: 'PATCH', path: '/admin/artikel/{id}/approve', name: 'admin.artikel.approve', component: '—', desc: 'Publish artikel' },
      { method: 'PATCH', path: '/admin/artikel/{id}/reject', name: 'admin.artikel.reject', component: '—', desc: 'Tolak artikel' },
      { method: 'GET', path: '/admin/quiz', name: 'quiz.index', component: 'Quiz/Index', desc: 'Daftar quiz · perm: view quiz' },
      { method: 'POST', path: '/admin/quiz', name: 'quiz.store', component: '—', desc: 'Simpan quiz baru' },
      { method: 'PUT', path: '/admin/quiz/{id}', name: 'quiz.update', component: '—', desc: 'Update quiz' },
      { method: 'DELETE', path: '/admin/quiz/{id}', name: 'quiz.destroy', component: '—', desc: 'Hapus quiz' },
      { method: 'GET', path: '/admin/quiz/{id}/questions', name: 'quiz.questions.index', component: 'Quiz/Questions/Index', desc: 'Manajemen soal quiz' },
      { method: 'POST', path: '/admin/quiz/{id}/questions', name: 'quiz.questions.store', component: '—', desc: 'Simpan soal baru' },
      { method: 'PUT', path: '/admin/quiz/{id}/questions/{qid}', name: 'quiz.questions.update', component: '—', desc: 'Update soal' },
      { method: 'DELETE', path: '/admin/quiz/{id}/questions/{qid}', name: 'quiz.questions.destroy', component: '—', desc: 'Hapus soal' },
      { method: 'GET', path: '/admin/quiz-test', name: 'quiz.attempt.index', component: 'QuizTest/Index', desc: 'Test quiz sebagai admin · perm: view test quiz' },
      { method: 'GET', path: '/admin/modul-pembelajaran', name: 'modul-pembelajaran.index', component: 'ModulPembelajaran/Index', desc: 'Daftar modul · perm: view modul pembelajaran' },
      { method: 'GET', path: '/admin/galeri', name: 'galeri.index', component: 'Galeri/Index', desc: 'Manajemen galeri · perm: view galeri' },
      { method: 'GET', path: '/admin/komentar', name: 'komentar.index', component: 'Komentar/Index', desc: 'Manajemen komentar · perm: view komentar' },
      { method: 'GET', path: '/admin/kontak-masuk', name: 'admin.kontak.index', component: 'Kontak/Index', desc: 'Pesan kontak masuk · perm: view kontak' },
      { method: 'GET', path: '/admin/dataset-translate', name: 'dataset-translate.index', component: 'DatasetTranslate/Index', desc: 'Dataset terjemah · perm: view dataset' },
      { method: 'GET', path: '/data-master', name: 'data-master.index', component: 'DataMaster/Index', desc: 'Data master · perm: view data master' },
      { method: 'GET', path: '/users', name: 'users.index', component: 'Users/Index', desc: 'Manajemen user · perm: view users' },
      { method: 'GET', path: '/roles', name: 'roles.index', component: 'Roles/Index', desc: 'Manajemen role · perm: view roles' },
      { method: 'GET', path: '/permissions', name: 'permissions.index', component: 'Permissions/Index', desc: 'Manajemen permission · role: super-admin' },
      { method: 'GET', path: '/activity-logs', name: 'activity-logs.index', component: 'ActivityLog/Index', desc: 'Activity logs · role: super-admin' },
      { method: 'GET', path: '/admin/dev-docs', name: 'dev-docs.index', component: 'DevDocs/Index', desc: 'Halaman ini · role: super-admin' },
    ],
  },

  // ── Models ────────────────────────────────────────
  'model-kamus': {
    type: 'model',
    title: 'Kamus',
    table: 'kamus',
    description: 'Entri kamus Bahasa Melayu Kutai dengan status validasi multi-tahap.',
    fields: [
      { name: 'id', type: 'BIGINT UNSIGNED', pk: true, nullable: false, desc: 'Primary key' },
      { name: 'kata', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Kata dalam Bahasa Melayu Kutai' },
      { name: 'arti', type: 'TEXT', pk: false, nullable: false, desc: 'Arti / terjemahan dalam Bahasa Indonesia' },
      { name: 'huruf', type: 'CHAR(1)', pk: false, nullable: true, desc: 'Huruf pertama kata (A–Z) untuk filter' },
      { name: 'contoh', type: 'TEXT', pk: false, nullable: true, desc: 'Contoh kalimat penggunaan' },
      { name: 'audio', type: 'VARCHAR(255)', pk: false, nullable: true, desc: 'Path file audio (storage/kamus-audio/)' },
      { name: 'status', type: 'TINYINT', pk: false, nullable: false, desc: '1=aktif · 2=nonaktif · 3=pending validasi · 4=pending finalisasi' },
      { name: 'created_by', type: 'BIGINT FK→users', pk: false, nullable: true, desc: 'ID user pembuat entri' },
      { name: 'created_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu pembuatan' },
      { name: 'updated_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu terakhir diperbarui' },
    ],
    relations: [
      { type: 'hasMany', related: 'KamusContoh', fk: 'kamus_id', desc: 'Contoh kalimat tambahan' },
      { type: 'hasMany', related: 'KamusValidasi', fk: 'kamus_id', desc: 'Riwayat validasi' },
      { type: 'belongsTo', related: 'User', fk: 'created_by', desc: 'Pembuat entri' },
    ],
  },

  'model-quiz': {
    type: 'model',
    title: 'Quiz',
    table: 'quizzes',
    description: 'Kuis interaktif dengan dukungan mode duel antar pemain.',
    fields: [
      { name: 'id', type: 'BIGINT UNSIGNED', pk: true, nullable: false, desc: 'Primary key' },
      { name: 'title', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Judul kuis' },
      { name: 'slug', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Slug unik untuk URL (a-z, 0-9, -)' },
      { name: 'description', type: 'TEXT', pk: false, nullable: true, desc: 'Deskripsi kuis' },
      { name: 'music_id', type: 'BIGINT FK→master_media_music_quiz', pk: false, nullable: true, desc: 'Musik latar kuis' },
      { name: 'is_duel_enabled', type: 'BOOLEAN', pk: false, nullable: false, desc: 'Aktifkan mode duel antar pemain' },
      { name: 'status', type: "ENUM('draft','published')", pk: false, nullable: false, desc: 'Status publikasi kuis' },
      { name: 'created_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu pembuatan' },
      { name: 'updated_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu terakhir diperbarui' },
    ],
    relations: [
      { type: 'hasMany', related: 'QuizQuestion', fk: 'quiz_id', desc: 'Soal kuis' },
      { type: 'hasMany', related: 'QuizAttempt', fk: 'quiz_id', desc: 'Riwayat percobaan pengguna' },
      { type: 'belongsTo', related: 'MasterMediaMusicQuiz', fk: 'music_id', desc: 'Musik latar' },
    ],
  },

  'model-artikel': {
    type: 'model',
    title: 'Artikel',
    table: 'artikel',
    description: 'Artikel blog dengan workflow approval: draft → pending → published / rejected.',
    fields: [
      { name: 'id', type: 'BIGINT UNSIGNED', pk: true, nullable: false, desc: 'Primary key' },
      { name: 'title', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Judul artikel' },
      { name: 'slug', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Slug unik untuk URL' },
      { name: 'content', type: 'LONGTEXT', pk: false, nullable: false, desc: 'Konten HTML (dari rich text editor)' },
      { name: 'excerpt', type: 'TEXT', pk: false, nullable: true, desc: 'Ringkasan / excerpt artikel' },
      { name: 'thumbnail', type: 'VARCHAR(255)', pk: false, nullable: true, desc: 'Path gambar thumbnail' },
      { name: 'kategori_id', type: 'BIGINT FK→master_artikel', pk: false, nullable: true, desc: 'Kategori artikel' },
      { name: 'author_id', type: 'BIGINT FK→users', pk: false, nullable: true, desc: 'Penulis artikel' },
      { name: 'status', type: "ENUM('draft','pending','published','rejected')", pk: false, nullable: false, desc: 'Status workflow artikel' },
      { name: 'published_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu dipublish' },
      { name: 'created_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu pembuatan' },
      { name: 'updated_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu terakhir diperbarui' },
    ],
    relations: [
      { type: 'belongsTo', related: 'User', fk: 'author_id', desc: 'Penulis artikel' },
      { type: 'belongsTo', related: 'MasterArtikel', fk: 'kategori_id', desc: 'Kategori artikel' },
      { type: 'hasMany', related: 'Komentar', fk: 'artikel_id', desc: 'Komentar pada artikel' },
    ],
  },

  'model-modul': {
    type: 'model',
    title: 'Modul Pembelajaran',
    table: 'modul_pembelajaran',
    description: 'Modul pembelajaran interaktif dengan konten HTML.',
    fields: [
      { name: 'id', type: 'BIGINT UNSIGNED', pk: true, nullable: false, desc: 'Primary key' },
      { name: 'judul', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Judul modul' },
      { name: 'slug', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Slug unik untuk URL' },
      { name: 'deskripsi', type: 'TEXT', pk: false, nullable: false, desc: 'Deskripsi singkat modul' },
      { name: 'konten', type: 'LONGTEXT', pk: false, nullable: false, desc: 'Konten lengkap HTML' },
      { name: 'thumbnail', type: 'VARCHAR(255)', pk: false, nullable: true, desc: 'Path gambar cover' },
      { name: 'kategori_id', type: 'BIGINT FK→master_modul', pk: false, nullable: true, desc: 'Kategori modul' },
      { name: 'status', type: "ENUM('draft','published')", pk: false, nullable: false, desc: 'Status publikasi' },
      { name: 'created_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu pembuatan' },
      { name: 'updated_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu terakhir diperbarui' },
    ],
    relations: [
      { type: 'belongsTo', related: 'MasterModul', fk: 'kategori_id', desc: 'Kategori modul' },
    ],
  },

  'model-user': {
    type: 'model',
    title: 'User',
    table: 'users',
    description: 'Pengguna sistem dengan role dan permission (Spatie Laravel Permission).',
    fields: [
      { name: 'id', type: 'BIGINT UNSIGNED', pk: true, nullable: false, desc: 'Primary key' },
      { name: 'name', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Nama lengkap pengguna' },
      { name: 'email', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Email unik untuk login' },
      { name: 'password', type: 'VARCHAR(255)', pk: false, nullable: false, desc: 'Password ter-hash (bcrypt)' },
      { name: 'email_verified_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu verifikasi email, null = belum terverifikasi' },
      { name: 'created_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu pendaftaran' },
      { name: 'updated_at', type: 'TIMESTAMP', pk: false, nullable: true, desc: 'Waktu terakhir diperbarui' },
    ],
    relations: [
      { type: 'hasOne', related: 'UserProfile', fk: 'user_id', desc: 'Profil extended (foto, bio, dll)' },
      { type: 'morphToMany', related: 'Role (Spatie)', fk: '—', desc: 'Roles: super-admin, admin, editor, dll' },
      { type: 'morphToMany', related: 'Permission (Spatie)', fk: '—', desc: 'Permission langsung (tanpa melalui role)' },
    ],
  },

  // ── Workflows ─────────────────────────────────────
  'workflow-kamus': {
    type: 'workflow',
    title: 'Kamus Validation Workflow',
    description: 'Entri kamus melewati 3 tahap validasi sebelum dipublish. Setiap tahap memerlukan permission yang berbeda untuk memastikan kualitas konten.',
    steps: [
      {
        status: 3, label: 'Pending Validasi', color: 'yellow',
        actor: 'Kontributor · perm: create kamus',
        action: 'Menambah entri kamus baru. Status otomatis menjadi 3 (pending validasi).',
        route: 'POST /admin/kamus',
      },
      {
        status: 4, label: 'Pending Finalisasi', color: 'blue',
        actor: 'Validator · perm: validasi kamus',
        action: 'Memeriksa konten, arti, dan contoh kalimat. Jika valid, lanjut ke finalisasi.',
        route: 'POST /admin/kamus/{id}/validasi',
      },
      {
        status: 1, label: 'Aktif (Published)', color: 'green',
        actor: 'Finalisator · perm: finalisasi kamus',
        action: 'Entri difinalisasi dan aktif tampil di halaman kamus publik.',
        route: 'POST /admin/kamus/{id}/finalisasi',
      },
    ],
    rejectedStep: {
      status: 2, label: 'Ditolak', color: 'red',
      actor: 'Finalisator · perm: finalisasi kamus',
      action: 'Entri ditolak dari tahap finalisasi. Tidak tampil di halaman publik.',
      route: 'PATCH /admin/kamus/{id}/reject',
    },
  },

  'workflow-terjemah': {
    type: 'workflow',
    title: 'Terjemah Pengujian Workflow',
    description: 'Data pengujian terjemahan melewati 3 tahap validasi untuk memastikan akurasi sebelum dijadikan dataset training AI.',
    steps: [
      {
        status: 1, label: 'Baru Dibuat', color: 'yellow',
        actor: 'Penguji · perm: create terjemah',
        action: 'Menambah data uji terjemahan baru.',
        route: 'POST /admin/terjemah',
      },
      {
        status: 2, label: 'Tervalidasi', color: 'blue',
        actor: 'Validator · perm: validasi terjemah',
        action: 'Meninjau akurasi terjemahan dan mengkonfirmasi data uji.',
        route: 'POST /admin/terjemah/{id}/validasi',
      },
      {
        status: 3, label: 'Final (Dataset)', color: 'green',
        actor: 'Finalisator · perm: finalisasi terjemah',
        action: 'Data difinalisasi dan siap digunakan sebagai dataset training AI.',
        route: 'POST /admin/terjemah/{id}/finalisasi',
      },
    ],
  },
};

const currentDocs = computed(() => DOCS[active.value] ?? DOCS.overview);
</script>

<template>
  <Head title="Developer Docs" />
  <AdminLayout>
    <div
      class="flex border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-900"
      style="height: calc(100vh - 140px); min-height: 500px"
    >
      <!-- ═══════════════ Left Navigation ═══════════════ -->
      <nav class="w-52 flex-shrink-0 border-r border-slate-200 dark:border-slate-700 overflow-y-auto bg-slate-50 dark:bg-slate-950 py-3">
        <div class="px-4 pb-3 border-b border-slate-200 dark:border-slate-700 mb-2">
          <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Developer Docs</p>
        </div>

        <div v-for="item in NAV" :key="item.id" class="px-2">
          <!-- Single item -->
          <button
            v-if="item.type === 'single'"
            @click="select(item.id)"
            :class="[
              'w-full text-left flex items-center px-3 py-2 text-sm rounded-lg transition-colors mb-0.5',
              active === item.id
                ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 font-medium'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
            ]"
          >
            {{ item.label }}
          </button>

          <!-- Group -->
          <div v-else class="mb-1">
            <button
              @click="toggle(item.id)"
              class="w-full text-left flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hover:text-slate-700 dark:hover:text-slate-300 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
            >
              <span>{{ item.label }}</span>
              <font-awesome-icon :icon="expanded[item.id] ? 'chevron-down' : 'chevron-right'" class="w-3 h-3" />
            </button>
            <div v-show="expanded[item.id]" class="pl-1 space-y-0.5">
              <button
                v-for="sub in item.items"
                :key="sub.id"
                @click="select(sub.id)"
                :class="[
                  'w-full text-left px-3 py-1.5 text-sm rounded-lg transition-colors block',
                  active === sub.id
                    ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 font-medium'
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                ]"
              >
                {{ sub.label }}
              </button>
            </div>
          </div>
        </div>
      </nav>

      <!-- ═══════════════ Right Content ═══════════════ -->
      <div class="flex-1 overflow-y-auto p-6">

        <!-- ── OVERVIEW ─────────────────────────── -->
        <template v-if="currentDocs.type === 'overview'">
          <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ currentDocs.title }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Arsitektur, tech stack, dan konfigurasi dasar sistem.</p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <!-- Tech Stack -->
            <div class="bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
              <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Tech Stack</h3>
              <dl class="space-y-2">
                <div v-for="item in currentDocs.stack" :key="item.label" class="flex gap-3 items-start">
                  <dt class="text-xs text-slate-400 dark:text-slate-500 w-24 flex-shrink-0 pt-0.5">{{ item.label }}</dt>
                  <dd class="text-xs font-mono text-slate-700 dark:text-slate-300">{{ item.value }}</dd>
                </div>
              </dl>
            </div>

            <!-- Request Headers & Base URL -->
            <div class="bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
              <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Default Request Headers</h3>
              <dl class="space-y-2">
                <div v-for="h in currentDocs.headers" :key="h.key" class="flex gap-3 items-start">
                  <dt class="text-xs font-mono text-blue-600 dark:text-blue-400 w-32 flex-shrink-0">{{ h.key }}</dt>
                  <dd class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ h.value }}</dd>
                </div>
              </dl>
              <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">Base URL</p>
                <code class="text-xs font-mono text-emerald-600 dark:text-emerald-400">{{ currentDocs.baseUrl }}</code>
              </div>
            </div>
          </div>

          <!-- Env Variables -->
          <div class="bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Environment Variables</h3>
            <table class="w-full text-xs">
              <thead>
                <tr class="text-left border-b border-slate-200 dark:border-slate-700">
                  <th class="pb-2 font-semibold text-slate-500 dark:text-slate-400 w-52">Variable</th>
                  <th class="pb-2 font-semibold text-slate-500 dark:text-slate-400">Deskripsi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <tr v-for="env in currentDocs.envVars" :key="env.key">
                  <td class="py-2 font-mono text-amber-600 dark:text-amber-400">{{ env.key }}</td>
                  <td class="py-2 text-slate-600 dark:text-slate-300">{{ env.desc }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Locales -->
          <div class="bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Bahasa yang Didukung</h3>
            <div class="flex gap-2 flex-wrap">
              <span
                v-for="loc in currentDocs.locales" :key="loc"
                class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs rounded-full font-mono"
              >
                {{ loc }}
              </span>
            </div>
          </div>
        </template>

        <!-- ── API SECTION ──────────────────────── -->
        <template v-else-if="currentDocs.type === 'api'">
          <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ currentDocs.title }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ currentDocs.description }}</p>
          </div>

          <div class="space-y-4">
            <div
              v-for="ep in currentDocs.endpoints"
              :key="ep.path + ep.method"
              class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden"
            >
              <!-- Endpoint header bar -->
              <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800 flex-wrap">
                <span :class="['text-xs font-bold px-2.5 py-1 rounded font-mono flex-shrink-0', METHOD[ep.method]]">
                  {{ ep.method }}
                </span>
                <code class="text-sm font-mono text-slate-700 dark:text-slate-200 flex-1 min-w-0 truncate">{{ ep.path }}</code>
                <div class="flex items-center gap-2 flex-shrink-0">
                  <span v-if="ep.auth" class="text-xs px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full">
                    Bearer Auth
                  </span>
                  <span v-if="ep.rateLimit" class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                    {{ ep.rateLimit }}
                  </span>
                </div>
              </div>

              <!-- Endpoint body -->
              <div class="p-4 space-y-4">
                <div>
                  <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ ep.title }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ ep.description }}</p>
                </div>

                <!-- Path Params -->
                <div v-if="ep.pathParams?.length">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Path Parameters</p>
                  <div class="border border-slate-100 dark:border-slate-700 rounded-lg overflow-hidden">
                    <table class="w-full text-xs">
                      <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr class="text-left text-slate-400 dark:text-slate-500">
                          <th class="px-3 py-2 font-medium w-28">Name</th>
                          <th class="px-3 py-2 font-medium w-24">Type</th>
                          <th class="px-3 py-2 font-medium">Description</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <tr v-for="p in ep.pathParams" :key="p.name">
                          <td class="px-3 py-2 font-mono text-blue-600 dark:text-blue-400">{{ p.name }}</td>
                          <td class="px-3 py-2 font-mono text-slate-400 dark:text-slate-500">{{ p.type }}</td>
                          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ p.desc }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Query Params -->
                <div v-if="ep.query?.length">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Query Parameters</p>
                  <div class="border border-slate-100 dark:border-slate-700 rounded-lg overflow-hidden">
                    <table class="w-full text-xs">
                      <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr class="text-left text-slate-400 dark:text-slate-500">
                          <th class="px-3 py-2 font-medium w-28">Name</th>
                          <th class="px-3 py-2 font-medium w-20">Type</th>
                          <th class="px-3 py-2 font-medium w-20">Required</th>
                          <th class="px-3 py-2 font-medium">Description</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <tr v-for="p in ep.query" :key="p.name">
                          <td class="px-3 py-2 font-mono text-blue-600 dark:text-blue-400">{{ p.name }}</td>
                          <td class="px-3 py-2 font-mono text-slate-400 dark:text-slate-500">{{ p.type }}</td>
                          <td class="px-3 py-2">
                            <span :class="p.required ? 'text-red-500' : 'text-slate-300 dark:text-slate-600'">
                              {{ p.required ? 'Ya' : 'Tidak' }}
                            </span>
                          </td>
                          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ p.desc }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Body Params -->
                <div v-if="ep.body?.length">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Request Body (application/json)</p>
                  <div class="border border-slate-100 dark:border-slate-700 rounded-lg overflow-hidden">
                    <table class="w-full text-xs">
                      <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr class="text-left text-slate-400 dark:text-slate-500">
                          <th class="px-3 py-2 font-medium w-28">Field</th>
                          <th class="px-3 py-2 font-medium w-28">Type</th>
                          <th class="px-3 py-2 font-medium w-20">Required</th>
                          <th class="px-3 py-2 font-medium">Description</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <tr v-for="p in ep.body" :key="p.name">
                          <td class="px-3 py-2 font-mono text-blue-600 dark:text-blue-400">{{ p.name }}</td>
                          <td class="px-3 py-2 font-mono text-slate-400 dark:text-slate-500">{{ p.type }}</td>
                          <td class="px-3 py-2">
                            <span :class="p.required ? 'text-red-500' : 'text-slate-300 dark:text-slate-600'">
                              {{ p.required ? 'Ya' : 'Tidak' }}
                            </span>
                          </td>
                          <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ p.desc }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Responses -->
                <div v-if="ep.responses">
                  <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Responses</p>
                  <div v-for="(body, code) in ep.responses" :key="code" class="mb-3 last:mb-0">
                    <div class="flex items-center gap-2 mb-1.5">
                      <span :class="['text-sm font-bold font-mono', STATUS_COLOR[code] ?? 'text-slate-500']">{{ code }}</span>
                      <span class="text-xs text-slate-400 dark:text-slate-500">{{ STATUS_TEXT[code] ?? '' }}</span>
                      <button
                        @click="copyText(body)"
                        class="ml-auto text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors px-2 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700"
                      >
                        Salin
                      </button>
                    </div>
                    <pre class="bg-slate-900 dark:bg-slate-950 text-emerald-400 text-xs p-3 rounded-lg overflow-x-auto font-mono leading-relaxed">{{ body }}</pre>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- ── WEB ROUTES ───────────────────────── -->
        <template v-else-if="currentDocs.type === 'web-routes'">
          <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ currentDocs.title }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ currentDocs.description }}</p>
          </div>

          <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
            <table class="w-full text-xs">
              <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left border-b border-slate-200 dark:border-slate-700">
                  <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 w-20">Method</th>
                  <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Path</th>
                  <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 hidden md:table-cell">Route Name</th>
                  <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 hidden xl:table-cell">Vue Component</th>
                  <th class="px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                <tr
                  v-for="r in currentDocs.routes"
                  :key="r.path + r.method"
                  class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                >
                  <td class="px-4 py-2.5">
                    <span :class="['font-bold px-1.5 py-0.5 rounded font-mono text-xs', METHOD[r.method] ?? 'bg-slate-100 text-slate-600']">
                      {{ r.method }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 font-mono text-slate-700 dark:text-slate-300">{{ r.path }}</td>
                  <td class="px-4 py-2.5 font-mono text-purple-600 dark:text-purple-400 hidden md:table-cell">{{ r.name }}</td>
                  <td class="px-4 py-2.5 font-mono text-blue-600 dark:text-blue-400 hidden xl:table-cell">{{ r.component }}</td>
                  <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ r.desc }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>

        <!-- ── MODEL ────────────────────────────── -->
        <template v-else-if="currentDocs.type === 'model'">
          <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Model: {{ currentDocs.title }}</h1>
            <div class="flex items-center gap-3 mt-2">
              <code class="text-sm font-mono px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded">
                table: {{ currentDocs.table }}
              </code>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">{{ currentDocs.description }}</p>
          </div>

          <!-- Fields Table -->
          <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden mb-4">
            <div class="bg-slate-50 dark:bg-slate-800 px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
              <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kolom / Fields</p>
            </div>
            <table class="w-full text-xs">
              <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-left border-b border-slate-100 dark:border-slate-700">
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 w-36">Nama</th>
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 hidden sm:table-cell">Tipe</th>
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 w-20 text-center">Nullable</th>
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                <tr
                  v-for="f in currentDocs.fields"
                  :key="f.name"
                  class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                >
                  <td class="px-4 py-2.5">
                    <span class="font-mono font-semibold text-blue-600 dark:text-blue-400">{{ f.name }}</span>
                    <span v-if="f.pk" class="ml-1.5 text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-1 py-0.5 rounded">PK</span>
                  </td>
                  <td class="px-4 py-2.5 font-mono text-slate-500 dark:text-slate-400 hidden sm:table-cell">{{ f.type }}</td>
                  <td class="px-4 py-2.5 text-center">
                    <span :class="f.nullable ? 'text-amber-500 dark:text-amber-400 font-medium' : 'text-slate-300 dark:text-slate-600'">
                      {{ f.nullable ? '✓' : '—' }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ f.desc }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Relations -->
          <div v-if="currentDocs.relations?.length" class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
            <div class="bg-slate-50 dark:bg-slate-800 px-4 py-2.5 border-b border-slate-200 dark:border-slate-700">
              <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Relasi Eloquent</p>
            </div>
            <table class="w-full text-xs">
              <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-left border-b border-slate-100 dark:border-slate-700">
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 w-32">Tipe</th>
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 w-44">Model</th>
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400 w-28 hidden sm:table-cell">FK</th>
                  <th class="px-4 py-2.5 font-semibold text-slate-500 dark:text-slate-400">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                <tr
                  v-for="r in currentDocs.relations"
                  :key="r.related"
                  class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                >
                  <td class="px-4 py-2.5 font-mono text-purple-600 dark:text-purple-400">{{ r.type }}</td>
                  <td class="px-4 py-2.5 font-mono text-blue-600 dark:text-blue-400">{{ r.related }}</td>
                  <td class="px-4 py-2.5 font-mono text-slate-400 dark:text-slate-500 hidden sm:table-cell">{{ r.fk }}</td>
                  <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ r.desc }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>

        <!-- ── WORKFLOW ──────────────────────────── -->
        <template v-else-if="currentDocs.type === 'workflow'">
          <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ currentDocs.title }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ currentDocs.description }}</p>
          </div>

          <div class="max-w-2xl">
            <!-- Steps -->
            <div v-for="(step, idx) in currentDocs.steps" :key="step.status" class="flex gap-4">
              <!-- Timeline line + dot -->
              <div class="flex flex-col items-center flex-shrink-0">
                <div :class="['w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold z-10 shadow-sm', STEP_COLOR[step.color].dot]">
                  {{ idx + 1 }}
                </div>
                <div
                  v-if="idx < currentDocs.steps.length - 1"
                  class="w-0.5 flex-1 bg-slate-200 dark:bg-slate-700 my-1 min-h-8"
                ></div>
              </div>

              <!-- Content -->
              <div class="pb-8 flex-1 min-w-0">
                <div :class="['inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold mb-2', STEP_COLOR[step.color].badge]">
                  Status {{ step.status }} — {{ step.label }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-0.5">
                  <span class="font-semibold text-slate-600 dark:text-slate-300">Aktor:</span> {{ step.actor }}
                </p>
                <p class="text-sm text-slate-700 dark:text-slate-300 mb-2">{{ step.action }}</p>
                <code v-if="step.route !== '—'" class="text-xs font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2.5 py-1.5 rounded-lg inline-block">
                  {{ step.route }}
                </code>
              </div>
            </div>

            <!-- Rejected branch -->
            <div v-if="currentDocs.rejectedStep" class="flex gap-4 mt-2">
              <div class="w-9 flex-shrink-0 flex items-start justify-center pt-2">
                <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
              </div>
              <div class="flex-1 border-l-2 border-dashed border-red-300 dark:border-red-700/50 pl-4 py-3 ml-3">
                <div :class="['inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold mb-2', STEP_COLOR['red'].badge]">
                  Status {{ currentDocs.rejectedStep.status }} — {{ currentDocs.rejectedStep.label }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-0.5">
                  <span class="font-semibold text-slate-600 dark:text-slate-300">Aktor:</span> {{ currentDocs.rejectedStep.actor }}
                </p>
                <p class="text-sm text-slate-700 dark:text-slate-300 mb-2">{{ currentDocs.rejectedStep.action }}</p>
                <code class="text-xs font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2.5 py-1.5 rounded-lg inline-block">
                  {{ currentDocs.rejectedStep.route }}
                </code>
              </div>
            </div>
          </div>
        </template>

      </div>
    </div>
  </AdminLayout>
</template>
