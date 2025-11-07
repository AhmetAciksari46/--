<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="UpdateSchoolDayRequest",
 * title="Okul Günü Güncelleme İsteği",
 * required={"is_open"},
 * @OA\Property(property="is_open", type="boolean", description="Bu günün açık olup olmadığı", example=true),
 * )
 */
class UpdateSchoolDayRequest extends FormRequest
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
            'is_open' => ['required', 'boolean'],
            // Diğer alanlar güncellenmeyecektir. Örneğin 'day_of_week_no'
        ];
    }

    public function messages(): array
    {
        return [
            'is_open.required' => 'Açık/Kapalı durumu zorunludur.',
            'is_open.boolean' => 'Açık/Kapalı durumu doğru veya yanlış olmalıdır.',
        ];
    }
}
