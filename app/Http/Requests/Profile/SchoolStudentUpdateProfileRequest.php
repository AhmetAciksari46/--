<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="SchoolStudentUpdateProfileRequest",
 *     type="object",
 *     title="Okul öğrencisi kendi profilini güncelleme isteği",
 *     required={},
 *     description="Öğrencinin kendi adı veya parolasını güncellemesine olanak tanır.",
 *     @OA\Property(property="name", type="string", maxLength=255, example="Ali Öğrenci", nullable=true),
 *     @OA\Property(property="password", type="string", format="password", minLength=8, example="YeniSifre123", nullable=true),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="YeniSifre123", nullable=true)
 * )
 */
class SchoolStudentUpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Eğer her iki alan da boşsa hata ver
            if (!$this->filled('name') && !$this->filled('password')) {
                $validator->errors()->add('general', 'En az bir alan (isim veya şifre) güncellenmelidir.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.string' => 'İsim geçerli bir metin olmalıdır.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifre doğrulaması eşleşmiyor.',
        ];
    }
}
