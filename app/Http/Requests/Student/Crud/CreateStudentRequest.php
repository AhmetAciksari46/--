<?php

namespace App\Http\Requests\Student\Crud;

use Illuminate\Foundation\Http\FormRequest;

class CreateStudentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('student.create');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'userName' => 'required|string|max:255|unique:users,userName',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|min:6',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Öğrenci adı zorunludur.',
            'userName.unique' => 'Bu kullanıcı adı zaten alınmış.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
        ];
    }
}
