<?php

namespace App\Http\Requests;

use App\Support\AccountAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Base validasi pembuatan akun oleh admin (§7.1).
 *
 * Tiga request spec (admin, petugas, dan pengguna) berbagi isi yang
 * identik, sehingga aturannya hidup di sini dan ketiganya tinggal
 * memakai nama. Controller masing-masing tetap memaksa role dan status
 * di server, sehingga form tidak bisa disalahgunakan (BR-15).
 */
abstract class AdminAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sistem mengunci pembuatan akun hanya untuk admin sebagai kunci
        // kedua; rute sudah dijaga middleware role:admin (§10).
        return $this->user()?->isAdmin() === true
            && $this->user()?->isActive() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            // Sistem mewajibkan konfirmasi agar typo tertangkap sebelum hash,
            // karena hash bcrypt tidak bisa dibalik untuk pengecekan ulang (§12.1).
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'identity' => ['required', 'string', 'max:30', 'unique:users,identity'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
        ];
    }

    /**
     * Normalisasi SEBELUM validasi agar unique menangkap duplikat yang
     * hanya beda format (User@X.test vs user@x.test, 0812... vs +62812...).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => AccountAttributes::normalizeEmail($this->input('email')),
            'identity' => AccountAttributes::normalizeIdentity($this->input('identity')),
            'phone' => AccountAttributes::normalizePhone($this->input('phone')),
        ]);
    }
}
