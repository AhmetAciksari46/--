<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="UpdateSchoolWeekRequest",
 * title="Hafta Güncelleme İsteği",
 * description="SchoolWeek şemasındaki alanların parça parça güncellenmesi için istek gövdesi.",
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=5),
 * @OA\Property(property="start_date", type="string", format="date", example="2025-11-24", description="Haftanın başlangıç tarihi"),
 * @OA\Property(property="is_holiday", type="boolean", example=true, nullable=true, description="Bu haftanın tatil olup olmadığını belirtir"),
 * )
 */
class UpdateSchoolWeekRequest extends FormRequest
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
        // Route Model Binding sayesinde School ve SchoolWeek modellerine erişebiliriz.
        $schoolId = $this->route('school')->id;
        $weekId = $this->route('week')->id; // Güncellenen SchoolWeek ID'si

        return [
            'week_no' => [
                'sometimes', // Bu alan gönderilmeyebilir
                'required',
                'integer',
                'min:1',
                // week_no, aynı okul içinde benzersiz olmalı, ancak mevcut kaydı hariç tut (ignore)
                Rule::unique('school_weeks')->where(function ($query) use ($schoolId) {
                    return $query->where('school_id', $schoolId);
                })->ignore($weekId),
            ],
            'start_date' => ['sometimes', 'required', 'date'],
            'is_holiday' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'week_no.unique' => 'Bu hafta numarası, bu okul için zaten tanımlanmış.',
            'week_no.required' => 'Hafta numarası zorunludur.',
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
            'is_holiday.boolean' => 'Tatil durumu doğru veya yanlış olmalıdır.',
        ];
    }
}
