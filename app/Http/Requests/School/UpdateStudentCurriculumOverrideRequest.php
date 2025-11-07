<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="UpdateStudentCurriculumOverrideRequest",
 * title="Müfredat Geçersiz Kılma Güncelleme İsteği",
 * @OA\Property(property="override_status", type="string", description="Geçersiz kılma durumu (Assigned, Completed, Skipped)", example="Completed"),
 * @OA\Property(property="completion_date", type="string", format="date", nullable=true, description="Tamamlanma tarihi", example="2025-05-15"),
 * )
 */
class UpdateStudentCurriculumOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Yetkilendirme StudentCurriculumOverridePolicy'de kontrol edilecektir.
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'integer', 'exists:users,id'],
            'curriculum_id' => ['sometimes', 'integer', 'exists:curriculums,id'],
            'override_status' => [
                'sometimes',
                'string',
                Rule::in(['Assigned', 'Completed', 'Skipped', 'InProgress']),
            ],
            'reason' => ['sometimes', 'nullable', 'string', 'max:500'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'completion_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'override_status.in' => 'Geçersiz kılma durumu Assigned, Completed, Skipped veya InProgress olmalıdır.',
            'completion_date.after_or_equal' => 'Tamamlanma tarihi, başlangıç tarihinden sonra veya eşit olmalıdır.',
        ];
    }
}
