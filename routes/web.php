<?php

use App\Http\Controllers\AdminProjectsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\PageProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminKamusController;   
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
Route::get('/', function () {
    return redirect()->route('login');
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

    // Dashboard Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';