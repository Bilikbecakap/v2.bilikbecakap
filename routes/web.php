<?php

use App\Http\Controllers\AdminProjectsController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\Frontend\KamusController;
use App\Http\Controllers\Frontend\PenerjemahController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PembelajaranController;
use App\Http\Controllers\Frontend\KuisController;
use App\Http\Controllers\Frontend\KontakController;
use App\Http\Controllers\Frontend\GaleriController;
use App\Http\Controllers\Frontend\ChatbotController;
use App\Http\Controllers\Frontend\OcrController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminKamusController; 
use App\Http\Controllers\DatasetTranslateController;
use App\Http\Controllers\TranslateController;
use App\Http\Controllers\MasterModulController;
use App\Http\Controllers\MasterArtikelController;
use App\Http\Controllers\MasterGambarController;
use App\Http\Controllers\MasterMediaMusicQuizController;
use App\Http\Controllers\ArtikelController;  
use App\Http\Controllers\ModulPembelajaranController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\GambarGaleriController;
use App\Http\Controllers\KomentarController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes (Landing Page)
|--------------------------------------------------------------------------
|
| Routes yang dapat diakses tanpa login
|
*/

// Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/kamus', [KamusController::class, 'index'])->name('kamus.public');
Route::get('/penerjemah', [PenerjemahController::class, 'index'])->name('penerjemah');
Route::post('/penerjemah/process', [PenerjemahController::class, 'translate'])->name('penerjemah.process');
Route::get('/pembelajaran', [PembelajaranController::class, 'index'])->name('pembelajaran');
Route::get('/pembelajaran/{slug}', [PembelajaranController::class, 'show'])->name('pembelajaran.show');
Route::get('/tentang', function () {return Inertia::render('Frontend/Tentang');})->name('tentang');
Route::post('/komentar', [KomentarController::class, 'store'])->name('komentar.store');
Route::get('/artikel', [BlogController::class, 'daftar'])->name('artikel.index');
Route::get('/artikel/{slug}', [BlogController::class, 'baca'])->name('artikel.show');

Route::get('/kuis', [KuisController::class, 'index'])->name('kuis.index');
Route::get('/kuis/{slug}', [KuisController::class, 'show'])->name('kuis.show');
Route::post('/kuis/{slug}/begin', [KuisController::class, 'begin'])->name('quiz-attempt.begin');
Route::get('/kuis/{slug}/do', [KuisController::class, 'quiz'])->name('quiz-attempt.quiz');
Route::post('/kuis/{slug}/do/submit', [KuisController::class, 'submit'])->name('quiz-attempt.submit');
Route::get('/kuis/{slug}/result', [KuisController::class, 'result'])->name('quiz-attempt.result');

Route::post('/kuis/{slug}/begin-duel', [KuisController::class, 'beginDuel'])->name('quiz-attempt.begin-duel');
Route::get('/kuis/{slug}/duel', [KuisController::class, 'duel'])->name('quiz-attempt.duel');
Route::post('/kuis/{slug}/submit-duel', [KuisController::class, 'submitDuel'])->name('quiz-attempt.submit-duel');
Route::get('/kuis/{slug}/duel-result', [KuisController::class, 'duelResult'])->name('quiz-attempt.duel-result');

Route::get('/kontak', [KontakController::class, 'index'])->name('kontak.index');
Route::post('/kontak', [KontakController::class, 'store'])->name('kontak.store');
Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri.public');

//ai
Route::post('/penerjemah/extract-text', [PenerjemahController::class, 'extractText'])->name('penerjemah.extractText');
Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');
Route::get('/chatbot/context-options', [ChatbotController::class, 'getContextOptions'])->name('chatbot.context');

Route::get('/test-gemini', function() {
    $service = new \App\Services\GeminiService();
    
    return response()->json([
        'api_key_exists' => !empty(env('GEMINI_API_KEY')),
        'connection_test' => $service->testConnection(),
        'database_stats' => $service->getDatabaseStats(),
    ]);
});

