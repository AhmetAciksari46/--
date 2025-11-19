<?php

namespace App\Http\Requests\School\Week;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateSchoolWeekRequest",
 *     type="object",
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-11-17")
 * )
 */
class UpdateSchoolWeekRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'start_date.date' => 'Başlangıç tarihi geçerli bir tarih formatında olmalıdır.',
        ];
    }
}
