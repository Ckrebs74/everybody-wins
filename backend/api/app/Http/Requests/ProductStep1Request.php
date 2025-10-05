<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SCHRITT 1: Kategorie & Typ
 */
class ProductStep1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'condition' => 'required|in:new,like_new,good,acceptable',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Bitte wählen Sie eine Kategorie aus',
            'category_id.exists' => 'Ungültige Kategorie',
            'condition.required' => 'Bitte wählen Sie einen Zustand aus',
            'condition.in' => 'Ungültiger Zustand',
        ];
    }
}