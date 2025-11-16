<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User Model",
 *     description="Kullanıcı modelini temsil eder.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Ahmet Açısarı"),
 *     @OA\Property(property="email", type="string", example="ahmet@example.com"),
 *     @OA\Property(property="role", type="string", example="admin"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'userName',
        'email',
        'password',
        'role',
        'is_active',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',

        ];
    }

    /* ========================================
     |  🔄 Rol Senkronizasyonu (Spatie)
     ======================================== */
    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->role && !$user->hasRole($user->role)) {
                $user->assignRole($user->role);
            }
        });

        static::updating(function ($user) {
            // Eğer rol kolonunda değişiklik varsa, Spatie tablosuna da uygula
            if ($user->isDirty('role') && $user->role) {
                $user->syncRoles([$user->role]);
            }
        });
    }

    // Öğretmen profili ilişkisi
    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class, 'user_id', 'id');
    }

    // Öğrenci profili ilişkisi
    public function schoolStudentProfile()
    {
        return $this->hasOne(SchoolStudentProfile::class, 'user_id', 'id');
    }
    // Öğrenci profili ilişkisi

    public function individualStudentProfile()
    {
        return $this->hasOne(IndividualStudentProfile::class, 'user_id', 'id');
    }
    // Yöneticilik profili ilişkisi
    public function managerProfile()
    {
        return $this->hasOne(ManagerProfile::class, 'user_id', 'id');
    }


    // Eğer kullanıcı bir okul yöneticisiyse
    public function school()
    {
        return $this->hasOne(School::class, 'manager_id');
    }

    // Eğer kullanıcı öğretmense
    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'teacher_id');
    }

    // Eğer kullanıcı öğrenciyse aktif sınıf/kursta olur
    public function activeClass()
    {
        return $this->belongsTo(ClassModel::class, 'active_class_id');
    }

    public function activeCourse()
    {
        return $this->belongsTo(Course::class, 'active_course_id');
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }
    // Abonelikleri (okul yöneticisi veya bireysel öğrenci için)
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /* ======================
     |   Role Helpers
     ====================== */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isSchoolStudent()
    {
        return $this->role === 'schoolstudent';
    }

    public function isIndividualStudent()
    {
        return $this->role === 'individualstudent';
    }
}
