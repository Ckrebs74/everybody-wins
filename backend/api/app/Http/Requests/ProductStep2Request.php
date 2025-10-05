<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:10',
                'max:255',
                'regex:/^[a-zA-Z0-9äöüÄÖÜß\s\-\(\)]+$/',
            ],
            'description' => [
                'required',
                'string',
                'min:50',
                'max:5000',
            ],
            'brand' => [
                'nullable',
                'string',
                'max:100',
            ],
            'model' => [
                'nullable',
                'string',
                'max:100',
            ],
            'condition' => [
                'required',
                'in:new,like_new,good,acceptable',
            ],
            'retail_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100000',
                'decimal:0,2',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Titel ist erforderlich',
            'title.min' => 'Titel muss mindestens 10 Zeichen lang sein',
            'title.max' => 'Titel darf maximal 255 Zeichen lang sein',
            'title.regex' => 'Titel enthält ungültige Zeichen',
            
            'description.required' => 'Beschreibung ist erforderlich',
            'description.min' => 'Beschreibung muss mindestens 50 Zeichen lang sein',
            'description.max' => 'Beschreibung darf maximal 5000 Zeichen lang sein',
            
            'brand.max' => 'Marke darf maximal 100 Zeichen lang sein',
            'model.max' => 'Modell darf maximal 100 Zeichen lang sein',
            
            'condition.required' => 'Zustand ist erforderlich',
            'condition.in' => 'Ungültiger Zustand',
            
            'retail_price.numeric' => 'UVP muss eine Zahl sein',
            'retail_price.min' => 'UVP muss mindestens 0 sein',
            'retail_price.max' => 'UVP darf maximal 100.000 sein',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Bereinige Whitespace
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title),
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description),
            ]);
        }
    }
}