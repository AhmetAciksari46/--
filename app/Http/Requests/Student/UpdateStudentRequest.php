<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateStudentRequest",
 *     type="object",
 *
 *     @OA\Property(property="name", type="string", example="Mehmet Can Demir"),
 *     @OA\Property(property="email", type="string", example="mehmetcan@example.com"),
 *     @OA\Property(property="phone", type="string", example="+905554445566"),
 *     @OA\Property(property="address", type="string", example="Ankara, Türkiye"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2014-05-20"),
 *     @OA\Property(property="gender", type="string", example="male")
 * )
 */
class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy kontrolü controller içinde yapılır
    }
    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $this->student->id,
            'phone' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
            'birth_date' => 'sometimes|date|before:today',
            'gender' => 'sometimes|nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'birth_date.before' => 'Doğum tarihi bugünden önce olmalıdır.',
        ];
    }
}
