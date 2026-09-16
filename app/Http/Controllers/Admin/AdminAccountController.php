<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CreateAdminRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Kelola akun admin oleh admin (BR-17).
 *
 * Sistem boleh memiliki lebih dari satu admin; admin aktif dapat membuat
 * admin baru tanpa jalur registrasi mandiri. Alur daftar-formulir-simpan
 * tinggal di base; kelas ini hanya menyatakan identitas modul admin.
 */
class AdminAccountController extends BaseAccountController
{
    protected function role(): string
    {
        return 'admin';
    }

    protected function viewPrefix(): string
    {
        return 'admin.admin';
    }

    protected function viewVariable(): string
    {
        return 'admins';
    }

    protected function indexRoute(): string
    {
        return 'admin.admin.index';
    }

    protected function createdMessage(string $email): string
    {
        return "Akun admin {$email} berhasil dibuat dan langsung aktif.";
    }

    public function store(CreateAdminRequest $request): RedirectResponse
    {
        return $this->persist($request);
    }
}
