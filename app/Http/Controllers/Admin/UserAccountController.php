<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CreateUserByAdminRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Kelola akun pengguna oleh admin (US-14).
 *
 * Alur daftar-formulir-simpan tinggal di base; kelas ini hanya menyatakan
 * identitas modul pengguna dan mempertahankan nama spec.
 */
class UserAccountController extends BaseAccountController
{
    protected function role(): string
    {
        return 'pengguna';
    }

    protected function viewPrefix(): string
    {
        return 'admin.pengguna';
    }

    protected function viewVariable(): string
    {
        return 'users';
    }

    protected function indexRoute(): string
    {
        return 'admin.pengguna.index';
    }

    protected function createdMessage(string $email): string
    {
        return "Akun pengguna {$email} berhasil dibuat dan langsung aktif.";
    }

    public function store(CreateUserByAdminRequest $request): RedirectResponse
    {
        return $this->persist($request);
    }
}
