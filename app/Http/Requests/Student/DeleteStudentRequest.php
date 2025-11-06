<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="DeleteStudentRequest",
 *     required={"confirm"},
 *     @OA\Property(property="confirm", type="boolean", example=true, description="Silme işlemini onaylamak için true gönderilmelidir.")
 * )
 */
class DeleteStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy kontrolü controller'da yapılır
    }

    public function rules(): array
    {
        return [
            'confirm' => 'required|boolean|in:true,1',
        ];
    }

    public function messages(): array
    {
        return [
            'confirm.required' => 'Silme işlemini onaylamak için "confirm" alanı zorunludur.',
            'confirm.boolean' => 'Confirm alanı true veya false olmalıdır.',
            'confirm.in' => 'Silme işlemini gerçekleştirmek için confirm alanını true olarak gönderiniz.',
        ];
    }
}
