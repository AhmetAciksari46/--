<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdatePackageWeekGradeRuleRequest",
 *     title="Sınıf Kuralı Güncelleme İsteği",
 *     description="Bir paketin sınıf kuralını güncellemek için kullanılır.",
 *     @OA\Property(property="grade_id", type="integer", example=4, description="Yeni sınıf seviyesi ID'si"),
 *     @OA\Property(property="week_no", type="integer", example=12, description="Yeni hafta numarası"),
 *     @OA\Property(property="days_required", type="integer", example=5, description="Yeni gün gereksinimi (1-7 arası)")
 * )
 */
class UpdatePackageWeekGradeRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        $package = $this->route('package');
        $gradeRule = $this->route('grade_rule');

        $packageId = $package->id;
        $ruleId = $gradeRule->id;

        $currentGradeId = $gradeRule->grade_id;
        $currentWeek = $gradeRule->week_no;

        $newGradeId = $this->input('grade_id', $currentGradeId);
        $newWeek = $this->input('week_no', $currentWeek);

        return [
            'grade_id' => [
                'sometimes',
                'integer',
                'exists:grades,id',
                Rule::unique('package_week_grade_rules', 'grade_id')
                    ->where(fn($q) => $q
                        ->where('package_id', $packageId)
                        ->where('week_no', $newWeek))
                    ->ignore($ruleId),
            ],

            'week_no' => [
                'sometimes',
                'integer',
                'min:1',
                'max:' . $package->week_count,
                Rule::unique('package_week_grade_rules', 'week_no')
                    ->where(fn($q) => $q
                        ->where('package_id', $packageId)
                        ->where('grade_id', $newGradeId))
                    ->ignore($ruleId),
            ],

            'days_required' => ['sometimes', 'integer', 'between:1,7'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_id.integer' => 'Sınıf seviyesi ID numeric olmalıdır.',
            'grade_id.exists' => 'Geçerli bir sınıf seviyesi seçilmelidir.',
            'grade_id.unique' => 'Bu sınıf ve hafta kombinasyonu zaten mevcut.',

            'week_no.integer' => 'Hafta numarası numeric olmalıdır.',
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'week_no.max' => 'Hafta numarası paketin toplam hafta sayısını aşamaz.',
            'week_no.unique' => 'Bu sınıf için belirtilen hafta zaten tanımlıdır.',

            'days_required.integer' => 'Gün sayısı numeric olmalıdır.',
            'days_required.between' => 'Gün sayısı 1 ile 7 arasında olmalıdır.',
        ];
    }
}
