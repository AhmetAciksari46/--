<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['choice', 'match', 'true_false'])],
            'status' => ['nullable', Rule::in(['draft', 'published'])],

            // payload (json)
            'payload' => ['required', 'array'],

            'payload.question' => ['required', 'array'],
            'payload.question.title' => ['required', 'string'],
            'payload.question.text' => ['nullable', 'string'],
            'payload.question.media_pool_id' => ['nullable', 'integer'],

            'payload.answers' => ['required', 'array'],
            'payload.answers.type' => ['required', 'string'],

            'payload.correct' => ['required', 'array'],
            'payload.solution' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Soru tipi zorunludur.',
            'type.in' => 'Soru tipi geçersiz.',
            'payload.required' => 'Payload zorunludur.',
            'payload.array' => 'Payload formatı geçersiz.',
            'payload.question.title.required' => 'Soru başlığı zorunludur.',
        ];
    }
}
