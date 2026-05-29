<?php

use App\Http\Controllers\Api\V1\ArtikelController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\KamusController;
use App\Http\Controllers\Api\V1\GaleriController;
use App\Http\Controllers\Api\V1\KontakController;
use App\Http\Controllers\Api\V1\KuisController;
use App\Http\Controllers\Api\V1\PembelajaranController;
use App\Http\Controllers\Api\V1\PenerjemahController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Public (tidak memerlukan autentikasi)
|--------------------------------------------------------------------------
|
| Semua route di sini menggunakan prefix /api secara otomatis dari
| bootstrap/app.php dan sudah dilindungi rate limiter bawaan Laravel.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Kontak ───────────────────────────────────────────────────────────
    Route::post('/kontak', [KontakController::class, 'store'])->middleware('throttle:10,1')->name('kontak.store');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/kontak',      [KontakController::class, 'index'])->name('kontak.index');
        Route::get('/kontak/{id}', [KontakController::class, 'show'])->name('kontak.show')->where('id', '[0-9]+');
    });

    // ── Auth ─────────────────────────────────────────────────────────────
    Route::post('/login',  [AuthController::class, 'login'])->middleware('throttle:10,1')->name('auth.login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user',    [AuthController::class, 'user'])->name('auth.user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    // ── Galeri ───────────────────────────────────────────────────────────
    Route::prefix('galeri')->name('galeri.')->group(function () {
        Route::get('/',          [GaleriController::class, 'index'])    ->name('index');
        Route::get('/kategori',  [GaleriController::class, 'kategori']) ->name('kategori');
        Route::get('/{id}',      [GaleriController::class, 'show'])     ->name('show')
            ->where('id', '[0-9]+');
    });

    // ── Kuis ─────────────────────────────────────────────────────────────
    Route::prefix('kuis')->name('kuis.')->group(function () {
        // GET — data & hasil
        Route::get('/',                          [KuisController::class, 'index'])     ->name('index');
        Route::get('/{slug}',                    [KuisController::class, 'show'])      ->name('show')     ->where('slug', '[a-z0-9-]+');
        Route::get('/{slug}/soal',               [KuisController::class, 'soal'])      ->name('soal')     ->where('slug', '[a-z0-9-]+');
        Route::get('/{slug}/hasil/{attempt_id}', [KuisController::class, 'hasil'])     ->name('hasil')    ->where(['slug' => '[a-z0-9-]+', 'attempt_id' => '[0-9]+']);
        Route::get('/{slug}/hasil-duel',         [KuisController::class, 'hasilDuel']) ->name('hasil-duel')->where('slug', '[a-z0-9-]+');
        // POST — actions (limit lebih ketat agar tidak bisa spam attempt)
        Route::post('/{slug}/mulai',             [KuisController::class, 'mulai'])      ->name('mulai')      ->where('slug', '[a-z0-9-]+')->middleware('throttle:20,1');
        Route::post('/{slug}/mulai-duel',        [KuisController::class, 'mulaiDuel'])  ->name('mulai-duel') ->where('slug', '[a-z0-9-]+')->middleware('throttle:20,1');
        Route::post('/{slug}/submit',            [KuisController::class, 'submit'])     ->name('submit')     ->where('slug', '[a-z0-9-]+');
        Route::post('/{slug}/submit-duel',       [KuisController::class, 'submitDuel']) ->name('submit-duel')->where('slug', '[a-z0-9-]+');
    });

    // ── Pembelajaran ─────────────────────────────────────────────────────
    Route::prefix('pembelajaran')->name('pembelajaran.')->group(function () {
        Route::get('/',          [PembelajaranController::class, 'index'])    ->name('index');
        Route::get('/kategori',  [PembelajaranController::class, 'kategori']) ->name('kategori');
        Route::get('/{slug}',    [PembelajaranController::class, 'show'])     ->name('show')
            ->where('slug', '[a-z0-9-]+');
    });

    // ── Penerjemah ───────────────────────────────────────────────────────
    // Rate limit lebih ketat karena bisa memanggil Gemini AI (biaya eksternal)
    Route::prefix('penerjemah')->name('penerjemah.')->middleware('throttle:30,1')->group(function () {
        Route::post('/',    [PenerjemahController::class, 'translate'])    ->name('translate');
        Route::post('/ocr', [PenerjemahController::class, 'extractText']) ->name('ocr');
    });

    // ── Kamus ────────────────────────────────────────────────────────────
    Route::prefix('kamus')->name('kamus.')->group(function () {
        Route::get('/',      [KamusController::class, 'index']) ->name('index');
        Route::get('/{id}',  [KamusController::class, 'show'])  ->name('show')
            ->where('id', '[0-9]+');
    });

    // ── Artikel ──────────────────────────────────────────────────────────
    Route::prefix('artikel')->name('artikel.')->group(function () {
        Route::get('/',          [ArtikelController::class, 'index'])    ->name('index');
        Route::get('/kategori',  [ArtikelController::class, 'kategori']) ->name('kategori');
        Route::get('/{slug}',    [ArtikelController::class, 'show'])     ->name('show')
            ->where('slug', '[a-z0-9-]+');
    });

});
