<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStep4Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_price' => [
                'required',
                'numeric',
                'min:10',
                'max:50000',
                'decimal:0,2',
            ],
            'decision_type' => [
                'required',
                'in:keep,give',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'target_price.required' => 'Zielpreis ist erforderlich',
            'target_price.numeric' => 'Zielpreis muss eine Zahl sein',
            'target_price.min' => 'Zielpreis muss mindestens 10 EUR sein',
            'target_price.max' => 'Zielpreis darf maximal 50.000 EUR sein',
            
            'decision_type.required' => 'Bitte treffen Sie eine Entscheidung',
            'decision_type.in' => 'Ungültige Entscheidung',
        ];
    }
}