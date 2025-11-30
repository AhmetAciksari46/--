<?php

namespace App\Http\Requests\LessonSession;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Swagger Şeması
 * 
 * @OA\Schema(
 *     schema="LessonSessionGenerateRequest",
 *     type="object",
 *     required={"schedule_ids","start_date"},
 *
 *     @OA\Property(
 *         property="schedule_ids",
 *         type="array",
 *         @OA\Items(type="integer", example=12)
 *     ),
 *     @OA\Property(property="weeks", type="integer", example=40),
 *     @OA\Property(property="start_date", type="string", example="2025-11-10")
 * )
 */
class LessonSessionGenerateRequest extends FormRequest
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
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'required|exists:class_schedules,id',
            'weeks' => 'sometimes|integer|min:1|max:60',
            'start_date' => 'required|date'
        ];
    }

    public function messages()
    {
        return [
            'schedule_ids.required' => 'En az bir ders planı seçilmelidir.',
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
            'weeks.integer' => 'Hafta sayısı bir sayı olmalıdır.',
            'weeks.min' => 'Hafta sayısı en az 1 olmalıdır.',
            'weeks.max' => 'Hafta sayısı en fazla 60 olabilir.',
        ];
    }
}
