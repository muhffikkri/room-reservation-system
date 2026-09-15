<?php

namespace App\Http\Requests;

/**
 * Validasi form pembuatan akun admin oleh admin (§7.1, BR-17).
 *
 * Isi aturan tinggal di base; kelas ini mempertahankan nama spec agar
 * rute, controller, dan dokumen tetap konsisten.
 */
class CreateAdminRequest extends AdminAccountRequest
{
    //
}