Route::get('/serve-media/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path("app/public/{$folder}/{$filename}");
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $mimeTypes = [
        'kamus-audio' => 'audio/mpeg',
        'quizzes/music' => 'audio/mpeg',
    ];
    
    $contentType = $mimeTypes[$folder] ?? 'audio/mpeg';
    
    return response()->file($path, [
        'Content-Type' => $contentType,
        'Cache-Control' => 'public, max-age=86400',
        'Accept-Ranges' => 'bytes'
    ]);
})->where(['folder' => '.*', 'filename' => '.*']);

Route::fallback(function () {
    return inertia('Errors/404')->toResponse(request())->setStatusCode(404);
});


/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
|
| Routes yang memerlukan authentication untuk mengakses admin panel
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // User Management
    Route::resource('users', UserController::class);

    // Role Management
    Route::resource('roles', RoleController::class);

    // Permission Management
    Route::resource('permissions', PermissionController::class)->only(['index', 'create', 'store', 'destroy']);

    // Activity Management
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('role:super-admin')->name('activity-logs.index');
    Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show'])->middleware('role:super-admin')->name('activity-logs.show');

    // Data Master Routes
    Route::get('/data-master', function () {return Inertia::render('DataMaster/Index');})->middleware('permission:view data master')->name('data-master.index');

    // Master Artikel Routes  
    Route::prefix('data-master')->name('data-master.')->group(function () {
        Route::resource('artikel', MasterArtikelController::class)->except(['show']);
        Route::resource('modul', MasterModulController::class)->except(['show']);
        Route::resource('gambar', MasterGambarController::class)->except(['show']);
        Route::resource('quiz-music', MasterMediaMusicQuizController::class)->except(['show']);
    });

    Route::prefix('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Dataset Translate
        Route::get('/dataset-translate', [DatasetTranslateController::class, 'index'])->name('dataset-translate.index');
        Route::get('/dataset-translate/create', [DatasetTranslateController::class, 'create'])->name('dataset-translate.create');
        Route::post('/dataset-translate', [DatasetTranslateController::class, 'store'])->name('dataset-translate.store');
        Route::get('/dataset-translate/{datasetTranslate}/edit', [DatasetTranslateController::class, 'edit'])->name('dataset-translate.edit');
        Route::put('/dataset-translate/{datasetTranslate}', [DatasetTranslateController::class, 'update'])->name('dataset-translate.update');
        Route::delete('/dataset-translate/{datasetTranslate}', [DatasetTranslateController::class, 'destroy'])->name('dataset-translate.destroy');
        Route::post('/dataset-translate/bulk-delete', [DatasetTranslateController::class, 'bulkDelete'])->name('dataset-translate.bulk-delete');
        Route::post('/dataset-translate/import', [DatasetTranslateController::class, 'import'])->name('dataset-translate.import');
        Route::get('/dataset-translate/export', [DatasetTranslateController::class, 'export'])->name('dataset-translate.export');
        Route::get('/dataset-translate/statistics', [DatasetTranslateController::class, 'statistics'])->name('dataset-translate.statistics');

        //Kamus Management
        Route::get('/kamus', [AdminKamusController::class, 'index'])->name('kamus.index');
        Route::get('/kamus/create', [AdminKamusController::class, 'create'])->name('kamus.create');
        Route::post('/kamus', [AdminKamusController::class, 'store'])->name('kamus.store');
        Route::get('/kamus/{kamus}/edit', [AdminKamusController::class, 'edit'])->name('kamus.edit');
        Route::put('/kamus/{kamus}', [AdminKamusController::class, 'update'])->name('kamus.update');
        Route::delete('/kamus/{kamus}', [AdminKamusController::class, 'destroy'])->name('kamus.destroy');
        Route::get('/kamus/{kamus}/tinjauan', [AdminKamusController::class, 'tinjauan'])->name('kamus.tinjauan');
        Route::post('/kamus/{kamus}/validasi', [AdminKamusController::class, 'validasiKamus'])->name('kamus.validasi');
        Route::post('/kamus/{kamus}/finalisasi', [AdminKamusController::class, 'finalisasiKamus'])->name('kamus.finalisasi');
        Route::patch('/kamus/{kamus}/approve', [AdminKamusController::class, 'approve'])->name('kamus.approve');
        Route::patch('/kamus/{kamus}/reject', [AdminKamusController::class, 'reject'])->name('kamus.reject');

        // Artikel Management
        Route::get('/artikel', [ArtikelController::class, 'index'])->name('artikel.index');
        Route::get('/artikel/create', [ArtikelController::class, 'create'])->name('artikel.create');
        Route::post('/artikel', [ArtikelController::class, 'store'])->name('artikel.store');
        Route::get('/artikel/{artikel}', [ArtikelController::class, 'show'])->name('artikel.show');
        Route::get('/artikel/{artikel}/edit', [ArtikelController::class, 'edit'])->name('artikel.edit');
        Route::put('/artikel/{artikel}', [ArtikelController::class, 'update'])->name('artikel.update');
        Route::delete('/artikel/{artikel}', [ArtikelController::class, 'destroy'])->name('artikel.destroy');
        Route::patch('/artikel/{artikel}/approve', [ArtikelController::class, 'approve'])->name('artikel.approve');
        Route::patch('/artikel/{artikel}/reject', [ArtikelController::class, 'reject'])->name('artikel.reject');
        Route::post('/artikel/upload-image', [ArtikelController::class, 'uploadImage'])->name('artikel.upload-image');
        Route::get('/artikel/{artikel}/detail', [ArtikelController::class, 'show'])->name('admin.artikel.show');

        // Modul Pembelajaran
        Route::get('/modul-pembelajaran', [ModulPembelajaranController::class, 'index'])->name('modul-pembelajaran.index');
        Route::get('/modul-pembelajaran/create', [ModulPembelajaranController::class, 'create'])->name('modul-pembelajaran.create');
        Route::post('/modul-pembelajaran', [ModulPembelajaranController::class, 'store'])->name('modul-pembelajaran.store');
        Route::get('/modul-pembelajaran/{modulPembelajaran}', [ModulPembelajaranController::class, 'show'])->name('modul-pembelajaran.show');
        Route::get('/modul-pembelajaran/{modulPembelajaran}/edit', [ModulPembelajaranController::class, 'edit'])->name('modul-pembelajaran.edit');
        Route::put('/modul-pembelajaran/{modulPembelajaran}', [ModulPembelajaranController::class, 'update'])->name('modul-pembelajaran.update');
        Route::delete('/modul-pembelajaran/{modulPembelajaran}', [ModulPembelajaranController::class, 'destroy'])->name('modul-pembelajaran.destroy');
        Route::post('/modul-pembelajaran/upload-image', [ModulPembelajaranController::class, 'uploadImage'])->name('modul-pembelajaran.upload-image');

        // Quiz
        Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
        Route::get('/quiz/create', [QuizController::class, 'create'])->name('quiz.create');
        Route::post('/quiz', [QuizController::class, 'store'])->name('quiz.store');
        Route::get('/quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
        Route::get('/quiz/{quiz}/edit', [QuizController::class, 'edit'])->name('quiz.edit');
        Route::put('/quiz/{quiz}', [QuizController::class, 'update'])->name('quiz.update');
        Route::delete('/quiz/{quiz}', [QuizController::class, 'destroy'])->name('quiz.destroy');

        Route::get('/quiz/{quiz}/questions', [QuizQuestionController::class, 'index'])->name('quiz.questions.index');
        Route::get('/quiz/{quiz}/questions/create', [QuizQuestionController::class, 'create'])->name('quiz.questions.create');
        Route::post('/quiz/{quiz}/questions', [QuizQuestionController::class, 'store'])->name('quiz.questions.store');
        Route::get('/quiz/{quiz}/questions/{question}', [QuizQuestionController::class, 'show'])->name('quiz.questions.show');
        Route::get('/quiz/{quiz}/questions/{question}/edit', [QuizQuestionController::class, 'edit'])->name('quiz.questions.edit');
        Route::put('/quiz/{quiz}/questions/{question}', [QuizQuestionController::class, 'update'])->name('quiz.questions.update');
        Route::delete('/quiz/{quiz}/questions/{question}', [QuizQuestionController::class, 'destroy'])->name('quiz.questions.destroy');

        // Quiz Testing Routes
        Route::get('/quiz-test', [QuizAttemptController::class, 'index'])->name('quiz.attempt.index');
        Route::get('/quiz-test/{quiz}/start', [QuizAttemptController::class, 'start'])->name('quiz.attempt.start');
        Route::post('/quiz-test/{quiz}/begin', [QuizAttemptController::class, 'begin'])->name('quiz.attempt.begin');
        Route::get('/quiz-test/{quiz}/{attempt}/quiz', [QuizAttemptController::class, 'quiz'])->name('quiz.attempt.quiz');
        Route::post('/quiz-test/{quiz}/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('quiz.attempt.submit');
        Route::get('/quiz-test/{quiz}/{attempt}/result', [QuizAttemptController::class, 'result'])->name('quiz.attempt.result');
        
        // Quiz History & Detail (untuk admin review)
        Route::get('/quiz/{quiz}/attempts', [QuizAttemptController::class, 'history'])->name('quiz.attempts.history');
        Route::get('/quiz/{quiz}/attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('quiz.attempts.show');
        Route::delete('quiz/{quiz}/attempts/{attempt}', [QuizAttemptController::class, 'destroy'])->name('quiz.attempts.destroy');

        // Gambar Galeri
        Route::get('/galeri', [GambarGaleriController::class, 'index'])->name('galeri.index');
        Route::get('/galeri/create', [GambarGaleriController::class, 'create'])->name('galeri.create');
        Route::post('/galeri', [GambarGaleriController::class, 'store'])->name('galeri.store');
        Route::get('/galeri/{galeri}', [GambarGaleriController::class, 'show'])->name('galeri.show');
        Route::get('/galeri/{galeri}/edit', [GambarGaleriController::class, 'edit'])->name('galeri.edit');
        Route::put('/galeri/{galeri}', [GambarGaleriController::class, 'update'])->name('galeri.update');
        Route::delete('/galeri/{galeri}', [GambarGaleriController::class, 'destroy'])->name('galeri.destroy');

        //Komentar Management
        Route::get('/komentar', [KomentarController::class, 'index'])->name('komentar.index');
        Route::patch('/komentar/{komentar}/approve', [KomentarController::class, 'approve'])->name('komentar.approve');
        Route::patch('/komentar/{komentar}/reject', [KomentarController::class, 'reject'])->name('komentar.reject');
        Route::delete('/komentar/{komentar}', [KomentarController::class, 'destroy'])->name('komentar.destroy');

        Route::get('/kontak-masuk', [KontakController::class, 'adminIndex'])->name('admin.kontak.index');
        Route::get('/kontak-masuk/{kontak}', [KontakController::class, 'adminShow'])->name('admin.kontak.show');
        Route::delete('/kontak-masuk/{kontak}', [KontakController::class, 'adminDestroy'])->name('admin.kontak.destroy');
        Route::post('/kontak-masuk/bulk-delete', [KontakController::class, 'bulkDelete'])->name('admin.kontak.bulk-delete');

    });
    // Translate Management
    Route::get('/translate-test', [TranslateController::class, 'index'])->name('translate.index');
    Route::post('/translate-test/process', [TranslateController::class, 'translate'])->name('translate.process');
    Route::post('/translate-to-english', [TranslateController::class, 'translateToEnglish'])->name('translate.to-english');
    
    // Translate Management - Routes tambahan baru
    Route::get('/translate-test/connection', [TranslateController::class, 'testConnection'])->name('translate.test-connection');
    Route::get('/translate-test/database-stats', [TranslateController::class, 'getDatabaseStats'])->name('translate.database-stats');

});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';