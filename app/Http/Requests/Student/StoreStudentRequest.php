<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="StoreStudentRequest",
 *     type="object",
 *     required={
 *         "name",
 *         "userName",
 *         "password",
 *         "password_confirmation",
 *         "birth_date",
 *         "student_number",
 *         "tc_no"
 *     },
 *
 *     @OA\Property(property="name", type="string", example="Mehmet Demir"),
 *     @OA\Property(property="userName", type="string", example="mehmetdemir123"),
 *     @OA\Property(property="email", type="string", example="mehmet@example.com"),
 *     @OA\Property(property="password", type="string", example="12345678"),
 *     @OA\Property(property="password_confirmation", type="string", example="12345678"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2014-05-20"),
 *     @OA\Property(property="student_number", type="string", example="2024-101"),
 *     @OA\Property(property="tc_no", type="string", example="12345678901"),
 *     @OA\Property(property="phone", type="string", example="+905551234567"),
 *     @OA\Property(property="address", type="string", example="İstanbul, Türkiye"),
 *     @OA\Property(
 *         property="gender",
 *         type="string",
 *         example="male",
 *         enum={"male","female","other"}
 *     ),
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
        $schoolId = $this->route('school')?->id; // route model binding

        return [
            'name' => 'required|string|max:255',

            'userName' => 'required|string|max:255|unique:users,userName',

            'email' => 'nullable|email|max:255|unique:users,email',

            'password' => 'required|string|min:6|max:64|confirmed',

            'birth_date' => 'required|date|before:today',

            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('school_student_profiles', 'student_number')
                    ->where('school_id', $schoolId),
            ],

            'tc_no' => 'required|string|size:11|unique:school_student_profiles,tc_no',

            'phone' => 'nullable|string|max:20',

            'address' => 'nullable|string|max:255',

            'gender' => 'nullable|string|in:male,female',

            'active_class_id' => 'nullable|integer|exists:class_models,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Öğrenci adı zorunludur.',
            'name.string' => 'Öğrenci adı metin olmalıdır.',

            'userName.required' => 'Kullanıcı adı zorunludur.',
            'userName.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',

            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta zaten kayıtlıdır.',

            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
            'password.confirmed' => 'Şifre doğrulaması eşleşmiyor.',
            'password_confirmation.required' => 'Şifre doğrulama alanı zorunludur.',

            'birth_date.required' => 'Doğum tarihi zorunludur.',
            'birth_date.before' => 'Doğum tarihi bugünden önce olmalıdır.',

            'student_number.required' => 'Öğrenci numarası zorunludur.',
            'student_number.unique' => 'Bu öğrenci numarası zaten kayıtlıdır.',

            'tc_no.required' => 'TC Kimlik numarası zorunludur.',
            'tc_no.size' => 'TC Kimlik numarası 11 haneli olmalıdır.',
            'tc_no.unique' => 'Bu TC Kimlik numarası zaten kayıtlıdır.',

            'gender.in' => 'Geçerli bir cinsiyet seçiniz (male, female, other).',

            'active_class_id.exists' => 'Seçilen sınıf mevcut değil veya bu okula ait değil.'
        ];
    }
}
