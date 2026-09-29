<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi pembatalan reservasi oleh petugas (§7.1, BR-9).
 *
 * Alasan wajib diisi minimal 10 karakter dan tersimpan sebagai
 * cancel_reason agar tampil di detail reservasi.
 */
class CancelReservationOfficerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPetugas() === true
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
            'cancel_reason' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }

    /**
     * Normalisasi SEBELUM validasi.
     *
     *	min:10 dihitung atas masukan mentah, sehingga '<b></b><i></i><u></u>'
     * (28 karakter) lolos sebagai alasan pembatalan yang sah lalu menjadi
     * kosong setelah strip_tags. Bersihkan lebih dulu agar aturannya mengukur
     * teks yang benar-benar akan disimpan.
     */
    protected function prepareForValidation(): void
    {
        $reason = $this->input('cancel_reason');

        $this->merge([
            'cancel_reason' => is_string($reason) ? strip_tags($reason) : $reason,
        ]);
    }
}
