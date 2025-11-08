<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="CreatePackageRequest",
 * title="Yeni Paket Oluşturma İsteği",
 * description="Admin panelinde yeni bir paket oluşturmak için kullanılan şema.",
 * required={"name", "price", "duration_days", "week_count", "type"},
 * @OA\Property(property="name", type="string", example="40 Haftalık Temel Eğitim Paketi", description="Paketin Adı"),
 * @OA\Property(property="price", type="number", format="float", example=2499.99, description="Paketin Fiyatı"),
 * @OA\Property(property="duration_days", type="integer", example=365, description="Paketin geçerlilik süresi (gün olarak)"),
 * @OA\Property(property="week_count", type="integer", example=40, description="Paketin içerdiği müfredat hafta sayısı"),
 * @OA\Property(property="type", type="string", enum={"school", "student", "other"}, example="school", description="Paketin Tipi"),
 * @OA\Property(property="description", type="string", example="Bu paket temel eğitim modüllerini kapsar.", nullable=true),
 * @OA\Property(property="is_active", type="boolean", example=true, description="Paket aktif mi?"),
 * @OA\Property(property="is_visible", type="boolean", example=true, description="Kullanıcılara görünür mü?"),
 * @OA\Property(property="has_homework_module", type="boolean", example=true, description="Ödev modülü var mı?"),
 * @OA\Property(property="has_schedule_module", type="boolean", example=true, description="Ders programı modülü var mı?"),
 * @OA\Property(property="has_exam_module", type="boolean", example=true, description="Sınav modülü var mı?"),
 * @OA\Property(property="has_chat_module", type="boolean", example=false, description="Canlı sohbet modülü var mı?"),
 * @OA\Property(property="has_analytics_module", type="boolean", example=true, description="Analitik rapor modülü var mı?"),
 * @OA\Property(property="has_certificate_module", type="boolean", example=false, description="Sertifika modülü var mı?"),
 * @OA\Property(property="is_trial", type="boolean", example=false, description="Deneme paketi mi?"),
 * @OA\Property(property="trial_days", type="integer", example=7, description="Deneme gün sayısı (is_trial true ise zorunlu)"),
 * @OA\Property(property="is_sequential_content_required", type="boolean", example=false, description="Sıralı içerik tamamlama kuralı var mı?"),
 * @OA\Property(property="sort_order", type="integer", example=1, description="Listeleme sırası."),
 * @OA\Property(property="img_path", type="string", example="/img/package-basic.png", nullable=true, description="Paket görsel yolu.")
 * )
 */
class CreatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:packages,name'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'price' => 'required|numeric|min:0',
            'week_count' => ['required', 'integer', 'min:1', 'max:52'],
            'type' => ['required', 'in:school,student,other'],

            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'has_homework_module' => ['nullable', 'boolean'],
            'has_schedule_module' => ['nullable', 'boolean'],
            'has_exam_module' => ['nullable', 'boolean'],
            'has_chat_module' => ['nullable', 'boolean'],
            'has_analytics_module' => ['nullable', 'boolean'],
            'has_certificate_module' => ['nullable', 'boolean'],
            'is_trial' => ['nullable', 'boolean'],
            'trial_days' => ['required_if:is_trial,true', 'integer', 'min:0'],
            'is_sequential_content_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'img_path' => ['nullable', 'string', 'max:255'], // (İleride dosya yüklemeyi yönetiriz)
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Paket adı zorunludur.',
            'duration_days.required' => 'Paket süresi (gün) belirtilmelidir.',
            'price.required' => 'Paket fiyatı belirtilmelidir.',
            'week_count.required' => 'Hafta sayısı belirtilmelidir.',
            'name.unique' => 'Bu isimde bir paket zaten mevcut.',
            'price.numeric' => 'Fiyat alanı sayısal bir değer olmalıdır.',
            'duration_days.required' => 'Süre (gün) zorunludur.',
            'duration_days.min' => 'Süre en az 1 gün olmalıdır.',
            'week_count.min' => 'Hafta sayısı en az 1 olmalıdır.',
            'type.required' => 'Paket tipi zorunludur.',
            'type.in' => 'Geçersiz paket tipi seçimi.',
            'trial_days.required_if' => 'Deneme paketi ise deneme gün sayısı zorunludur.',
        ];
    }
}
