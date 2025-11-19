<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateClassRequest",
 *     @OA\Property(property="name", type="string", example="9-B"),
 *     @OA\Property(property="teacher_id", type="integer", example=8),
 *     @OA\Property(property="grade_id", type="integer", example=10),
 *     @OA\Property(property="academic_year", type="string", example="2025-2026"),
 *     @OA\Property(property="description", type="string", example="Sosyal Bilimler sınıfı"),
 *     @OA\Property(property="is_active", type="boolean", example=false),
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
            'name'           => 'sometimes|string|max:255',
            'teacher_id'     => 'sometimes|exists:users,id',
            'grade_id'       => 'sometimes|exists:grades,id',
            'academic_year'  => 'nullable|string|max:20',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.exists' => 'Geçersiz öğretmen seçildi.',
        ];
    }
}
