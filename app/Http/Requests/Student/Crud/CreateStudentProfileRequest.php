<?php

namespace App\Http\Requests\Student\Crud;

use Illuminate\Foundation\Http\FormRequest;

class CreateStudentProfileRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('student.create');
    }
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'schoolId' => 'required|integer',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birth_date' => 'required|date',
            'student_number' => 'required|string|unique:school_student_profiles,student_number',
            'tc_no' => 'required|string|unique:school_student_profiles,tc_no',
            'gender' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Kullanıcı ID gereklidir.',
            'schoolId.required' => 'Okul ID gereklidir.',
            'student_number.unique' => 'Bu öğrenci numarası zaten kayıtlı.',
            'tc_no.unique' => 'Bu TC numarası zaten kayıtlı.',
        ];
    }
}
