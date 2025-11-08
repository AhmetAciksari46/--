<?php

namespace App\Http\Requests\Curriculum;

use App\Models\PackageWeekGradeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @OA\Schema(
 * schema="UpdatePackageWeekSubjectRuleRequest",
 * title="Ders Kuralı Güncelleme İsteği",
 * description="Bir paketin mevcut ders kuralını (Subject Rule) güncellemek için kullanılır. Alanlar isteğe bağlıdır.",
 * @OA\Property(property="grade_rule_id", type="integer", example=1, description="Kuralın bağlı olduğu Sınıf Kuralı (PackageWeekGradeRule) ID'si."),
 * @OA\Property(property="subject_id", type="integer", example=5, description="Kuralın uygulanacağı ders ID'si."),
 * @OA\Property(property="max_weeks", type="integer", example=10, description="Bu ders için maksimum izin verilen hafta sayısı."),
 * )
 */
class UpdatePackageWeekSubjectRuleRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        $package = $this->route('package');
        $ruleId = $this->route('subject_rule')->id ?? null;

        // Güncellenecek Grade Rule ID'sini input'tan al (eğer gönderildiyse) veya mevcut kuraldan al.
        $gradeRuleId = $this->input('grade_rule_id') ?? $this->route('subject_rule')->grade_rule_id ?? null;

        return [
            'grade_rule_id' => [
                'sometimes',
                'integer',
                'exists:package_week_grade_rules,id,package_id,' . $package->id
            ],
            'subject_id' => [
                'sometimes',
                'integer',
                'exists:subjects,id',
                // Unique kontrolü, güncellenen kuralın kendisini yok sayarak çalışır.
                Rule::unique('package_week_subject_rules')->where(function ($query) use ($gradeRuleId) {
                    return $query->where('grade_rule_id', $gradeRuleId);
                })->ignore($ruleId)
            ],
            'max_weeks' => [
                'sometimes',
                'integer',
                'min:1',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'grade_rule_id.exists' => 'Belirtilen sınıf kuralı ID\'si geçersiz veya bu pakete ait değil.',
            'subject_id.exists' => 'Belirtilen Ders ID\'si sistemde mevcut değil.',
            'subject_id.unique' => 'Bu sınıf kuralı altında bu ders zaten tanımlanmış.',
            'max_weeks.min' => 'Maksimum hafta sayısı en az 1 olmalıdır.',
        ];
    }
    /**
     * Doğrulama tamamlandıktan sonra ek kontrolleri yapar (Max Hafta Kontrolü).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('grade_rule_id')) {
                    return;
                }

                $maxWeeksInput = $this->input('max_weeks');
                // Mevcut veya yeni grade_rule_id'yi al
                $gradeRuleId = $this->input('grade_rule_id') ?? $this->route('subject_rule')->grade_rule_id;

                if ($maxWeeksInput !== null && $gradeRuleId !== null) {
                    $gradeRule = PackageWeekGradeRule::find($gradeRuleId);

                    if ($gradeRule && $maxWeeksInput > $gradeRule->max_weeks) {
                        $validator->errors()->add(
                            'max_weeks',
                            'Maksimum hafta sayısı (' . $maxWeeksInput . '), bağlı olduğu Sınıf Kuralının (Max: ' . $gradeRule->max_weeks . ') izin verdiği süreyi aşamaz.'
                        );
                    }
                }
            }
        ];
    }
}
