<?php

namespace App\Http\Requests\LessonSession;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LessonSessionStoreRequest",
 *     type="object",
 *     required={"class_schedule_id","week_number","date","status","is_attendance_required"},
 *     @OA\Property(property="class_schedule_id", type="integer", example=12),
 *     @OA\Property(property="week_number", type="integer", example=5),
 *     @OA\Property(property="date", type="string", example="2025-11-14"),
 *     @OA\Property(property="teacher_id", type="integer", example=22),
 *     @OA\Property(property="physical_classroom_id", type="integer", example=3),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"scheduled","completed","cancelled","skipped","no_school"},
 *         example="scheduled"
 *     ),
 *     @OA\Property(property="is_attendance_required", type="boolean", example=true)
 * )
 */
class LessonSessionStoreRequest extends FormRequest
{
    public function authorize()
    {
        //return auth()->user()->can('lessonsession.create');
        return true;
    }

    public function rules()
    {
        return [
            'class_schedule_id' => 'required|exists:class_schedules,id',
            'week_number' => 'required|integer|min:1|max:52',
            'date' => 'required|date',
            'teacher_id' => 'nullable|exists:users,id',
            'physical_classroom_id' => 'nullable|exists:physical_classrooms,id',
            'status' => 'required|in:scheduled,completed,cancelled,skipped,no_school',
            'is_attendance_required' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'class_schedule_id.required' => 'Ders planı seçilmelidir.',
            'week_number.required' => 'Hafta numarası zorunludur.',
            'week_number.min' => 'Hafta numarası en az 1 olmalıdır.',
            'date.required' => 'Ders tarihini belirtmelisiniz.',
            'status.in' => 'Durum alanı geçersiz bir değer içeriyor.',
            'is_attendance_required.boolean' => 'Yoklama zorunluluğu boolean olmalıdır.',
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
