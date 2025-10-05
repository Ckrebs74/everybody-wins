<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStep5Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duration_days' => [
                'required',
                'integer',
                'in:3,5,7,10',
            ],
            'starts_at' => [
                'nullable',
                'date',
                'after_or_equal:now',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'duration_days.required' => 'Verlosungsdauer ist erforderlich',
            'duration_days.integer' => 'Ungültige Dauer',
            'duration_days.in' => 'Dauer muss 3, 5, 7 oder 10 Tage sein',
            
            'starts_at.date' => 'Ungültiges Startdatum',
            'starts_at.after_or_equal' => 'Startdatum muss in der Zukunft liegen',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Falls kein Startdatum angegeben: Sofort starten
        if (!$this->has('starts_at') || empty($this->starts_at)) {
            $this->merge([
                'starts_at' => now(),
            ]);
        }
    }
}