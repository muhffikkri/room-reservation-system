<?php

namespace App\Http\Requests;

use App\Models\Facility;
use App\Rules\MaxWords;
use App\Services\AccountStatusGate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form tambah/edit fasilitas oleh admin (§7.1).
 *
 * Foto bersifat opsional karena saat edit admin boleh menahan foto lama;
 * ketika ada unggahan baru maka foto lama diganti.
 */
class FacilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return AccountStatusGate::mayActAs($this->user(), 'admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['ruang_kelas', 'aula', 'laboratorium', 'alat', 'lapangan'])],
            'location' => ['required', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'description' => ['nullable', 'string', new MaxWords(Facility::MAX_DESCRIPTION_WORDS)],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
                'dimensions:max_width=6000,max_height=6000',
            ],
        ];
    }
}
