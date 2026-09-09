<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAccountRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Base kelola akun oleh admin (US-13, US-14).
 *
 * Dua controller spec (petugas dan pengguna) hanya berbeda role, view,
 * dan pesan; seluruh alur daftar-formulir-simpan hidup di sini. Subclass
 * mempertahankan nama spec agar rute dan dokumen tetap konsisten.
 * Akun yang dibuat langsung aktif tanpa verifikasi karena admin sendiri
 * yang menjamin identitas pemiliknya (BR-14 hanya berlaku untuk
 * registrasi mandiri).
 */
abstract class BaseAccountController extends Controller
{
    abstract protected function role(): string;

    abstract protected function viewPrefix(): string;

    abstract protected function viewVariable(): string;

    abstract protected function indexRoute(): string;

    abstract protected function createdMessage(string $email): string;

    public function index(): View
    {
        $accounts = User::query()
            ->where('role', $this->role())
            ->orderBy('name')
            ->get();

        return view($this->viewPrefix().'.index', [$this->viewVariable() => $accounts]);
    }

    public function create(): View
    {
        return view($this->viewPrefix().'.create');
    }

    /**
     * Simpan akun baru dengan role dan status terkunci.
     *
     * Sistem mengabaikan input dengan nama role/status, sehingga form
     * tidak bisa disalahgunakan untuk membuat akun admin atau mengubah
     * status secara ilegal (BR-15).
     */
    protected function persist(AdminAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'identity' => $validated['identity'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $this->role(),
            'account_status' => 'aktif',
        ]);

        return redirect()
            ->route($this->indexRoute())
            ->with('success', $this->createdMessage($validated['email']));
    }
}
