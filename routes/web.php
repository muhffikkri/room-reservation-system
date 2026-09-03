<?php

use App\Http\Controllers\Admin\AccountVerificationController;
use App\Http\Controllers\Admin\OfficerAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::get('/pengguna/verifikasi', [AccountVerificationController::class, 'index'])
        ->name('admin.pengguna.verifikasi');
    Route::patch('/pengguna/{user}/verifikasi', [AccountVerificationController::class, 'verify'])
        ->name('admin.pengguna.verify');
    Route::patch('/pengguna/{user}/tolak', [AccountVerificationController::class, 'reject'])
        ->name('admin.pengguna.reject');
    Route::get('/petugas', [OfficerAccountController::class, 'index'])
        ->name('admin.petugas.index');
    Route::get('/petugas/create', [OfficerAccountController::class, 'create'])
        ->name('admin.petugas.create');
    Route::post('/petugas', [OfficerAccountController::class, 'store'])
        ->name('admin.petugas.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
