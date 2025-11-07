<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="StoreSchoolSessionRequest",
 * title="Ders Oturumu Oluşturma İsteği",
 * required={"class_id", "subject_id", "teacher_id", "session_date", "start_time", "end_time"},
 * @OA\Property(property="class_id", type="integer", description="Dersin yapılacağı Sınıf ID", example=5),
 * @OA\Property(property="subject_id", type="integer", description="Dersin (Subject) ID", example=12),
 * @OA\Property(property="teacher_id", type="integer", description="Dersi verecek Öğretmen (User) ID", example=205),
 * @OA\Property(property="session_date", type="string", format="date", example="2025-11-25", description="Oturumun yapılacağı tarih"),
 * @OA\Property(property="start_time", type="string", format="time", example="09:00:00", description="Dersin başlangıç saati (HH:MM:SS)"),
 * @OA\Property(property="end_time", type="string", format="time", example="10:30:00", description="Dersin bitiş saati (HH:MM:SS)"),
 * @OA\Property(property="location", type="string", nullable=true, example="B-Blok 301 Nolu Sınıf", description="Dersin yapılacağı yer"),
 * )
 */
class StoreSchoolSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            // İlişki kontrolleri (Okul aitliği Policy veya Controller'da kontrol edilmeli)
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],

            // Tarih ve Saat kontrolleri
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],

            // Opsiyonel Alanlar
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'class_id.required' => 'Dersin yapılacağı sınıf kimliği zorunludur.',
            'subject_id.required' => 'Ders (Subject) kimliği zorunludur.',
            'teacher_id.required' => 'Öğretmen kimliği zorunludur.',
            'class_id.exists' => 'Geçerli bir sınıf seçilmelidir.',
            'session_date.required' => 'Oturum tarihi zorunludur.',
            'start_time.required' => 'Başlangıç saati zorunludur.',
            'end_time.required' => 'Bitiş saati zorunludur.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
        ];
    }
}
