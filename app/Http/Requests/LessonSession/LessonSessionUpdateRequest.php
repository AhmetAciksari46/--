<?php

namespace App\Http\Requests\LessonSession;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LessonSessionUpdateRequest",
 *     type="object",
 *     @OA\Property(property="week_number", type="integer", example=8),
 *     @OA\Property(property="date", type="string", example="2025-11-22"),
 *     @OA\Property(property="teacher_id", type="integer", example=15),
 *     @OA\Property(property="physical_classroom_id", type="integer", example=9),
 *     @OA\Property(property="status", type="string", example="completed"),
 *     @OA\Property(property="is_attendance_required", type="boolean", example=false)
 * )
 */
class LessonSessionUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'week_number' => 'sometimes|integer|min:1|max:52',
            'date' => 'sometimes|date',
            'teacher_id' => 'sometimes|nullable|exists:users,id',
            'physical_classroom_id' => 'sometimes|nullable|exists:physical_classrooms,id',
            'status' => 'sometimes|in:scheduled,completed,cancelled,skipped,no_school',
            'is_attendance_required' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'week_number.integer' => 'Hafta numarası sayı olmalıdır.',
            'status.in' => 'Durum alanı geçersizdir.',
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
