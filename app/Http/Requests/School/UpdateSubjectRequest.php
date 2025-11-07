<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="UpdateSubjectRequest",
 * title="Ders Güncelleme İsteği",
 * @OA\Property(property="name", type="string", description="Ders adı (Örn: Matematik)", example="Matematik"),
 * )
 */
class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            // Güncellemede benzersizlik kontrolü (mevcut ders hariç)
            'code' => ['nullable', 'string', 'max:50', 'unique:subjects,code,' . $subjectId . ',id,school_id,' . $this->route('school')->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ders adı zorunludur.',
            'code.unique' => 'Bu ders kodu, okulunuzda zaten tanımlanmıştır.',
            'is_active.boolean' => 'Aktiflik durumu doğru veya yanlış olmalıdır.',
        ];
    }
}
