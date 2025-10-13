<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('package.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'max_students' => 'nullable|integer|min:0',
            'max_teachers' => 'nullable|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',

            'type' => 'required|in:school,student,other',

            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'is_trial' => 'boolean',

            'has_homework_module' => 'boolean',
            'has_exam_module' => 'boolean',
            'has_chat_module' => 'boolean',
            'has_analytics_module' => 'boolean',
            'has_certificate_module' => 'boolean',

            'trial_days' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'img_path' => 'nullable|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Paket adı zorunludur.',
            'duration_days.required' => 'Süre (gün) zorunludur.',
            'price.required' => 'Fiyat zorunludur.',
            'type.in' => 'Geçersiz paket türü seçildi.',
        ];
    }
}
