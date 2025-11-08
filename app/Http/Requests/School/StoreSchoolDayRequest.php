<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="StoreSchoolDayRequest",
 * title="Okul Günü Oluşturma İsteği",
 * description="SchoolDay şemasındaki alanlarla uyumlu gün oluşturma isteği.",
 * required={"class_model_id", "week_no", "day_index", "date"},
 * @OA\Property(property="class_model_id", type="integer", description="Sınıf modeli ID'si", example=12),
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=3),
 * @OA\Property(property="day_index", type="integer", description="Haftanın günü (1=Pazartesi, 7=Pazar)", example=1),
 * @OA\Property(property="date", type="string", format="date", description="Takvim tarihi", example="2025-10-06"),
 * )
 */
class StoreSchoolDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->route('school')->id;

        return [
            'class_model_id' => ['required', 'integer', 'exists:class_models,id'],
            'week_no' => ['required', 'integer', 'min:1'],
            'day_index' => [
                'required',
                'integer',
                'min:1',
                'max:7',
                Rule::unique('school_week_days', 'day_index')->where(function ($query) use ($schoolId) {
                    return $query->where('school_id', $schoolId)
                        ->where('class_model_id', $this->input('class_model_id'))
                        ->where('week_no', $this->input('week_no'));
                }),
            ],
            'date' => ['required', 'date'],

        ];
    }

    public function messages(): array
    {
        return [
            'class_model_id.required' => 'Sınıf bilgisi zorunludur.',
            'class_model_id.exists' => 'Seçilen sınıf bulunamadı.',
            'week_no.required' => 'Hafta numarası zorunludur.',
            'day_index.unique' => 'Bu haftada bu sınıf için belirtilen gün zaten tanımlı.',
        ];
    }
}
