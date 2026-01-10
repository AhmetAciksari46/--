<?php

namespace App\Http\Requests\ContentDetail;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContentDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'], // detail update'te komple payload güncelleyelim
        ];
    }

    public function messages(): array
    {
        return [
            'payload.required' => 'Payload zorunludur.',
            'payload.array' => 'Payload formatı geçersiz.',
        ];
    }
}
