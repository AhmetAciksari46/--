<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="UpdatePackageWeekSubjectRuleRequest",
 * title="Ders Kuralı Güncelleme İsteği",
 * @OA\Property(property="subject_id", type="integer", description="İzin verilen ders ID'si", example=5),
 * )
 */
class UpdatePackageWeekSubjectRuleRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week_no' => ['integer', 'min:1', 'sometimes'],
            'subject_id' => ['integer', 'exists:subjects,id', 'sometimes'],
            'is_mandatory' => ['boolean', 'sometimes'],
        ];
    }

    public function messages(): array
    {
        return [
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'subject_id.integer' => 'Ders kimliği sayısal olmalıdır.',
            'subject_id.exists' => 'Geçerli bir ders kimliği belirtilmelidir.',
            'is_mandatory.boolean' => 'Zorunluluk durumu doğru veya yanlış olmalıdır.',
        ];
    }
}
