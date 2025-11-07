<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="StorePackageWeekGradeRuleRequest",
 * title="Derece Kuralı Oluşturma İsteği",
 * required={"package_id", "week_no", "grade_level"},
 * @OA\Property(property="package_id", type="integer", example=1),
 * @OA\Property(property="week_no", type="integer", description="Hafta Numarası", example=3),
 * @OA\Property(property="grade_level", type="string", description="İzin verilen derece/seviye", example="HighSchoolGrade10"),
 * )
 */
class StorePackageWeekGradeRuleRequest extends FormRequest
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
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'week_no' => ['required', 'integer', 'min:1'],
            // grade_level sizin tanımladığınız enum veya sabitler listesinden gelmelidir.
            'grade_level' => ['required', 'string', 'max:50'],
            'is_mandatory' => ['boolean'], // İsteğe bağlı
        ];
    }
    public function messages(): array
    {
        return [
            'package_id.required' => 'Paket kimliği zorunludur.',
            'package_id.exists' => 'Geçerli bir paket kimliği belirtilmelidir.',
            'week_no.required' => 'Hafta numarası zorunludur.',
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'grade_level.required' => 'Derece/Seviye bilgisi zorunludur.',
            'is_mandatory.boolean' => 'Zorunluluk durumu doğru veya yanlış olmalıdır.',
        ];
    }
}
