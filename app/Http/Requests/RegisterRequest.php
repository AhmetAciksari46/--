<?php

// app/Http/Requests/IndividualStudentRegisterRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    // Yetkilendirmeyi true yapın. (Genellikle Auth dışındaki formlarda true olmalıdır)
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "userName" => "required|string|max:255|unique:users",
            "email" => "required|string|email|unique:users",
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
            'email.required'            => 'E-posta alanı zorunludur.',
            'email.email'               => 'Lütfen geçerli bir e-posta adresi giriniz.', // Yeni eklenen
            'password.min'              => 'Şifre alanı en az :min karakter olmalıdır.',
            'password.required'         => 'Şifre alanı zorunludur.',
            'password.confirmed'        => 'Şifreler eşleşmiyor. Lütfen iki şifrenin de aynı olduğundan emin olun.',
        ];
    }
}