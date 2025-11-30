<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AttendanceStoreRequest",
 *     type="object",
 *     required={"student_id","status"},
 *
 *     @OA\Property(property="student_id", type="integer", example=35),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="present",
 *         enum={"present","absent","late","excused"}
 *     ),
 *     @OA\Property(property="absent_excuse_note", type="string", example="Veli tarafından izinli"),
 *     @OA\Property(property="entered_at", type="string", example="2025-11-10 10:05:00")
 * )
 */
class AttendanceStoreRequest extends FormRequest
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
            'student_id' => 'required|exists:school_student_profiles,id',
            'status' => 'required|in:present,absent,late,excused',
            'absent_excuse_note' => 'nullable|string|max:500',
            'entered_at' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'student_id.required' => 'Öğrenci ID zorunludur.',
            'status.required' => 'Yoklama durumu belirtilmelidir.',
            'status.in' => 'Durum geçersiz. present, absent, late, excused değerleri olabilir.',
            'absent_excuse_note.max' => 'Not alanı en fazla 500 karakter olabilir.',
        ];
    }
}
