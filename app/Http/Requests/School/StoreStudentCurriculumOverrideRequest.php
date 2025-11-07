<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 * schema="StoreStudentCurriculumOverrideRequest",
 * title="Müfredat Geçersiz Kılma Oluşturma İsteği",
 * required={"student_id", "curriculum_id", "override_status"},
 * @OA\Property(property="student_id", type="integer", description="Geçersiz kılınan öğrencinin ID'si", example=105),
 * @OA\Property(property="curriculum_id", type="integer", description="Geçersiz kılınan Müfredat (Lesson/Unit) ID'si", example=50),
 * @OA\Property(property="override_status", type="string", description="Geçersiz kılma durumu (Assigned, Completed, Skipped)", example="Assigned"),
 * @OA\Property(property="reason", type="string", nullable=true, description="Geçersiz kılma nedeni/açıklaması", example="Öğrenci bu üniteyi önceki okulunda tamamladı."),
 * )
 */
class StoreStudentCurriculumOverrideRequest extends FormRequest
{
    /**
     * Sadece Admin veya Manager gibi yetkili roller bu işlemi yapabilmelidir.
     */
    public function authorize(): bool
    {
        // Yetkilendirme StudentCurriculumOverridePolicy'de kontrol edilecektir.
        return true;
    }

    public function rules(): array
    {
        // Not: 'override_status' için geçerli durumları (Assigned, Completed, Skipped) içeren
        // bir veritabanı tablosu veya sabit (enum) olduğunu varsayıyoruz.
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'], // Öğrenci ID'si
            'curriculum_id' => ['required', 'integer', 'exists:curriculums,id'], // Müfredat Öğesi ID'si (Lesson/Unit)
            'override_status' => [
                'required',
                'string',
                // Örnek: Belirli değerlerle kısıtlama
                Rule::in(['Assigned', 'Completed', 'Skipped', 'InProgress']),
            ],
            'reason' => ['nullable', 'string', 'max:500'], // Neden
            'start_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Öğrenci kimliği zorunludur.',
            'curriculum_id.required' => 'Müfredat kimliği zorunludur.',
            'curriculum_id.exists' => 'Geçersiz bir müfredat öğesi kimliği.',
            'override_status.required' => 'Geçersiz kılma durumu zorunludur.',
            'override_status.in' => 'Geçersiz kılma durumu Assigned, Completed, Skipped veya InProgress olmalıdır.',
            'completion_date.after_or_equal' => 'Tamamlanma tarihi, başlangıç tarihinden sonra veya eşit olmalıdır.',
        ];
    }
}
