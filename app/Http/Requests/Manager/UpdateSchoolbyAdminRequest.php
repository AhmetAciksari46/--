<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateSchoolbyAdminRequest",
 *     type="object",
 *     title="School Update Request",
 *     description="Admin tarafından okul güncelleme isteği",
 *     @OA\Property(property="name", type="string", example="Açıksarı Koleji"),
 *     @OA\Property(property="nickname", type="string", example="ackolej"),
 *     @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
 *     @OA\Property(property="img_path", type="string", example="uploads/schools/logo.png"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="manager_id", type="integer", example=5)
 * )
 */
class UpdateSchoolbyAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('school.update');
    }


    public function rules(): array
    {


        return [
            'name' => 'sometimes|required|string|max:255',
            'nickname' => 'sometimes|required|string|max:255|unique:schools,nickname,' . $this->school->id,
            'address' => 'nullable|string|max:500',
            'img_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'manager_id' => 'sometimes|required|exists:users,id',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Okul adı zorunludur.',
            'name.string' => 'Okul adı metin türünde olmalıdır.',
            'name.max' => 'Okul adı en fazla 255 karakter olabilir.',

            'nickname.required' => 'Okul kısa adı (nickname) zorunludur.',
            'nickname.string' => 'Okul kısa adı metin türünde olmalıdır.',
            'nickname.max' => 'Okul kısa adı en fazla 255 karakter olabilir.',
            'nickname.unique' => 'Bu kısa ad zaten başka bir okul tarafından kullanılıyor.',

            'address.string' => 'Adres metin türünde olmalıdır.',
            'address.max' => 'Adres en fazla 500 karakter olabilir.',

            'img_path.string' => 'Görsel yolu metin türünde olmalıdır.',
            'img_path.max' => 'Görsel yolu en fazla 500 karakter olabilir.',

            'is_active.boolean' => 'Aktiflik bilgisi yalnızca true veya false olabilir.',
            'manager_id.exists' => 'Bu manager kullanıcı olarak bulunamadı.'

        ];
    }
}
