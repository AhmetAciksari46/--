<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="StorePackageWeekSubjectRuleRequest",
 * title="Ders Kuralı Oluşturma İsteği",
 * required={"package_id", "week_no", "subject_id"},
 * @OA\Property(property="package_id", type="integer", example=1),
 * @OA\Property(property="week_no", type="integer", description="Hafta Numarası", example=3),
 * @OA\Property(property="subject_id", type="integer", description="İzin verilen ders ID'si", example=5),
 * )
 */
class StorePackageWeekSubjectRuleRequest extends FormRequest
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
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'is_mandatory' => ['boolean'], // İsteğe bağlı
        ];
    }
    public function messages(): array
    {
        return [
            'package_id.required' => 'Paket ID zorunludur.',
            'package_id.integer' => 'Paket ID bir tamsayı olmalıdır.',
            'package_id.exists' => 'Belirtilen paket bulunamadı.',
            'week_no.required' => 'Hafta numarası zorunludur.',
            'week_no.integer' => 'Hafta numarası bir tamsayı olmalıdır.',
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'subject_id.required' => 'Ders ID zorunludur.',
            'subject_id.integer' => 'Ders ID bir tamsayı olmalıdır.',
            'subject_id.exists' => 'Belirtilen ders bulunamadı.',
            'is_mandatory.boolean' => 'is_mandatory alanı boolean (true/false) olmalıdır.',
        ];
    }
}
