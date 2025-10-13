<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        // Kullanıcı aboneliği güncelleme yetkisini kontrol etmek istersen buraya policy ekle
        return $this->user() ? true : false;
    }

    public function rules()
    {
        return [
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
            'payment_method' => 'sometimes|string|max:100',
            'payment_reference' => 'sometimes|string|max:255',

            'status' => 'sometimes|in:active,expired,cancelled',
            'auto_renew' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'note' => 'nullable|string',

            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ];
    }
    public function messages()
    {
        return [
            'package_id.exists' => 'Seçilen paket mevcut değil.',
            'price.numeric' => 'Fiyat geçerli bir sayı olmalıdır.',
            'currency.size' => 'Para birimi 3 karakter olmalıdır (ör: TRY).',
            'end_date.after' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.',
            'payment_status.in' => 'Geçersiz ödeme durumu.',
            'status.in' => 'Geçersiz abonelik durumu.',
        ];
    }
}
