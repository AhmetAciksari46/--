<?php

namespace App\Http\Requests\ContentDetail;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'],

            'payload.question' => ['required', 'array'],
            'payload.question.title' => ['required', 'string'],
            'payload.question.text' => ['nullable', 'string'],
            'payload.question.media_pool_id' => ['nullable', 'integer'],

            'payload.answers' => ['required', 'array'],
            'payload.correct' => ['required', 'array'],
            'payload.solution' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'payload.required' => 'Payload zorunludur.',
            'payload.array' => 'Payload formatı geçersiz.',
            'payload.question.required' => 'Question alanı zorunludur.',
            'payload.question.title.required' => 'Soru başlığı zorunludur.',
        ];
    }
}
