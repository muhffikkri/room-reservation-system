<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserByAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Kelola akun pengguna oleh admin (US-14).
 *
 * Controller ini melengkapi halaman verifikasi: verifikasi hanya menampilkan
 * akun pending, sedangkan index di sini memberi admin gambaran lengkap
 * seluruh pengguna beserta statusnya. Akun yang dibuat langsung aktif tanpa
 * verifikasi karena admin sendiri yang menjamin identitas pemiliknya
 * (BR-14 hanya berlaku untuk registrasi mandiri).
 */
class UserAccountController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('role', 'pengguna')
            ->orderBy('name')
            ->get();

        return view('admin.pengguna.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.pengguna.create');
    }

    public function store(CreateUserByAdminRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Sistem mengunci role dan status di sini dan mengabaikan input
        // dengan nama sama, sehingga form tidak bisa disalahgunakan untuk
        // membuat akun admin atau mengubah status secara ilegal (BR-15).
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'identity' => $validated['identity'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => 'pengguna',
            'account_status' => 'aktif',
        ]);

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Akun pengguna {$validated['email']} berhasil dibuat dan langsung aktif.");
    }
}
