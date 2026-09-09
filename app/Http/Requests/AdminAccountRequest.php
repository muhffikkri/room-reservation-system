<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Base validasi pembuatan akun oleh admin (§7.1).
 *
 * Dua request spec (petugas dan pengguna) berbagi isi yang identik,
 * sehingga aturannya hidup di sini dan keduanya tinggal memakai nama.
 * Controller masing-masing tetap memaksa role dan status di server,
 * sehingga form tidak bisa disalahgunakan (BR-15).
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
        return $this->user()?->role === 'admin';
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
            'identity' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
