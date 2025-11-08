<?php

namespace App\Http\Requests\Curriculum;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="UpdatePackageWeekGradeRuleRequest",
 * title="Sınıf Kuralı Güncelleme İsteği",
 * description="Bir paketin mevcut sınıf kuralını (Grade Rule) güncellemek için kullanılır. Alanlar isteğe bağlıdır.",
 * @OA\Property(property="grade_id", type="integer", example=6, description="Kuralın uygulanacağı yeni sınıf ID'si."),
 * @OA\Property(property="max_weeks", type="integer", example=45, description="Bu sınıf için maksimum izin verilen yeni hafta sayısı."),
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
        $ruleId = $this->route('grade_rule')->id ?? null;

        // Paketin toplam hafta sayısı.
        $maxPackageWeeks = $package ? $package->week_count : 52;

        return [
            // 'sometimes' kullanılır, çünkü tüm alanların her zaman gönderilmesi gerekmez.
            'grade_id' => [
                'sometimes',
                'integer',
                'exists:grades,id',
                // Güncellenen kuralın kendisini ve bu paketteki diğer kural ID'lerini hariç tutarak
                // aynı pakette aynı sınıf ID'sinin tekrarını engeller.
                Rule::unique('package_week_grade_rules', 'grade_id')
                    ->where(fn($query) => $query->where('package_id', $package->id))
                    ->ignore($ruleId, 'id'),
            ],
            'max_weeks' => [
                'sometimes',
                'integer',
                'min:1',
                // Kurallar paketin genel hafta sayısını aşmamalıdır.
                'max:' . $maxPackageWeeks
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'grade_id.sometimes' => 'Sınıf ID\'si belirtilmelidir.',
            'grade_id.exists' => 'Belirtilen Sınıf ID\'si sistemde mevcut değil.',
            'grade_id.unique' => 'Bu paket için bu sınıfa ait bir kural zaten tanımlı.',
            'max_weeks.sometimes' => 'Maksimum hafta sayısı belirtilmelidir.',
            'max_weeks.integer' => 'Maksimum hafta sayısı tam sayı olmalıdır.',
            'max_weeks.min' => 'Maksimum hafta sayısı en az 1 olmalıdır.',
            'max_weeks.max' => 'Maksimum hafta sayısı, paketin izin verdiği toplam hafta sayısını aşamaz (' . $this->route('package')->week_count . ' hafta).',
        ];
    }
}
