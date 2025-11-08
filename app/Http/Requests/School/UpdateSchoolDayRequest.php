<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="UpdateSchoolDayRequest",
 * title="Okul Günü Güncelleme İsteği",
 * description="SchoolDay şemasındaki alanların güncellenmesi için istek gövdesi.",
 * @OA\Property(property="class_model_id", type="integer", description="Sınıf modeli ID'si", example=12),
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=3),
 * @OA\Property(property="day_index", type="integer", description="Haftanın günü (1=Pazartesi, 7=Pazar)", example=2),
 * @OA\Property(property="date", type="string", format="date", description="Takvim tarihi", example="2025-10-07"),
 * )
 */
class UpdateSchoolDayRequest extends FormRequest
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
        /** @var \App\Models\SchoolWeekDay $day */
        $day = $this->route('day');
        $schoolId = $day->school_id;

        $classModelId = $this->input('class_model_id', $day->class_model_id);
        $weekNo = $this->input('week_no', $day->week_no);


        return [
            'class_model_id' => ['sometimes', 'required', 'integer', 'exists:class_models,id'],
            'week_no' => ['sometimes', 'required', 'integer', 'min:1'],
            'day_index' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:7',
                Rule::unique('school_week_days', 'day_index')->where(function ($query) use ($schoolId, $classModelId, $weekNo) {
                    return $query->where('school_id', $schoolId)
                        ->where('class_model_id', $classModelId)
                        ->where('week_no', $weekNo);
                })->ignore($day->id),
            ],
            'date' => ['sometimes', 'required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_model_id.exists' => 'Seçilen sınıf bulunamadı.',
            'day_index.unique' => 'Bu haftada bu sınıf için belirtilen gün zaten tanımlı.',
        ];
    }
}
