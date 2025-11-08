<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="StorePackageWeekGradeRuleRequest",
 * title="Sınıf Kuralı Oluşturma İsteği",
 * required={"grade_id", "max_weeks"},
 * @OA\Property(property="grade_id", type="integer", example=5, description="Kuralın uygulanacağı sınıf ID'si."),
 * @OA\Property(property="max_weeks", type="integer", example=40, description="Bu sınıf için maksimum izin verilen hafta sayısı (Package week_count'tan küçük veya eşit olmalı)."),
 * )
 */
class StorePackageWeekGradeRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        // Paketin ID'sini route'tan alıyoruz.
        $packageId = $this->route('package');

        return [
            'grade_id' => [
                'required',
                'integer',
                'exists:grades,id',
                // Aynı paket ve aynı sınıf için tekrar kural oluşturulmasını engeller (unique key)
                'unique:package_week_grade_rules,grade_id,NULL,id,package_id,' . $packageId
            ],
            'max_weeks' => [
                'required',
                'integer',
                'min:1',
                // Paketin toplam hafta sayısını aşmamalı
                'max:' . $this->route('package')->week_count
            ],
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
