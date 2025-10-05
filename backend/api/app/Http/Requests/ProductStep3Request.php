<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media' => 'required|array|min:1|max:10',
            'media.*' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $mimeType = $value->getMimeType();
                    
                    // Erlaubte Bild-MIMEs
                    $allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
                    // Erlaubte Video-MIMEs
                    $allowedVideos = ['video/mp4', 'video/webm', 'video/quicktime'];
                    
                    if (in_array($mimeType, $allowedImages)) {
                        // Bild: Max 5MB
                        if ($value->getSize() > 5120 * 1024) {
                            $fail('Bilder dürfen maximal 5MB groß sein');
                        }
                    } elseif (in_array($mimeType, $allowedVideos)) {
                        // Video: Max 50MB
                        if ($value->getSize() > 51200 * 1024) {
                            $fail('Videos dürfen maximal 50MB groß sein');
                        }
                    } else {
                        $fail('Ungültiger Dateityp. Erlaubt: JPEG, PNG, WebP, MP4, WebM, MOV');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'Bitte laden Sie mindestens ein Bild oder Video hoch',
            'media.min' => 'Mindestens 1 Medium ist erforderlich',
            'media.max' => 'Maximal 10 Medien sind erlaubt',
            'media.*.file' => 'Ungültige Datei',
        ];
    }
}