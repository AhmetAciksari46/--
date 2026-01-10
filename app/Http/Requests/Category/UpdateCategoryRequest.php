<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Kategori adı metin olmalıdır.',
            'name.max' => 'Kategori adı en fazla 255 karakter olabilir.',

            'description.string' => 'Açıklama metin olmalıdır.',

            'color.string' => 'Renk alanı metin olmalıdır.',
            'color.max' => 'Renk alanı en fazla 50 karakter olabilir.',

            'parent_id.exists' => 'Seçilen üst kategori bulunamadı.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Kategori adı',
            'description' => 'Açıklama',
            'color' => 'Renk',
            'parent_id' => 'Üst kategori',
        ];
    }
}
