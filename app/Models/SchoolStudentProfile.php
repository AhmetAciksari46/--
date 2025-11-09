<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="SchoolStudentProfile",
 *     type="object",
 *     title="SchoolStudentProfile",
 *     description="Okul öğrencisi profil modeli",
 *     @OA\Property(property="id", type="integer", example=12),
 *     @OA\Property(property="user_id", type="integer", example=45),
 *     @OA\Property(property="student_number", type="string", example="2024-001"),
 *     @OA\Property(property="tc_no", type="string", example="12345678901", nullable=true),
 *     @OA\Property(property="phone", type="string", example="+905551234567", nullable=true),
 *     @OA\Property(property="address", type="string", example="Bursa, Türkiye", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2012-06-15", nullable=true),
 *     @OA\Property(property="active_course_id", type="integer", example=3, nullable=true),
 *     @OA\Property(property="active_class_id", type="integer", example=7, nullable=true),
 *     @OA\Property(property="active_additional_class_id", type="integer", example=2, nullable=true),
 *     @OA\Property(property="parent_name", type="string", example="Fatma Veli", nullable=true),
 *     @OA\Property(property="parent_phone", type="string", example="+905559991122", nullable=true),
 *     @OA\Property(property="school_id", type="integer", example=5, nullable=true),
 *     @OA\Property(property="gender", type="string", example="female", nullable=true),
 *     @OA\Property(property="description", type="string", example="Robotik kulübü öğrencisi", nullable=true),
 *     @OA\Property(property="registered_at", type="string", format="date-time", example="2024-01-10T09:00:00Z", nullable=true),
 *     @OA\Property(property="img_path", type="string", example="/storage/students/12.png", nullable=true),
 *     @OA\Property(property="status", type="string", example="active", nullable=true),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="parent_status", type="string", example="reachable", nullable=true),
 *     @OA\Property(property="family_notes", type="string", example="Hafta sonu etkinliklerine katılamaz.", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="user", ref="#/components/schemas/User", nullable=true)
 * )
 */
class SchoolStudentProfile extends Model
{
    use HasFactory;
    //TODO :ADDİTİONAL CLASSROOM İÇİN AYNI ŞEY YAPILACAK.

    protected $fillable = [
        'user_id',
        'student_number',
        'tc_no',
        'phone',
        'address',
        'birth_date',
        'active_course_id',
        'active_class_id',
        'active_additional_class_id',
        'parent_name',
        'parent_phone',
        'school_id',
        'gender',
        'description',
        'registered_at',
        'img_path',
        'status',
        'is_active',
        'parent_status',
        'family_notes',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function activeClass()
    {
        return $this->belongsTo(ClassModel::class, 'active_class_id');
    }
    public function activeAdditionalClass()
    {
        return $this->belongsTo(AdditionalClassRoom::class, 'active_additional_class_room_id');
    }
    public function activeCourse()
    {
        return $this->belongsTo(Course::class, 'active_course_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function parents()
    {
        return $this->hasMany(StudentParent::class);
    }

    public function healthProfile()
    {
        return $this->hasOne(StudentHealthProfile::class);
    }
    public function curriculumOverrides()
    {
        return $this->hasMany(StudentCurriculumOverride::class, 'school_student_profile_id');
    }
}
