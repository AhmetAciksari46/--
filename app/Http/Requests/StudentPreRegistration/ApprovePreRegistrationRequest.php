<?php

namespace App\Http\Requests\StudentPreRegistration;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ApprovePreRegistrationRequest",
 *     required={"classroom_id"},
 *     @OA\Property(property="classroom_id", type="integer", example=5)
 * )
 */
class ApprovePreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Öğrencinin atanacağı sınıf seçilmelidir.',
            'classroom_id.exists'   => 'Seçilen sınıf bulunamadı.',
        ];
    }
}
