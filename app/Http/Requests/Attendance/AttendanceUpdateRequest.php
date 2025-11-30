<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AttendanceUpdateRequest",
 *     type="object",
 *     @OA\Property(property="status", type="string", example="late"),
 *     @OA\Property(property="absent_excuse_note", type="string", example="Geç geldi."),
 *     @OA\Property(property="entered_at", type="string", example="2025-11-10 10:20:00")
 * )
 */
class AttendanceUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'sometimes|in:present,absent,late,excused',
            'absent_excuse_note' => 'nullable|string|max:500',
            'entered_at' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'status.in' => 'Durum geçersizdir.',
            'absent_excuse_note.max' => 'Not en fazla 500 karakter olabilir.',
        ];
    }
}
