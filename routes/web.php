<?php

use App\Http\Controllers\Admin\AccountVerificationController;
use App\Http\Controllers\Admin\OfficerAccountController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Officer\DashboardController as OfficerDashboardController;
use App\Http\Controllers\Officer\ReportController as OfficerReportController;
use App\Http\Controllers\ReportController;
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

    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/baru', [ReportController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [ReportController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{report}', [ReportController::class, 'show'])->name('laporan.show');
});

Route::middleware(['auth', 'active', 'role:petugas,admin'])->prefix('petugas')->group(function (): void {
    Route::get('/', OfficerDashboardController::class)->name('petugas.dashboard');
    Route::get('/laporan', [OfficerReportController::class, 'index'])->name('petugas.laporan.index');
    Route::get('/laporan/{report}', [OfficerReportController::class, 'show'])->name('petugas.laporan.show');
    Route::patch('/laporan/{report}/status', [OfficerReportController::class, 'updateStatus'])->name('petugas.laporan.status');
    Route::patch('/laporan/{report}/fasilitas-status', [OfficerReportController::class, 'toggleFacilityStatus'])->name('petugas.laporan.fasilitas-status');
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::get('/pengguna/verifikasi', [AccountVerificationController::class, 'index'])
        ->name('admin.pengguna.verifikasi');
    Route::patch('/pengguna/{user}/verifikasi', [AccountVerificationController::class, 'verify'])
        ->name('admin.pengguna.verify');
    Route::patch('/pengguna/{user}/tolak', [AccountVerificationController::class, 'reject'])
        ->name('admin.pengguna.reject');
    Route::get('/pengguna', [UserAccountController::class, 'index'])
        ->name('admin.pengguna.index');
    Route::get('/pengguna/create', [UserAccountController::class, 'create'])
        ->name('admin.pengguna.create');
    Route::post('/pengguna', [UserAccountController::class, 'store'])
        ->name('admin.pengguna.store');
    Route::get('/petugas', [OfficerAccountController::class, 'index'])
        ->name('admin.petugas.index');
    Route::get('/petugas/create', [OfficerAccountController::class, 'create'])
        ->name('admin.petugas.create');
    Route::post('/petugas', [OfficerAccountController::class, 'store'])
        ->name('admin.petugas.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', LogoutController::class)->name('logout');
});
