<?php

namespace App\Http\Requests\School\Week;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AutoGenerateWeekDaysRequest",
 *     type="object",
 *     required={"days_of_week"},
 *     @OA\Property(
 *         property="days_of_week",
 *         type="array",
 *         @OA\Items(type="string", example="monday")
 *     )
 * )
 */
class AutoGenerateWeekDaysRequest extends FormRequest
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
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }

    public function messages()
    {
        return [
            'days_of_week.required' => 'Hafta günleri zorunludur.',
            'days_of_week.array' => 'Gün listesi geçersiz.',
            'days_of_week.*.in' => 'Geçerli bir gün adı girin (ör: monday).',
        ];
    }
}
