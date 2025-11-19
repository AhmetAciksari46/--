<?php

namespace App\Http\Requests\School\Week;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSchoolWeekRequest",
 *     type="object",
 *     required={"package_week_grade_rule_id"},
 *     @OA\Property(property="package_week_grade_rule_id", type="integer", example=10),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-11-15"),
 * )
 */
class StoreSchoolWeekRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_week_grade_rule_id' => 'required|exists:package_week_grade_rules,id',
            'start_date' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'package_week_grade_rule_id.required' => 'Hafta kuralı zorunludur.',
            'package_week_grade_rule_id.exists'   => 'Geçerli bir paket hafta kuralı seçiniz.',
            'start_date.date'                     => 'Başlangıç tarihi geçerli bir tarih formatında olmalıdır.',
        ];
    }
}
