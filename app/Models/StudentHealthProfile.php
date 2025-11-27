<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SchoolStudentProfile;

/**
 * @OA\Schema(
 *   schema="StudentHealthProfile",
 *   title="Öğrenci Sağlık Modeli",
 *   description="Öğrenci sağlık bilgileri şeması",
 *
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      example=1,
 *      description="Kayıt ID"
 *   ),
 *
 *   @OA\Property(
 *      property="school_student_profile_id",
 *      type="integer",
 *      example=55,
 *      description="Öğrencinin profil kimliği"
 *   ),
 *
 *   @OA\Property(
 *      property="has_chronic_disease",
 *      type="boolean",
 *      example=true,
 *      description="Öğrencinin kronik hastalığı var mı?"
 *   ),
 *
 *   @OA\Property(
 *      property="chronic_disease_description",
 *      type="string",
 *      example="Astım ve KOAH",
 *      nullable=true,
 *      description="Kronik hastalık açıklaması"
 *   ),
 *
 *   @OA\Property(
 *      property="allergies",
 *      type="string",
 *      example="Fıstık ve çiğ balık",
 *      nullable=true,
 *      description="Alerjiler"
 *   ),
 *
 *   @OA\Property(
 *      property="medications",
 *      type="string",
 *      example="Ventolin, Bricanyl",
 *      nullable=true,
 *      description="Düzenli kullanılan ilaçlar"
 *   ),
 *
 *   @OA\Property(
 *      property="special_needs",
 *      type="string",
 *      example="Fiziksel destek gerektirir",
 *      nullable=true,
 *      description="Özel ihtiyaçlar"
 *   ),
 *
 *   @OA\Property(
 *      property="doctor_name",
 *      type="string",
 *      example="Dr. Ahmet Yalçın",
 *      nullable=true,
 *      description="Doktorun adı"
 *   ),
 *
 *   @OA\Property(
 *      property="doctor_phone",
 *      type="string",
 *      example="0535 444 22 11",
 *      nullable=true,
 *      description="Doktor telefon numarası"
 *   ),
 *
 *   @OA\Property(
 *      property="blood_type",
 *      type="string",
 *      enum={"A+","A-","B+","B-","AB+","AB-","O+","O-","bilinmiyor"},
 *      example="O+",
 *      description="Kan grubu"
 *   ),
 *
 *   @OA\Property(
 *      property="health_insurance",
 *      type="string",
 *      enum={"SGK","özel sağlık sigortası","yeşil kart","sigortasız","diğer"},
 *      example="SGK",
 *      description="Sağlık sigortası türü"
 *   ),
 *
 *   @OA\Property(
 *      property="created_at",
 *      type="string",
 *      format="date-time",
 *      example="2025-01-14T12:45:23Z",
 *      description="Kayıt oluşturulma tarihi"
 *   ),
 *
 *   @OA\Property(
 *      property="updated_at",
 *      type="string",
 *      format="date-time",
 *      example="2025-01-15T09:16:40Z",
 *      description="Kayıt güncellenme tarihi"
 *   )
 * )
 */
class StudentHealthProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_student_profile_id',
        'has_chronic_disease',
        'chronic_disease_description',
        'allergies',
        'medications',
        'special_needs',
        'doctor_name',
        'doctor_phone',
        'blood_type',
        'health_insurance'
    ];


    public function profile()
    {
        return $this->belongsTo(SchoolStudentProfile::class, 'school_student_profile_id');
    }
}
