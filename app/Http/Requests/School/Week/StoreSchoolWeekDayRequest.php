<?php

namespace App\Http\Requests\School\Week;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSchoolWeekDayRequest",
 *     type="object",
 *     required={"day_no","real_date"},
 *     @OA\Property(property="day_no", type="integer", example=1),
 *     @OA\Property(property="real_date", type="string", format="date", example="2025-11-17"),
 * )
 */
class StoreSchoolWeekDayRequest extends FormRequest
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
            'day_no' => 'required|integer|min:1',
            'real_date' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'day_no.required' => 'Günün sıra numarası zorunludur.',
            'day_no.integer'  => 'Günün sıra numarası sayı olmalıdır.',
            'day_no.min'      => 'Günün sıra numarası 1 veya daha büyük olmalıdır.',
            'real_date.required' => 'Gerçek tarih zorunludur.',
            'real_date.date'     => 'Gerçek tarih geçerli bir formatta olmalıdır.',
        ];
    }
}
