<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="UpdateSingleAttendanceRequest",
 * title="Tekil Yoklama Güncelleme İsteği",
 * @OA\Property(property="status", type="string", enum={"present", "absent", "late", "excused"}, example="absent"),
 * @OA\Property(property="note", type="string", nullable=true, example="Rahatsızlandığı için izinli ayrıldı.")
 * )
 */
class UpdateSingleAttendanceRequest extends FormRequest
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
        return [
            'status' => ['sometimes', 'required', 'string', Rule::in(['present', 'absent', 'late', 'excused'])],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Yoklama durumu zorunludur.',
            'status.in' => 'Geçerli bir yoklama durumu (present, absent, late, excused) belirtilmelidir.',
        ];
    }
}
