<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateStudentRequest",
 *     @OA\Property(property="name", type="string", example="Ahmet Can"),
 *     @OA\Property(property="email", type="string", example="ahmetcan@example.com"),
 *     @OA\Property(property="userName", type="string", example="ahmetcan123"),
 *     @OA\Property(property="password", type="string", example="YeniŞifre123"),
 *     @OA\Property(property="phone", type="string", example="05554443322"),
 *     @OA\Property(property="address", type="string", example="İstanbul"),
 *     @OA\Property(property="parent_name", type="string", example="Ali Can"),
 *     @OA\Property(property="parent_phone", type="string", example="05551112233"),
 *     @OA\Property(property="blood_type", type="string", example="A+"),
 *     @OA\Property(property="health_insurance", type="string", example="SGK")
 * )
 */
class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy kontrolü controller içinde yapılır
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $this->route('id'),
            'userName' => 'nullable|string|max:255|unique:users,userName,' . $this->route('id'),
            'password' => 'nullable|string|min:6',

            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',

            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',

            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,bilinmiyor',
            'health_insurance' => 'nullable|in:SGK,özel sağlık sigortası,yeşil kart,sigortasız,diğer',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta zaten kayıtlıdır.',
            'userName.unique' => 'Bu kullanıcı adı zaten alınmıştır.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
            'blood_type.in' => 'Geçerli bir kan grubu seçiniz.',
            'health_insurance.in' => 'Geçerli bir sağlık sigortası türü seçiniz.',
        ];
    }
}
