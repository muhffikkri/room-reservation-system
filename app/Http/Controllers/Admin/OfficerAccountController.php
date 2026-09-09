<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CreateOfficerRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Kelola akun petugas oleh admin (US-13, BR-15).
 *
 * Alur daftar-formulir-simpan tinggal di base; kelas ini hanya menyatakan
 * identitas modul petugas dan mempertahankan nama spec.
 */
class OfficerAccountController extends BaseAccountController
{
    protected function role(): string
    {
        return 'petugas';
    }

    protected function viewPrefix(): string
    {
        return 'admin.petugas';
    }

    protected function viewVariable(): string
    {
        return 'officers';
    }

    protected function indexRoute(): string
    {
        return 'admin.petugas.index';
    }

    protected function createdMessage(string $email): string
    {
        return "Akun petugas {$email} berhasil dibuat dan langsung aktif.";
    }

    public function store(CreateOfficerRequest $request): RedirectResponse
    {
        return $this->persist($request);
    }
}
