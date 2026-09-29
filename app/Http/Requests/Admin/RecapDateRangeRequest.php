<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class RecapDateRangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Kedua batas bersifat berpasangan: hanya end_date menghasilkan
            // rentang terbalik (start > end) dan max_possible_hours negatif.
            'start_date' => ['nullable', 'date', 'before_or_equal:today', 'required_with:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:today', 'required_with:start_date'],
        ];
    }

    /**
     * Get validated dates as Carbon instances.
     */
    public function getValidatedDates(): array
    {
        $validated = $this->validated();

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        return [$startDate, $endDate];
    }

    /**
     * Get default dates when not provided.
     */
    public function getDefaultDates(): array
    {
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(30)->startOfDay();

        return [$startDate, $endDate];
    }

    /**
     * Get date strings for form repopulation.
     */
    public function getDateStrings(): array
    {
        [$startDate, $endDate] = $this->getValidatedDates();

        return [
            'startDate' => $startDate?->toDateString() ?? Carbon::now()->subDays(30)->toDateString(),
            'endDate' => $endDate?->toDateString() ?? Carbon::now()->toDateString(),
        ];
    }
}
