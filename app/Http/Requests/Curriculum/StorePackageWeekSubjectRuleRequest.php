<?php

namespace App\Http\Requests\Curriculum;

use App\Models\PackageWeekGradeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="StorePackageWeekSubjectRuleRequest",
 *     title="Ders Kuralı Oluşturma İsteği",
 *     required={"grade", "week_no", "subject_id", "hours"},
 *     @OA\Property(property="grade", type="integer", example=5, description="Kuralın uygulanacağı sınıf seviyesi."),
 *     @OA\Property(property="week_no", type="integer", example=10, description="Kuralın geçerli olduğu müfredat haftası."),
 *     @OA\Property(property="subject_id", type="integer", example=7, description="Kuralın uygulanacağı dersin ID'si."),
 *     @OA\Property(property="hours", type="integer", example=6, description="Hafta için planlanan ders saati."),
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
        $package = $this->route('package');
        $packageId = is_object($package) ? $package->getKey() : $package;
        $maxWeeks = is_object($package) ? $package->week_count : 52;
        return [
            'grade' => ['required', 'integer', 'min:1'],
            'week_no' => ['required', 'integer', 'min:1', 'max:' . $maxWeeks],
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
                Rule::unique('package_week_subject_rules', 'subject_id')
                    ->where(fn($query) => $query
                        ->where('package_id', $packageId)
                        ->where('grade', $this->input('grade'))
                        ->where('week_no', $this->input('week_no'))),
            ],
            'hours' => ['required', 'integer', 'min:1'],
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
            'subject_id.required' => 'Ders ID\'si zorunludur.',
            'subject_id.integer' => 'Ders ID\'si sayısal olmalıdır.',
            'subject_id.exists' => 'Belirtilen ders bulunamadı.',
            'subject_id.unique' => 'Bu sınıf ve hafta için belirtilen ders zaten tanımlı.',
            'hours.required' => 'Ders saati zorunludur.',
            'hours.integer' => 'Ders saati sayısal olmalıdır.',
            'hours.min' => 'Ders saati en az 1 olmalıdır.',
        ];
    }
}
