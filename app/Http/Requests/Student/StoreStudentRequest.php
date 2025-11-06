<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreStudentRequest",
 *     required={"name", "userName", "password"},
 *     @OA\Property(property="name", type="string", example="Ahmet Yılmaz"),
 *     @OA\Property(property="userName", type="string", example="ahmetyilmaz"),
 *     @OA\Property(property="email", type="string", example="ahmet@example.com"),
 *     @OA\Property(property="password", type="string", example="123456")
 * )
 */
class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy ile ayrıca kontrol edilecek
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'userName' => 'required|string|max:255|unique:users,userName',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Öğrenci adı zorunludur.',
            'userName.required' => 'Kullanıcı adı zorunludur.',
            'userName.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
        ];
    }
}
