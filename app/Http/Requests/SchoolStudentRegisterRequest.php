<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolStudentRegisterRequest extends FormRequest
{
     public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "userName" => "required|string|max:255|unique:users",
            "password" => "required|string|min:6|confirmed",
        ];
    }

    // Mesajları tanımlayacağımız metot
    public function messages(): array
    {
        return [
            'name.required'             => 'Ad ve Soyad alanı zorunludur.',
            'userName.required'         => 'Kullanıcı adı alanı zorunludur.',
            'userName.unique'           => 'Bu kullanıcı adı daha önce alınmıştır.',
            'password.min'              => 'Şifre alanı en az :min karakter olmalıdır.',
            'password.required'         => 'Şifre alanı zorunludur.',
            'password.confirmed'        => 'Şifreler eşleşmiyor. Lütfen iki şifrenin de aynı olduğundan emin olun.',
        ];
    }
}
