<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ManagerUpdateProfileSettingRequest extends FormRequest
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
            'note' => ['nullable', 'string', 'max:500'],
            'referance' => ['nullable', 'string', 'max:255'],

            // sadece gönderildiyse kontrol et
            'schoolId' => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],

            // ödeme/paket bilgisi sonradan güncellenecek, o yüzden optional
            'payment_reminder' => ['sometimes', 'boolean'],

            'birth_date' => ['nullable', 'date', 'before:today'],
        ];
    }
    public function messages(): array
    {
        return [
            'schoolId.exists' => 'Seçilen okul sistemde mevcut değil.',
            'birth_date.before' => 'Doğum tarihi bugünden sonra olamaz.',
        ];
    }
}
