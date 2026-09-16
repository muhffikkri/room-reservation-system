<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPengguna() === true
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
            'facility_id' => [
                'required',
                Rule::exists('facilities', 'id')->where('status', 'aktif'),
            ],
            'category' => ['required', Rule::in(['kerusakan_alat', 'listrik', 'kebersihan', 'sarana_prasarana', 'lainnya'])],
            'description' => ['required', 'string', 'min:15', 'max:2000'],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
                'dimensions:max_width=6000,max_height=6000',
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'facility_id.required' => 'Fasilitas wajib dipilih.',
            'facility_id.exists' => 'Fasilitas yang dipilih tidak valid.',
            'category.required' => 'Kategori kerusakan wajib dipilih.',
            'category.in' => 'Kategori kerusakan tidak valid.',
            'description.required' => 'Deskripsi kerusakan wajib diisi.',
            'description.min' => 'Deskripsi kerusakan minimal 15 karakter.',
            'description.max' => 'Deskripsi kerusakan maksimal 2000 karakter.',
            'photo.image' => 'Berkas foto harus berupa gambar.',
            'photo.mimes' => 'Format foto harus jpg, jpeg, atau png.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'photo.dimensions' => 'Dimensi foto maksimal 6000 x 6000 piksel.',
        ];
    }
}
