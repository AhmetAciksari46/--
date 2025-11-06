<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="TeacherUpdateProfileSettingRequest",
 *     type="object",
 *     title="Teacher Update Profile Settings",
 *     required={},
 *     description="Öğretmenin profil ayarlarını güncellemek için kullanılan alanlar.",
 *     @OA\Property(property="phone", type="string", example="+905551234567", nullable=true),
 *     @OA\Property(property="address", type="string", example="İzmir, Türkiye", nullable=true),
 *     @OA\Property(property="schoolId", type="integer", example=3, nullable=true),
 *     @OA\Property(property="branch_id", type="integer", example=4, nullable=true),
 *     @OA\Property(property="img_path", type="string", example="/storage/teachers/avatars/5.png", nullable=true),
 *     @OA\Property(property="status", type="string", example="active", nullable=true),
 *     @OA\Property(property="gender", type="string", example="female", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-02-14", nullable=true),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-09-01", nullable=true),
 *     @OA\Property(property="description", type="string", example="STEM kulübü sorumlusu", nullable=true),
 *     @OA\Property(property="color_code", type="string", example="#ff9800", nullable=true),
 *     @OA\Property(property="emergency_contact_name", type="string", example="Ahmet Öğretmen", nullable=true),
 *     @OA\Property(property="emergency_contact_phone", type="string", example="+905559998877", nullable=true),
 *     @OA\Property(property="emergency_contact_relationship", type="string", example="Kardeşi", nullable=true),
 *     @OA\Property(property="emergency_contact_description", type="string", example="Hafta içi 09:00-18:00 arası ulaşılabilir.", nullable=true)
 * )
 */
class TeacherUpdateProfileSettingRequest extends FormRequest
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
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'schoolId' => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],
            'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
            'img_path' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'start_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'color_code' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_description' => ['nullable', 'string'],
        ];
    }
}
