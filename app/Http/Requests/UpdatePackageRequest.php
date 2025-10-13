<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return optional(auth()->user())->can('package.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',

            'max_students' => 'sometimes|integer|min:0',
            'max_teachers' => 'sometimes|integer|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',

            'type' => 'sometimes|in:school,student,other',

            'is_active' => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
            'is_trial' => 'sometimes|boolean',

            'has_homework_module' => 'sometimes|boolean',
            'has_exam_module' => 'sometimes|boolean',
            'has_chat_module' => 'sometimes|boolean',
            'has_analytics_module' => 'sometimes|boolean',
            'has_certificate_module' => 'sometimes|boolean',

            'trial_days' => 'sometimes|integer|min:0',
            'sort_order' => 'sometimes|integer',
            'img_path' => 'sometimes|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Paket adı boş olamaz.',
            'duration_days.integer' => 'Süre gün cinsinden olmalıdır.',
            'price.numeric' => 'Fiyat sayısal bir değer olmalıdır.',
        ];
    }
}
