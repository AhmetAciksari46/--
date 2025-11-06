<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'week_count' => 'required|integer|min:1|max:52',
            'has_schedule_module' => 'boolean',
            'has_homework_module' => 'boolean',
            'has_exam_module' => 'boolean',
            'has_chat_module' => 'boolean',
            'has_analytics_module' => 'boolean',
            'has_certificate_module' => 'boolean',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'is_trial' => 'boolean',
            'trial_days' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Paket adı zorunludur.',
            'duration_days.required' => 'Paket süresi (gün) belirtilmelidir.',
            'price.required' => 'Paket fiyatı belirtilmelidir.',
            'week_count.required' => 'Hafta sayısı belirtilmelidir.',
        ];
    }
}
