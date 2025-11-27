<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreStudentRequest",
 *     type="object",
 *     required={"name","userName","password","birth_date","student_number","tc_no"},
 *
 *     @OA\Property(property="name", type="string", example="Mehmet Demir"),
 *     @OA\Property(property="userName", type="string", example="mehmetdemir"),
 *     @OA\Property(property="email", type="string", example="mehmet@example.com"),
 *     @OA\Property(property="password", type="string", example="12345678"),
 *     @OA\Property(property="password_confirmation", type="string", example="12345678"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2014-05-20"),
 *     @OA\Property(property="student_number", type="string", example="2024-101"),
 *     @OA\Property(property="tc_no", type="string", example="12345678901"),
 *     @OA\Property(property="phone", type="string", example="+905551234567"),
 *     @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
 *     @OA\Property(property="gender", type="string", example="male"),
 *     @OA\Property(property="active_class_id", type="integer", example=5),  
 * )
 */
class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'userName' => 'required|string|unique:users,userName',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|max:64',
            'birth_date' => 'required|date|before:today',
            'student_number' => 'required|string|unique:school_student_profiles,student_number',
            'tc_no' => 'required|string|unique:school_student_profiles,tc_no',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'gender' => 'nullable|string',

            'active_class_id' => 'nullable|integer|exists:class_models,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Öğrenci adı zorunludur.',
            'userName.required' => 'Kullanıcı adı zorunludur.',
            'userName.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
            'birth_date.before' => 'Doğum tarihi bugünden önce olmalıdır.',
            'student_number.unique' => 'Bu öğrenci numarası zaten kayıtlı.',
            'tc_no.unique' => 'Bu TC numarası zaten kayıtlı.',
        ];
    }
}
