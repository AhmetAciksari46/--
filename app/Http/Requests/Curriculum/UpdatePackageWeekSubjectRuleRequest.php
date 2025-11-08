<?php

namespace App\Http\Requests\Curriculum;

use App\Models\PackageWeekGradeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @OA\Schema(
 *     schema="UpdatePackageWeekSubjectRuleRequest",
 *     title="Ders Kuralı Güncelleme İsteği",
 *     description="Bir paketin mevcut ders kuralını güncellemek için kullanılır. Alanlar isteğe bağlıdır.",
 *     @OA\Property(property="grade", type="integer", example=6, description="Kuralın uygulanacağı sınıf seviyesi."),
 *     @OA\Property(property="week_no", type="integer", example=12, description="Kuralın geçerli olduğu müfredat haftası."),
 *     @OA\Property(property="subject_id", type="integer", example=8, description="Kuralın uygulanacağı dersin ID'si."),
 *     @OA\Property(property="hours", type="integer", example=4, description="Hafta için planlanan ders saati."),
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
        $subjectRule = $this->route('subject_rule');
        $packageId = is_object($package) ? $package->getKey() : $package;
        $ruleId = $subjectRule->id ?? null;
        $maxWeeks = is_object($package) ? $package->week_count : 52;

        $currentGrade = $subjectRule->grade ?? null;
        $currentWeek = $subjectRule->week_no ?? null;
        $currentSubject = $subjectRule->subject_id ?? null;
        $gradeValue = $this->input('grade', $currentGrade);
        $weekValue = $this->input('week_no', $currentWeek);
        $subjectValue = $this->input('subject_id', $currentSubject);

        return [
            'grade' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('package_week_subject_rules', 'grade')
                    ->where(fn($query) => $query
                        ->where('package_id', $packageId)
                        ->where('week_no', $weekValue)
                        ->where('subject_id', $subjectValue))
                    ->ignore($ruleId),
            ],
            'week_no' => [
                'sometimes',
                'integer',
                'min:1',
                'max:' . $maxWeeks,
                Rule::unique('package_week_subject_rules', 'week_no')
                    ->where(fn($query) => $query
                        ->where('package_id', $packageId)
                        ->where('grade', $gradeValue)
                        ->where('subject_id', $subjectValue))
                    ->ignore($ruleId),
            ],
            'subject_id' => [
                'sometimes',
                'integer',
                'exists:subjects,id',
                Rule::unique('package_week_subject_rules', 'subject_id')
                    ->where(fn($query) => $query
                        ->where('package_id', $packageId)
                        ->where('grade', $gradeValue)
                        ->where('week_no', $weekValue))
                    ->ignore($ruleId),
            ],
            'hours' => ['sometimes', 'integer', 'min:1'],
        ];
    }
    public function messages(): array
    {
        return [
            'grade.integer' => 'Sınıf seviyesi sayısal olmalıdır.',
            'grade.min' => 'Sınıf seviyesi en az 1 olmalıdır.',
            'grade.unique' => 'Bu ders için aynı hafta ve sınıf kombinasyonu zaten tanımlı.',
            'week_no.integer' => 'Hafta numarası sayısal olmalıdır.',
            'week_no.min' => 'Hafta numarası en az 1 olmalıdır.',
            'week_no.max' => 'Hafta numarası, paketin toplam hafta sayısını aşamaz.',
            'week_no.unique' => 'Bu ders için aynı hafta ve sınıf kombinasyonu zaten tanımlı.',
            'subject_id.integer' => 'Ders ID\'si sayısal olmalıdır.',
            'subject_id.exists' => 'Belirtilen ders bulunamadı.',
            'subject_id.unique' => 'Bu ders için aynı hafta ve sınıf kombinasyonu zaten tanımlı.',
            'hours.integer' => 'Ders saati sayısal olmalıdır.',
            'hours.min' => 'Ders saati en az 1 olmalıdır.',
        ];
    }
}
