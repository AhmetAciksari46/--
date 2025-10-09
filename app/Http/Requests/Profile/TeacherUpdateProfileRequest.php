<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class TeacherUpdateProfileRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'referance' => ['nullable', 'string', 'max:255'],

            // sadece gönderildiyse kontrol et
            'schoolId' => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],

            // ödeme/paket bilgisi sonradan güncellenecek, o yüzden optional
            'payment_reminder' => ['sometimes', 'boolean'],

            'birth_date' => ['nullable', 'date', 'before:today'],
        ];
    }
}
