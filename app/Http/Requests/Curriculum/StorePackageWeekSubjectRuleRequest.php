<?php

namespace App\Http\Requests\Curriculum;

use App\Models\PackageWeekGradeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * @OA\Schema(
 * schema="StorePackageWeekSubjectRuleRequest",
 * title="Ders Kuralı Oluşturma İsteği",
 * required={"grade_rule_id", "subject_id", "max_weeks"},
 * @OA\Property(property="grade_rule_id", type="integer", example=1, description="Kuralın bağlı olduğu Sınıf Kuralı (PackageWeekGradeRule) ID'si. Bu ID, mevcut pakete ait olmalıdır."),
 * @OA\Property(property="subject_id", type="integer", example=5, description="Kuralın uygulanacağı ders ID'si."),
 * @OA\Property(property="max_weeks", type="integer", example=10, description="Bu ders için maksimum izin verilen hafta sayısı."),
 * )
 */
class StorePackageWeekSubjectRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        $packageId = $this->route('package')->id;

        return [
            'grade_rule_id' => [
                'required',
                'integer',
                // Kuralın mevcut pakete bağlı olup olmadığını kontrol et
                'exists:package_week_grade_rules,id,package_id,' . $packageId
            ],
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
                // Aynı sınıf kuralı altında aynı dersin tekrarını engeller.
                'unique:package_week_subject_rules,subject_id,NULL,id,grade_rule_id,' . $this->input('grade_rule_id')
            ],
            'max_weeks' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'grade_rule_id.required' => 'Ders kuralının hangi sınıf kuralına bağlı olduğu belirtilmelidir.',
            'grade_rule_id.exists' => 'Belirtilen sınıf kuralı ID\'si geçersiz veya bu pakete ait değil.',
            'subject_id.required' => 'Ders ID\'si zorunludur.',
            'subject_id.exists' => 'Belirtilen Ders ID\'si sistemde mevcut değil.',
            'subject_id.unique' => 'Bu sınıf kuralı altında bu ders zaten tanımlanmış.',
            'max_weeks.required' => 'Maksimum hafta sayısı zorunludur.',
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
                // grade_rule_id doğrulama hatası varsa kontrolü atla
                if ($validator->errors()->has('grade_rule_id') || !$this->input('grade_rule_id')) {
                    return;
                }

                $gradeRule = PackageWeekGradeRule::find($this->input('grade_rule_id'));
                $maxWeeks = $this->input('max_weeks');

                if ($gradeRule && $maxWeeks > $gradeRule->max_weeks) {
                    $validator->errors()->add(
                        'max_weeks',
                        'Maksimum hafta sayısı (' . $maxWeeks . '), bağlı olduğu Sınıf Kuralının (Max: ' . $gradeRule->max_weeks . ') izin verdiği süreyi aşamaz.'
                    );
                }
            }
        ];
    }
}
