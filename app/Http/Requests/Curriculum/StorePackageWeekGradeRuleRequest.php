<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="StorePackageWeekGradeRuleRequest",
 *     title="Sınıf Kuralı Oluşturma İsteği",
 *     required={"grade", "week_no", "days_required"},
 *     @OA\Property(property="grade", type="integer", example=5, description="Kuralın uygulanacağı sınıf seviyesi."),
 *     @OA\Property(property="week_no", type="integer", example=10, description="Kuralın geçerli olduğu müfredat haftası."),
 *     @OA\Property(property="days_required", type="integer", example=4, description="Bu haftada gerekli minimum gün sayısı (1-7 arası).")
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
        $package = $this->route('package');
        $packageId = is_object($package) ? $package->getKey() : $package;
        $maxWeeks = is_object($package) ? $package->week_count : 52;

        return [
            'grade' => ['required', 'integer', 'min:1'],
            'week_no' => [
                'required',
                'integer',
                'min:1',
                'max:' . $maxWeeks,
                Rule::unique('package_week_grade_rules', 'week_no')
                    ->where(fn($query) => $query
                        ->where('package_id', $packageId)
                        ->where('grade', $this->input('grade'))),
            ],
            'days_required' => ['required', 'integer', 'between:1,7'],
        ];
    }
    public function messages(): array
    {
        return [
            'grade.required' => 'Sınıf seviyesi zorunludur.',
            'grade.integer' => 'Sınıf seviyesi sayısal olmalıdır.',
            'week_no.required' => 'Hafta numarası zorunludur.',
            'week_no.integer' => 'Hafta numarası sayısal olmalıdır.',
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'week_no.max' => 'Hafta numarası, paketin toplam hafta sayısını aşamaz.',
            'week_no.unique' => 'Bu sınıf için belirtilen hafta zaten tanımlı.',
            'days_required.required' => 'Gerekli gün sayısı zorunludur.',
            'days_required.integer' => 'Gerekli gün sayısı sayısal olmalıdır.',
            'days_required.between' => 'Gerekli gün sayısı 1 ile 7 arasında olmalıdır.',
        ];
    }
}
