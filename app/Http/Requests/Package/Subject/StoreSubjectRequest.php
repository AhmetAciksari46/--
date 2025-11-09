<?php

namespace App\Http\Requests\Package\Subject;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name'],
            'code' => ['nullable', 'string', 'max:50', 'unique:subjects,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ders adı alanı zorunludur.',
            'name.string' => 'Ders adı metin türünde olmalıdır.',
            'name.max' => 'Ders adı en fazla 255 karakter olabilir.',
            'name.unique' => 'Bu ders adı zaten mevcut.',

            'code.string' => 'Kod alanı metin türünde olmalıdır.',
            'code.max' => 'Kod alanı en fazla 50 karakter olabilir.',
            'code.unique' => 'Bu ders kodu zaten kullanımda.',

            'description.string' => 'Açıklama metin türünde olmalıdır.',
            'description.max' => 'Açıklama en fazla 1000 karakter olabilir.',

            'is_active.boolean' => 'Aktiflik değeri sadece true veya false olabilir.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'ders adı',
            'code' => 'ders kodu',
            'description' => 'açıklama',
            'is_active' => 'aktiflik durumu',
        ];
    }
}
