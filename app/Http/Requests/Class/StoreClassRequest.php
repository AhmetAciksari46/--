<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreClassRequest",
 *     required={"name","teacher_id"},
 *     @OA\Property(property="name", type="string", example="7-A"),
 *     @OA\Property(property="teacher_id", type="integer", example=12)
 * )
 */
class StoreClassRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Sınıf adı gereklidir.',
            'name.string' => 'Sınıf adı metin olmalıdır.',
            'teacher_id.required' => 'Öğretmen kimliği gereklidir.',
            'teacher_id.exists' => 'öğretmen bulunamadı.',
        ];
    }
}
