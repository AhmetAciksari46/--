<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'name' => 'sometimes|string|max:255',
            'userName' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'new_password' => 'nullable|string|min:6|confirmed',
            'current_password' => 'nullable|string',
        ];

        if ($this->has('new_password')) {
            $rules['current_password'] = 'required|string';
        }

        return $rules;
    }
    protected function passedValidation()
    {
        $user = $this->user();
        if ($this->filled('current_password')) {
            if (!Hash::check($this->input('current_password'), $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Mevcut şifre yanlış.'],
                ]);
            }
        }
    }
    public function messages(): array
    {
        return [
            'name.string' => 'Ad alanı geçerli bir metin olmalıdır.',
            'userName.unique' => 'Bu kullanıcı adı zaten alınmış.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'new_password.confirmed' => 'Yeni şifre tekrarı eşleşmiyor.',
            'current_password.required' => 'Yeni şifre belirlerken mevcut şifre zorunludur.',
        ];
    }
}
