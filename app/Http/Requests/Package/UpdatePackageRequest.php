<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'week_count' => 'sometimes|integer|min:1|max:52',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Paket adı geçerli bir metin olmalıdır.',
        ];
    }
}
