<?php

use App\Http\Controllers\Admin\AccountVerificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\OfficerAccountController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Officer\DashboardController as OfficerDashboardController;
use App\Http\Controllers\Officer\ReservationController as OfficerReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/fasilitas', [FacilityController::class, 'index'])->name('fasilitas.index');
Route::get('/fasilitas/{facility}', [FacilityController::class, 'show'])->name('fasilitas.show');
Route::get('/fasilitas/{facility}/jadwal', [FacilityController::class, 'jadwal'])->name('fasilitas.jadwal');

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
    Route::get('/', AdminDashboardController::class)
        ->name('admin.dashboard');
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
    Route::get('/fasilitas', [AdminFacilityController::class, 'index'])
        ->name('admin.fasilitas.index');
    Route::get('/fasilitas/create', [AdminFacilityController::class, 'create'])
        ->name('admin.fasilitas.create');
    Route::post('/fasilitas', [AdminFacilityController::class, 'store'])
        ->name('admin.fasilitas.store');
    Route::get('/fasilitas/{facility}/edit', [AdminFacilityController::class, 'edit'])
        ->name('admin.fasilitas.edit');
    Route::put('/fasilitas/{facility}', [AdminFacilityController::class, 'update'])
        ->name('admin.fasilitas.update');
    Route::patch('/fasilitas/{facility}/nonaktifkan', [AdminFacilityController::class, 'deactivate'])
        ->name('admin.fasilitas.deactivate');
    Route::patch('/fasilitas/{facility}/aktifkan', [AdminFacilityController::class, 'activate'])
        ->name('admin.fasilitas.activate');
});

Route::middleware(['auth', 'active', 'role:petugas,admin'])->prefix('petugas')->group(function (): void {
    Route::get('/', OfficerDashboardController::class)->name('petugas.dashboard');
    Route::get('/reservasi', [OfficerReservationController::class, 'index'])->name('petugas.reservasi.index');
    Route::get('/reservasi/{reservation}', [OfficerReservationController::class, 'show'])->name('petugas.reservasi.show');
    Route::post('/reservasi/{reservation}/approve', [OfficerReservationController::class, 'approve'])
        ->name('petugas.reservasi.approve');
    Route::post('/reservasi/{reservation}/reject', [OfficerReservationController::class, 'reject'])
        ->name('petugas.reservasi.reject');
    Route::post('/reservasi/{reservation}/cancel', [OfficerReservationController::class, 'cancel'])
        ->name('petugas.reservasi.cancel');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', LogoutController::class)->name('logout');
});
