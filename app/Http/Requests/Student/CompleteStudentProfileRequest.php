<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CompleteStudentProfileRequest",
 *     required={"schoolId","active_class_model_id","birth_date","student_number","tc_no"},
 *     @OA\Property(property="schoolId", type="integer", example=1),
 *     @OA\Property(property="active_class_model_id", type="integer", example=12),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2012-05-10"),
 *     @OA\Property(property="student_number", type="string", example="2025A12"),
 *     @OA\Property(property="tc_no", type="string", example="12345678901"),
 *     @OA\Property(property="parent_name", type="string", example="Ali Yılmaz"),
 *     @OA\Property(property="parent_phone", type="string", example="05551112233"),
 *     @OA\Property(property="gender", type="string", example="erkek"),
 *     @OA\Property(property="address", type="string", example="Ankara, Türkiye")
 * )
 */
class CompleteStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schoolId' => 'required|integer|exists:schools,id',
            'active_class_model_id' => 'required|integer|exists:class_models,id',
            'birth_date' => 'required|date',
            'student_number' => 'required|string|unique:school_student_profiles,student_number',
            'tc_no' => 'required|string|unique:school_student_profiles,tc_no',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'address' => 'nullable|string',
            'gender' => 'nullable|string|in:erkek,kız,diğer',
        ];
    }

    public function messages(): array
    {
        return [
            'schoolId.required' => 'Okul ID zorunludur.',
            'active_class_model_id.required' => 'Sınıf ID zorunludur.',
            'birth_date.required' => 'Doğum tarihi zorunludur.',
            'student_number.required' => 'Öğrenci numarası zorunludur.',
            'tc_no.required' => 'TC Kimlik numarası zorunludur.',
            'tc_no.unique' => 'Bu TC kimlik numarası zaten kayıtlı.',
        ];
    }
}
