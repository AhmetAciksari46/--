<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="UpdatePackageRequest",
 * title="Paket Güncelleme İsteği",
 * description="Admin panelinde mevcut bir paketi güncellemek için kullanılan şema. Alanlar isteğe bağlıdır (Sadece güncellenmek istenen alanlar gönderilir).",
 * @OA\Property(property="name", type="string", example="40 Haftalık Premium Paket", description="Paketin Adı"),
 * @OA\Property(property="price", type="number", format="float", example=3499.99, description="Paketin Fiyatı"),
 * @OA\Property(property="duration_days", type="integer", example=365, description="Paketin geçerlilik süresi (gün olarak)"),
 * @OA\Property(property="week_count", type="integer", example=40, description="Paketin içerdiği müfredat hafta sayısı"),
 * @OA\Property(property="type", type="string", enum={"school", "student", "other"}, example="school", description="Paketin Tipi"),
 * @OA\Property(property="description", type="string", example="Tüm modüllerin en kapsamlı versiyonu.", nullable=true),
 * @OA\Property(property="is_active", type="boolean", example=true, description="Paket aktif mi?"),
 * @OA\Property(property="has_homework_module", type="boolean", example=true),
 * @OA\Property(property="is_trial", type="boolean", example=false),
 * @OA\Property(property="trial_days", type="integer", example=7, description="Deneme süresi (sadece is_trial true ise geçerli)"),
 * )
 */
class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        $packageId = $this->route('package')->id ?? null;
        return [
            // 'sometimes' kullanıyoruz çünkü güncelleme isteklerinde tüm alanlar zorunlu değildir.
            'name' => [
                'sometimes',
                'string',
                'max:255',
                // Güncellenen paketin adını unique kontrolünden hariç tutar
                Rule::unique('packages', 'name')->ignore($packageId)
            ],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'duration_days' => ['sometimes', 'integer', 'min:1'],
            'week_count' => ['sometimes', 'integer', 'min:1'],
            'type' => ['sometimes', 'in:school,student,other'],

            // Modül/Özellik Kontrolleri (Hepsi 'sometimes' veya 'nullable')
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'is_visible' => ['sometimes', 'boolean'],
            'has_homework_module' => ['sometimes', 'boolean'],
            'has_schedule_module' => ['sometimes', 'boolean'],
            'has_exam_module' => ['sometimes', 'boolean'],
            'has_chat_module' => ['sometimes', 'boolean'],
            'has_analytics_module' => ['sometimes', 'boolean'],
            'has_certificate_module' => ['sometimes', 'boolean'],
            'is_trial' => ['sometimes', 'boolean'],
            'trial_days' => [
                'nullable',
                Rule::requiredIf(fn() => $this->boolean('is_trial')),
                'integer',
                'min:0'
            ],
            'is_sequential_content_required' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'img_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Bu isimde bir paket zaten mevcut.',
            'price.numeric' => 'Fiyat alanı sayısal bir değer olmalıdır.',
            'duration_days.min' => 'Süre en az 1 gün olmalıdır.',
            'week_count.min' => 'Hafta sayısı en az 1 olmalıdır.',
            'type.in' => 'Geçersiz paket tipi seçimi.',
        ];
    }
}
