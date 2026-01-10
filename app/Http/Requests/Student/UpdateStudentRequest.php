<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateStudentRequest",
 *     type="object",
 *     description="Öğrenci güncelleme isteği. Alanlar opsiyoneldir (partial update).",
 *
 *     @OA\Property(property="name", type="string", maxLength=255, example="Mehmet Can Demir"),
 *     @OA\Property(property="userName", type="string", maxLength=255, example="mehmetcan"),
 *     @OA\Property(property="email", type="string", format="email", example="mehmetcan@example.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="User aktiflik durumu"),
 *
 *     @OA\Property(property="phone", type="string", maxLength=30, example="+905554445566"),
 *     @OA\Property(property="address", type="string", maxLength=255, example="Ankara, Türkiye"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2014-05-20"),
 *     @OA\Property(property="gender", type="string", enum={"male","female"}, example="male"),
 *
 *     @OA\Property(property="student_number", type="string", maxLength=50, example="2024-101"),
 *     @OA\Property(property="tc_no", type="string", minLength=11, maxLength=11, example="12345678901"),
 *
 *     @OA\Property(property="active_class_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="active_course_id", type="integer", nullable=true, example=10),
 *
 *     @OA\Property(property="img_path", type="string", maxLength=255, nullable=true, example="uploads/students/profile.jpg"),
 *     @OA\Property(property="status", type="string", enum={"active","passive","graduated","deleted"}, example="active"),
 *
 *     @OA\Property(property="description", type="string", maxLength=500, nullable=true, example="Öğrenci hakkında açıklama"),
 *     @OA\Property(property="family_notes", type="string", maxLength=500, nullable=true, example="Aile ile ilgili notlar"),
 *
 *     @OA\Property(property="registered_at", type="string", format="date", nullable=true, example="2026-01-10"),
 *
 *     @OA\Property(property="profile_is_active", type="boolean", example=true, description="Profile aktiflik durumu (school_student_profile.is_active)")
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
            // ✅ USER TABLE
            'name' => 'sometimes|string|max:255',
            'userName' => 'sometimes|string|max:255|unique:users,userName,' . $this->student->id,
            'email' => 'sometimes|nullable|email|max:255|unique:users,email,' . $this->student->id,
            'is_active' => 'sometimes|boolean',

            // ✅ PROFILE TABLE
            'phone' => 'sometimes|nullable|string|max:30',
            'address' => 'sometimes|nullable|string|max:255',

            'birth_date' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|nullable|in:male,female',

            'student_number' => 'sometimes|nullable|string|max:50',
            'tc_no' => 'sometimes|nullable|string|size:11',

            'active_class_id' => 'sometimes|nullable|integer',
            'active_course_id' => 'sometimes|nullable|integer',

            'img_path' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|nullable|in:active,passive,graduated,deleted',

            'description' => 'sometimes|nullable|string|max:500',
            'family_notes' => 'sometimes|nullable|string|max:500',

            'registered_at' => 'sometimes|nullable|date',
            'profile_is_active' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta zaten kullanımda.',
            'userName.unique' => 'Bu kullanıcı adı zaten kullanımda.',
            'birth_date.before' => 'Doğum tarihi bugünden önce olmalıdır.',
            'gender.in' => 'Cinsiyet sadece male veya female olabilir.',
            'tc_no.size' => 'TC Kimlik No 11 haneli olmalıdır.',
            'status.in' => 'Status geçersiz.',
        ];
    }
}
