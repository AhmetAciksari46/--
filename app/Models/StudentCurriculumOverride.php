<?php

namespace App\Models;

use App\Policies\SchoolStudentPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="StudentCurriculumOverride",
 *     type="object",
 *     title="StudentCurriculumOverride",
 *     description="Öğrenci müfredat geçiş (override) modeli",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="school_student_profile_id", type="integer", example=12, description="Bağlı olduğu öğrenci profil ID"),
 *     @OA\Property(property="target_grade", type="integer", example=2, description="Erişim verilecek sınıf seviyesi"),
 *     @OA\Property(property="unlimited_access", type="boolean", example=true, description="Tüm haftalara erişim izni"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-06T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-06T12:00:00Z")
 * )
 */
class StudentCurriculumOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_student_profile_id',
        'target_grade',
        'unlimited_access',
    ];

    protected $casts = [
        'unlimited_access' => 'boolean',
    ];

    /** 🔗 İlişkiler **/

    // 1️⃣ Öğrenci ilişkisi
    /**
     * Öğrenci profili ile ilişki
     */
    public function studentProfile()
    {
        return $this->belongsTo(SchoolStudentProfile::class, 'school_student_profile_id');
    }

    /**
     * Öğrenci profilinden kullanıcıya ulaşma (hasOneThrough)
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            SchoolStudentProfile::class,
            'id',          // Foreign key on SchoolStudentProfile
            'id',          // Foreign key on User
            'school_student_profile_id',
            'user_id'      // Local key on SchoolStudentProfile
        );
    }

    /* ⚙️ Yardımcı Metodlar */

    /**
     * Öğrencinin belirli sınıf için override erişimi var mı?
     */
    public static function hasAccessToGrade(int $profileId, int $grade): bool
    {
        return self::where('school_student_profile_id', $profileId)
            ->where('target_grade', $grade)
            ->exists();
    }

    /**
     * Öğrencinin sınırsız erişim izni var mı?
     */
    public static function hasUnlimitedAccess(int $profileId, int $grade): bool
    {
        return self::where('school_student_profile_id', $profileId)
            ->where('target_grade', $grade)
            ->where('unlimited_access', true)
            ->exists();
    }
}
