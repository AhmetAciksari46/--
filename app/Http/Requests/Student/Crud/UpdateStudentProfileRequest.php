<?php

namespace App\Http\Requests\Student\Crud;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('student.update');
    }

    public function rules()
    {
        return [
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'active_course_id' => 'nullable|integer',
            'active_class_id' => 'nullable|integer',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'status' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
