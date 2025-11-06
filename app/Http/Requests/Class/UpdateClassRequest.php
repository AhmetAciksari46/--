<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateClassRequest",
 *     @OA\Property(property="name", type="string", example="8-B"),
 *     @OA\Property(property="teacher_id", type="integer", example=15)
 * )
 */
class UpdateClassRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Sınıf adı metin olmalıdır.',
            'teacher_id.exists' => 'Öğretmen bulunamadı.',
        ];
    }
}
