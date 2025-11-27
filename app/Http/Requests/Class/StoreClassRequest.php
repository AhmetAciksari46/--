<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SchoolHasGrade;

/**
 * @OA\Schema(
 *     schema="StoreClassRequest",
 *     required={"name","teacher_id"},
 *     @OA\Property(property="name", type="string", example="9-A"),
 *     @OA\Property(property="teacher_id", type="integer", example=10),
 *     @OA\Property(property="grade_id", type="integer", example=9),
 *     @OA\Property(property="academic_year", type="string", example="2024-2025"),
 *     @OA\Property(property="description", type="string", example="Fen Lisesi hazırlık sınıfı"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 * )
 */
class StoreClassRequest extends FormRequest
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
            'name'           => 'required|string|max:255',
            'teacher_id'     => 'required|exists:users,id',
            'grade_id'       => 'required|exists:grades,id',
            'academic_year'  => 'nullable|string|max:20',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Sınıf adı zorunludur.',
            'teacher_id.required' => 'Sınıf öğretmeni belirtilmelidir.',
            'teacher_id.exists' => 'Geçersiz öğretmen seçildi.',
            'grade_id.required'   => 'Sınıfın seviye (grade) bilgisi zorunludur.',
            'grade_id.exists'     => 'Belirtilen grade geçerli değildir.',
            'academic_year.required' => 'Akademik yıl zorunludur.'
        ];
    }
    // /**
    //  * Okulun bu grade’i kullanma yetkisini doğrula
    //  */
    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {

    //         // Route model binding’den school'ı al
    //         $school = $this->route('school');
    //         $gradeId = $this->input('grade_id');

    //         if (!$school) {
    //             $validator->errors()->add('school_id', 'Okul bilgisi bulunamadı.');
    //             return;
    //         }

    //         // Okulun bu grade’e sahip olup olmadığını kontrol et
    //         $hasGrade = SchoolHasGrade::where('school_id', $school->id)
    //             ->where('grade_id', $gradeId)
    //             ->exists();

    //         if (!$hasGrade) {
    //             $validator->errors()->add(
    //                 'grade_id',
    //                 'Bu okul belirtilen sınıf seviyesinde (grade) sınıf oluşturma yetkisine sahip değildir.'
    //             );
    //         }
    //     });
    // }
}
