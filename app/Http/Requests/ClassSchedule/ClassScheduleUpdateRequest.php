<?php

namespace App\Http\Requests\ClassSchedule;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ClassScheduleUpdateRequest",
 *     type="object",
 *     @OA\Property(property="class_model_id", type="integer", example=5),
 *     @OA\Property(property="subject_id", type="integer", example=2),
 *     @OA\Property(property="day_of_week", type="string", example="tuesday"),
 *     @OA\Property(property="start_time", type="string", example="09:00"),
 *     @OA\Property(property="end_time", type="string", example="11:00"),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 */
class ClassScheduleUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        //        return auth()->user()->can('classschedule.update');

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_model_id' => 'sometimes|exists:class_models,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'day_of_week' => 'sometimes|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'is_active' => 'sometimes|boolean',
        ];
    }
    public function messages()
    {
        return [
            'class_model_id.exists' => 'Seçilen sınıf geçersizdir.',
            'subject_id.exists' => 'Seçilen ders geçersizdir.',

            'day_of_week.in' => 'Haftanın günü geçersizdir. monday, tuesday, wednesday, thursday, friday, saturday, sunday değerlerinden biri olmalıdır.',

            'start_time.date_format' => 'Başlangıç saati H:i formatında olmalıdır.',
            'end_time.date_format' => 'Bitiş saati H:i formatında olmalıdır.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',

            'is_active.boolean' => 'is_active değeri true veya false olmalıdır.'
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
