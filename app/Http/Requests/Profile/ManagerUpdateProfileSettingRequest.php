<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ManagerUpdateProfileSettingRequest",
 *     type="object",
 *     title="Manager Update Profile Request",
 *     @OA\Property(property="phone", type="string", example="+905551234567", nullable=true),
 *     @OA\Property(property="address", type="string", example="İstanbul, Türkiye", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01", nullable=true),
 *     @OA\Property(property="note", type="string", example="Özel not", nullable=true),
 *     @OA\Property(property="referance", type="string", example="ABC123", nullable=true),
 *     @OA\Property(property="school_id", type="integer", example=2, nullable=true),
 *     @OA\Property(property="payment_reminder", type="boolean", example=true)
 * )
 */

class ManagerUpdateProfileSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Admin veya Manager erişebilsin
        return in_array($user->role, ['admin', 'manager']);
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
            'school_id' => [
                'sometimes',
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    if ($user && $user->role !== 'admin') {
                        $fail('Sadece admin okul bilgisini değiştirebilir.');
                    }
                },
                'exists:schools,id'
            ],
            // ödeme/paket bilgisi sonradan güncellenecek, o yüzden optional
            'payment_reminder' => ['sometimes', 'boolean'],

            'birth_date' => ['nullable', 'date', 'before:today'],
        ];
    }
    public function messages(): array
    {
        return [
            'school_id.exists' => 'Seçilen okul sistemde mevcut değil.',
            'birth_date.before' => 'Doğum tarihi bugünden sonra olamaz.',
            'payment_reminder.boolean' => 'Ödeme hatırlatıcı değeri yalnızca true veya false olabilir.',

        ];
    }
}
