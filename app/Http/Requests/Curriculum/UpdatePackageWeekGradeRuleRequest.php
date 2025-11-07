<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="UpdatePackageWeekGradeRuleRequest",
 * title="Derece Kuralı Güncelleme İsteği",
 * @OA\Property(property="grade_level", type="string", description="İzin verilen derece/seviye", example="HighSchoolGrade10"),
 * )
 */
class UpdatePackageWeekGradeRuleRequest extends FormRequest
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
            'week_no' => ['integer', 'min:1', 'sometimes'],
            'grade_level' => ['string', 'max:50', 'sometimes'],
            'is_mandatory' => ['boolean', 'sometimes'],
        ];
    }

    public function messages(): array
    {
        return [
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'grade_level.string' => 'Derece/Seviye bilgisi metin olmalıdır.',
            'is_mandatory.boolean' => 'Zorunluluk durumu doğru veya yanlış olmalıdır.',
        ];
    }
}
