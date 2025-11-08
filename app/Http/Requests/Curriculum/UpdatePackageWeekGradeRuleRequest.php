<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdatePackageWeekGradeRuleRequest",
 *     title="Sınıf Kuralı Güncelleme İsteği",
 *     description="Bir paketin mevcut sınıf kuralını güncellemek için kullanılır. Alanlar isteğe bağlıdır.",
 *     @OA\Property(property="grade", type="integer", example=6, description="Kuralın uygulanacağı yeni sınıf seviyesi."),
 *     @OA\Property(property="week_no", type="integer", example=12, description="Kuralın geçerli olduğu müfredat haftası."),
 *     @OA\Property(property="days_required", type="integer", example=5, description="Bu haftada gerekli minimum gün sayısı (1-7 arası).")
 * )
 */
class UpdatePackageWeekGradeRuleRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkili olup olmadığını belirler.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * İstek için doğrulama kurallarını alır.
     */
    public function rules(): array
    {
        // Paketin ID'sini ve güncellenen kuralın ID'sini rotadan alıyoruz.
        $package = $this->route('package');
        $gradeRule = $this->route('grade_rule');
        $packageId = is_object($package) ? $package->getKey() : $package;
        $ruleId = $gradeRule->id ?? null;
        $maxWeeks = is_object($package) ? $package->week_count : 52;

        $currentGrade = $gradeRule->grade ?? null;
        $currentWeek = $gradeRule->week_no ?? null;
        $gradeValue = $this->input('grade', $currentGrade);
        $weekValue = $this->input('week_no', $currentWeek);

        return [
            'grade' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('package_week_grade_rules', 'grade')
                    ->where(fn($query) => $query
                        ->where('package_id', $packageId)
                        ->where('week_no', $weekValue))
                    ->ignore($ruleId),
            ],
            'week_no' => [
                'sometimes',
                'integer',
                'min:1',
                'max:' . $maxWeeks,
                Rule::unique('package_week_grade_rules', 'week_no')
                    ->where(fn($query) => $query
                        ->where('package_id', $packageId)
                        ->where('grade', $gradeValue))
                    ->ignore($ruleId),
            ],
            'days_required' => ['sometimes', 'integer', 'between:1,7'],
        ];
    }


    public function messages(): array
    {
        return [
            'grade.integer' => 'Sınıf seviyesi sayısal olmalıdır.',
            'grade.min' => 'Sınıf seviyesi en az 1 olmalıdır.',
            'grade.unique' => 'Bu paket için aynı hafta içinde belirtilen sınıf zaten tanımlı.',
            'week_no.integer' => 'Hafta numarası sayısal olmalıdır.',
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'week_no.max' => 'Hafta numarası, paketin toplam hafta sayısını aşamaz.',
            'week_no.unique' => 'Bu sınıf için belirtilen hafta zaten tanımlı.',
            'days_required.integer' => 'Gerekli gün sayısı sayısal olmalıdır.',
            'days_required.between' => 'Gerekli gün sayısı 1 ile 7 arasında olmalıdır.',
        ];
    }
}
