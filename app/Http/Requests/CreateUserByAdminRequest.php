<?php

namespace App\Http\Requests;

/**
 * Validasi form pembuatan akun pengguna oleh admin (§7.1, US-14).
 *
 * Isi aturan tinggal di base; kelas ini mempertahankan nama spec agar
 * rute, controller, dan dokumen tetap konsisten.
 */
class CreateUserByAdminRequest extends AdminAccountRequest
{
    //
}
