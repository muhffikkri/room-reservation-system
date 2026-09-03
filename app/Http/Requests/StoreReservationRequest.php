<?php

namespace App\Http\Requests;

use App\Rules\SlotAvailable;
use App\Rules\SlotTimeValid;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facility_id' => ['required', 'exists:facilities,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i', new SlotTimeValid],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // SlotAvailable butuh user yang login dan seluruh isi form,
            // sehingga sistem menjalankannya di sini, bukan di rules().
            // Sistem melewatinya jika validasi dasar sudah gagal agar
            // pesan error tidak bertumpuk.
            $rule = new SlotAvailable($this->user()->id);
            $rule->setData($this->all());
            $rule->validate('facility_id', $this->input('facility_id'), function (string $message) use ($validator): void {
                $validator->errors()->add('facility_id', $message);
            });
        });
    }
}
