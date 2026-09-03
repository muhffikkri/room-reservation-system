<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOfficerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Kelola akun petugas oleh admin (US-13, BR-15).
 *
 * Petugas tidak pernah bisa registrasi mandiri, sehingga controller ini
 * menjadi satu-satunya pintu pembuatan akun petugas selain seeder.
 * Akun yang dibuat langsung aktif tanpa verifikasi karena admin sendiri
 * yang menjamin identitas pemiliknya (BR-14 hanya berlaku untuk
 * registrasi mandiri).
 */
class OfficerAccountController extends Controller
{
    public function index(): View
    {
        $officers = User::query()
            ->where('role', 'petugas')
            ->orderBy('name')
            ->get();

        return view('admin.petugas.index', ['officers' => $officers]);
    }

    public function create(): View
    {
        return view('admin.petugas.create');
    }

    public function store(CreateOfficerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Sistem mengunci role dan status di sini dan mengabaikan input
        // dengan nama sama, sehingga form tidak bisa disalahgunakan untuk
        // membuat akun admin atau mengaktifkan akun secara ilegal (BR-15).
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'identity' => $validated['identity'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => 'petugas',
            'account_status' => 'aktif',
        ]);

        return redirect()
            ->route('admin.petugas.index')
            ->with('success', "Akun petugas {$validated['email']} berhasil dibuat dan langsung aktif.");
    }
}
