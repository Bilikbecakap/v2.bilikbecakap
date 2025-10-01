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

Route::get('/', [LandingPageController::class, 'index'])->name('landing');

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

    // Project Management

    // Role Management
    Route::resource('roles', RoleController::class);

    // Permission Management
    Route::resource('permissions', PermissionController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('role:super-admin')->name('activity-logs.index');
    Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show'])->middleware('role:super-admin')->name('activity-logs.show');

    // Data Master Routes
    Route::get('/data-master', function () {return Inertia::render('DataMaster/Index');})->middleware('permission:view data master')->name('data-master.index');

});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';