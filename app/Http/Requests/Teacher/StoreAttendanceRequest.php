<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="StoreAttendanceRequest",
 * title="Yoklama Kayıt İsteği",
 * required={"attendance_records"},
 * @OA\Property(
 * property="attendance_records",
 * type="array",
 * description="Öğrenci yoklama kayıtları listesi",
 * @OA\Items(
 * @OA\Property(property="student_id", type="integer", example=101),
 * @OA\Property(property="status", type="string", enum={"present", "absent", "late", "excused"}, example="present"),
 * @OA\Property(property="note", type="string", nullable=true)
 * )
 * )
 * )
 */
class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_records' => ['required', 'array'],
            'attendance_records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'attendance_records.*.status' => ['required', 'string', Rule::in(['present', 'absent', 'late', 'excused'])],
            'attendance_records.*.note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_records.required' => 'Yoklama kayıtları listesi zorunludur.',
            'attendance_records.*.student_id.required' => 'Öğrenci kimliği zorunludur.',
            'attendance_records.*.student_id.exists' => 'Geçerli bir öğrenci kimliği belirtilmelidir.',
            'attendance_records.*.status.required' => 'Yoklama durumu zorunludur.',
            'attendance_records.*.status.in' => 'Geçerli bir yoklama durumu (present, absent, late, excused) belirtilmelidir.',
        ];
    }
}
