<?php

namespace App\Http\Requests\StudentPreRegistration;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StorePreRegistrationRequest",
 *     required={"student_name","student_surname"},
 *     @OA\Property(property="student_name", type="string", example="Ahmet"),
 *     @OA\Property(property="student_surname", type="string", example="Yılmaz"),
 *     @OA\Property(property="student_tc", type="string", example="12345678901"),
 *     @OA\Property(property="birth_date", type="string", format="date"),
 *     @OA\Property(property="gender", type="string", example="erkek"),
 *     @OA\Property(property="student_phone", type="string"),
 *     @OA\Property(property="student_email", type="string"),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="mother", type="object"),
 *     @OA\Property(property="father", type="object"),
 *     @OA\Property(property="parent_status", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="notes", type="object")
 * )
 */
class StorePreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_name'    => 'required|string|max:255',
            'student_surname' => 'required|string|max:255',
            'student_tc'      => 'nullable|string|size:11',
            'birth_date'      => 'nullable|date',
            'gender'          => 'nullable|string|in:erkek,kadın',
            'student_phone'   => 'nullable|string|max:20',
            'student_email'   => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'mother'          => 'nullable|array',
            'father'          => 'nullable|array',
            'parent_status'   => 'nullable|string',
            'description'     => 'nullable|string',
            'notes'           => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'student_name.required'    => 'Öğrenci adı zorunludur.',
            'student_name.string'      => 'Öğrenci adı metin olmalıdır.',
            'student_name.max'         => 'Öğrenci adı en fazla 255 karakter olabilir.',

            'student_surname.required' => 'Öğrenci soyadı zorunludur.',
            'student_surname.string'   => 'Öğrenci soyadı metin olmalıdır.',
            'student_surname.max'      => 'Öğrenci soyadı en fazla 255 karakter olabilir.',

            'student_tc.size'          => 'TC kimlik numarası 11 haneli olmalıdır.',

            'birth_date.date'          => 'Doğum tarihi geçerli bir tarih olmalıdır.',


            'gender.in'                => 'Cinsiyet yalnızca erkek veya kadın olabilir.',

            'student_email.email'      => 'Öğrenci e-posta adresi geçerli değil.',

            'mother.array'             => 'Anne bilgileri geçerli formatta değil.',
            'father.array'             => 'Baba bilgileri geçerli formatta değil.',

            'notes.array'              => 'Notlar geçerli formatta değil.',
        ];
    }
}
