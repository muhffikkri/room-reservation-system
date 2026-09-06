<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi penolakan reservasi oleh petugas (§7.1, BR-9).
 *
 * Alasan wajib diisi minimal 10 karakter dan tersimpan sebagai
 * reject_reason agar tampil di detail reservasi.
 */
class RejectReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPetugas() || $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }
}
