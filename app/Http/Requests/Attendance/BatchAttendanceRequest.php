<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="BatchAttendanceRequest",
 *     type="object",
 *     required={"attendances"},
 *
 *     @OA\Property(
 *         property="attendances",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="student_id", type="integer", example=35),
 *             @OA\Property(property="status", type="string", enum={"present","absent","late","excused"}),
 *             @OA\Property(property="absent_excuse_note", type="string", example="Veli tarafından izinli")
 *         )
 *     )
 * )
 */
class BatchAttendanceRequest extends FormRequest
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
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:school_student_profiles,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.absent_excuse_note' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'attendances.required' => 'Yoklama verisi gönderilmelidir.',
            'attendances.*.student_id.required' => 'Öğrenci ID zorunludur.',
            'attendances.*.status.required' => 'Durum zorunludur.',
            'attendances.*.status.in' => 'Geçersiz yoklama durumu.',
        ];
    }
}
