<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class CreateSchoolRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()->can('school.create');
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255|unique:schools,nickname',
            'address' => 'nullable|string|max:500',
            'img_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'package_id' => 'required|exists:packages,id', // ✅ yeni

        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Okul adı zorunludur.',
            'name.string' => 'Okul adı metin türünde olmalıdır.',
            'name.max' => 'Okul adı en fazla 255 karakter olabilir.',
            'package_id.required' => 'Paket seçimi zorunludur.', // ✅ yeni
            'package_id.exists' => 'Seçilen paket bulunamadı.', // ✅ yeni
            'nickname.required' => 'Okul kısa adı (nickname) zorunludur.',
            'nickname.string' => 'Okul kısa adı metin türünde olmalıdır.',
            'nickname.max' => 'Okul kısa adı en fazla 255 karakter olabilir.',
            'nickname.unique' => 'Bu kısa ad zaten başka bir okul tarafından kullanılıyor.',

            'address.string' => 'Adres metin türünde olmalıdır.',
            'address.max' => 'Adres en fazla 500 karakter olabilir.',

            'img_path.string' => 'Görsel yolu metin türünde olmalıdır.',
            'img_path.max' => 'Görsel yolu en fazla 500 karakter olabilir.',

            'is_active.boolean' => 'Aktiflik bilgisi yalnızca true veya false olabilir.',
        ];
    }
}
