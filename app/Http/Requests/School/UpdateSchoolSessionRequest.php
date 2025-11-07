<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="UpdateSchoolSessionRequest",
 * title="Ders Oturumu Güncelleme İsteği",
 * @OA\Property(property="teacher_id", type="integer", description="Dersi verecek Öğretmen (User) ID", example=206),
 * @OA\Property(property="start_time", type="string", format="time", example="10:00:00", description="Dersin başlangıç saati (HH:MM:SS)"),
 * )
 */
class UpdateSchoolSessionRequest extends FormRequest
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
            // İlişki kontrolleri
            'class_id' => ['sometimes', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['sometimes', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['sometimes', 'integer', 'exists:users,id'],

            // Tarih ve Saat kontrolleri
            'session_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i:s'],
            'end_time' => ['sometimes', 'date_format:H:i:s', 'after:start_time'],

            // Opsiyonel Alanlar
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.exists' => 'Geçerli bir sınıf seçilmelidir.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
        ];
    }
}
