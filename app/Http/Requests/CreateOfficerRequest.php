<?php

namespace App\Http\Requests;

/**
 * Validasi form pembuatan akun petugas oleh admin (§7.1, US-13).
 *
 * Isi aturan tinggal di base; kelas ini mempertahankan nama spec agar
 * rute, controller, dan dokumen tetap konsisten.
 */
class CreateOfficerRequest extends AdminAccountRequest
{
    //
}
