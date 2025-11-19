<?php

namespace App\Http\Requests\School\Week;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateSchoolWeekDayRequest",
 *     type="object",
 *     @OA\Property(property="day_no", type="integer", example=2),
 *     @OA\Property(property="real_date", type="string", format="date", example="2025-11-18")
 * )
 */
class UpdateSchoolWeekDayRequest extends FormRequest
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
            'day_no' => 'nullable|integer|min:1',
            'real_date' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'day_no.integer' => 'Günün sıra numarası sayı olmalıdır.',
            'day_no.min'      => 'Günün sıra numarası 1 veya daha büyük olmalıdır.',
            'real_date.date'  => 'Gerçek tarih geçerli bir formatta olmalıdır.',
        ];
    }
}
