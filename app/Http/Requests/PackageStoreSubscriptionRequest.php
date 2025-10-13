<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageStoreSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    public function authorize()
    {
        // Kullanıcı giriş yapmış olmalı (route auth middleware ile de sağla)
        return $this->user() ? true : false;
    }

    public function rules()
    {
        return [
            // Polymorphic hedef (frontend 'User' veya 'App\\Models\\User' tarzı gönderebilir).
            // Ben burada 'subscribable_type' olarak tam model sınıfını bekliyorum.
            'subscribable_type' => 'required|string',
            'subscribable_id' => 'required|integer',

            'package_id' => 'required|exists:packages,id',

            // Opsiyonel: eğer ödeme işlemi frontend tarafında yapıldıysa
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',

            // Eğer frontend tarih göndermiyorsa, backend paket süresine göre atar
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',

            'auto_renew' => 'sometimes|boolean',
            'note' => 'nullable|string',
        ];
    }
    public function messages()
    {
        return [
            'subscribable_id.required' => 'Abonelik sahibi ID bilgisi zorunludur.',
            'subscribable_type.required' => 'Abonelik tipi (ör. user, school) belirtilmelidir.',
            'package_id.required' => 'Paket seçimi zorunludur.',
            'package_id.exists' => 'Seçilen paket bulunamadı.',

            'price.required' => 'Fiyat bilgisi zorunludur.',
            'price.numeric' => 'Fiyat geçerli bir sayı olmalıdır.',
            'currency.size' => 'Para birimi 3 karakter olmalıdır (ör: TRY).',

            'start_date.required' => 'Başlangıç tarihi zorunludur.',
            'end_date.after' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.',

            'payment_status.in' => 'Geçersiz ödeme durumu.',
            'status.in' => 'Geçersiz abonelik durumu.',
        ];
    }
}
