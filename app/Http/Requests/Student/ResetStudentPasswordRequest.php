<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ResetStudentPasswordRequest",
 *     type="object",
 *     required={"password"},
 *
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         example="YeniSifre123!"
 *     )
 * )
 */
class ResetStudentPasswordRequest extends FormRequest
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
            'password' => 'required|string|min:6|max:64',
        ];
    }

    public function messages()
    {
        return [
            'password.required' => 'Yeni şifre zorunludur.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
        ];
    }
}
