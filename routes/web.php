<?php

use App\Http\Controllers\AdminProjectsController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\Frontend\KamusController;
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
Route::get('/kamus', [KamusController::class, 'index'])->name('kamus.public');

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
|
| Routes yang memerlukan authentication untuk mengakses admin panel
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard Admin
    Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

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
        Route::get('/kamus-validate', [AdminKamusController::class, 'validate'])->name('kamus.validate');
        Route::patch('/kamus/{kamus}/approve', [AdminKamusController::class, 'approve'])->name('kamus.approve');
        Route::patch('/kamus/{kamus}/reject', [AdminKamusController::class, 'reject'])->name('kamus.reject');
        Route::post('/kamus/bulk-delete', [AdminKamusController::class, 'bulkDelete'])->name('kamus.bulk-delete');
        Route::post('/kamus/bulk-approve', [AdminKamusController::class, 'bulkApprove'])->name('kamus.bulk-approve');
        Route::post('/kamus/bulk-reject', [AdminKamusController::class, 'bulkReject'])->name('kamus.bulk-reject');

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

        // Gambar Galeri
        Route::get('/galeri', [GambarGaleriController::class, 'index'])->name('galeri.index');
        Route::get('/galeri/create', [GambarGaleriController::class, 'create'])->name('galeri.create');
        Route::post('/galeri', [GambarGaleriController::class, 'store'])->name('galeri.store');
        Route::get('/galeri/{galeri}', [GambarGaleriController::class, 'show'])->name('galeri.show');
        Route::get('/galeri/{galeri}/edit', [GambarGaleriController::class, 'edit'])->name('galeri.edit');
        Route::put('/galeri/{galeri}', [GambarGaleriController::class, 'update'])->name('galeri.update');
        Route::delete('/galeri/{galeri}', [GambarGaleriController::class, 'destroy'])->name('galeri.destroy');

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