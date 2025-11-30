<?php

namespace App\Http\Requests\ClassSchedule;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Swagger Gövde Şeması
 * @OA\Schema(
 *     schema="ClassScheduleStoreRequest",
 *     type="object",
 *     required={"class_model_id","subject_id","day_of_week","start_time","end_time"},
 *     @OA\Property(property="class_model_id", type="integer", example=5),
 *     @OA\Property(property="subject_id", type="integer", example=2),
 *     @OA\Property(property="day_of_week", type="string", example="monday"),
 *     @OA\Property(property="start_time", type="string", example="08:00"),
 *     @OA\Property(property="end_time", type="string", example="10:00")
 * )
 */
class ClassScheduleStoreRequest extends FormRequest
{
    public function authorize()
    {
        // Permission middleware controller'da yapılacak
        return true;
        //        return auth()->user()->can('classschedule.create');

    }

    public function rules()
    {
        return [
            'class_model_id' => 'required|exists:class_models,id',
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'class_model_id.required' => 'Sınıf seçilmesi zorunludur.',
            'class_model_id.exists' => 'Seçilen sınıf bulunamadı.',

            'subject_id.required' => 'Ders ilişkisi zorunludur.',
            'subject_id.exists' => 'Seçilen ders ilişkisi geçersiz.',


            'day_of_week.required' => 'Hafta günü zorunludur.',
            'day_of_week.in' => 'Geçerli bir gün seçmelisiniz.',

            'start_time.required' => 'Başlangıç saati zorunludur.',
            'start_time.date_format' => 'Başlangıç saati H:i formatında olmalıdır.',

            'end_time.required' => 'Bitiş saati zorunludur.',
            'end_time.date_format' => 'Bitiş saati H:i formatında olmalıdır.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',

        ];
    }

    protected function prepareForValidation()
    {
        $school = $this->route('school');

        if ($school) {
            $this->merge(['school_id' => $school->id]);
        }
    }
}
