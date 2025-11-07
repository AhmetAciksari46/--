<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="StoreSubjectRequest",
 * title="Ders Oluşturma İsteği",
 * required={"name"},
 * @OA\Property(property="name", type="string", description="Ders adı (Örn: Matematik)", example="Matematik"),
 * @OA\Property(property="code", type="string", description="Ders kodu (Örn: MT101)", example="MT101"),
 * )
 */
class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Kodun okul içinde benzersiz olması gerektiğini varsayıyoruz
            'code' => ['nullable', 'string', 'max:50', 'unique:subjects,code,NULL,id,school_id,' . $this->route('school')->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ders adı zorunludur.',
            'name.max' => 'Ders adı 255 karakterden uzun olamaz.',
            'code.unique' => 'Bu ders kodu, okulunuzda zaten tanımlanmıştır.',
            'is_active.boolean' => 'Aktiflik durumu doğru veya yanlış olmalıdır.',
        ];
    }
}
