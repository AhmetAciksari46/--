<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\GroupType;

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
    protected $guard_name = 'sanctum';   // 👈 EKLEMEK ZORUNDASIN


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
    public function groupMemberships()
    {
        return $this->hasMany(GroupMember::class, 'user_id');
    }

    public function classroomGroups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id');
    }
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'user_permissions'
        )->withTimestamps();
    }
    public function getSchoolId()
    {
        // Admin → tüm okullara erişebilir, istersek null döndürür
        if ($this->hasRole('admin')) {
            return null;
        }

        // Manager → manager_profiles.school_id
        if ($this->hasRole('manager')) {
            return $this->managerProfile->school_id ?? null;
        }

        // Teacher → teacher_profiles.school_id
        if ($this->hasRole('teacher')) {
            return $this->teacherProfile->school_id ?? null;
        }

        // Student → school_student_profiles.school_id
        if ($this->hasRole('schoolstudent')) {
            return $this->schoolStudentProfile->school_id ?? null;
        }

        return null;
    }


    public function isMemberOf(Group $group): bool
    {

        // ADMIN → TÜM GRUPLARIN OTOMATİK ÜYESİ
        if ($this->hasRole('admin')) {
            return true;
        }

        /** ---------------------------------------------
         * GLOBAL GRUPLAR
         * -------------------------------------------- */

        // Global Genel Grup → tüm kullanıcılar otomatik üye
        if ($group->type === GroupType::GlobalGeneral) {
            return true;
        }

        // Global Manager Grubu → manager + admin
        if ($group->type === GroupType::GlobalManager) {
            return $this->hasRole('manager');
        }

        // Global Yönetim Grubu → admin + manager + teacher
        if ($group->type === GroupType::GlobalYonetim) {
            return $this->hasAnyRole(['manager', 'teacher']);
        }

        /** ---------------------------------------------
         * SCHOOL GRUPLARI
         * -------------------------------------------- */

        $userSchoolId = $this->getSchoolId();

        if ($group->school_id && $userSchoolId !== $group->school_id) {
            return false;
        }

        // Okul Genel Grup → okulun tüm üyeleri otomatik üye
        if ($group->type === GroupType::SchoolGeneral) {
            return true;
        }

        // Okul Yönetim Grubu → sadece school's manager + admin
        if ($group->type === GroupType::SchoolManagement) {
            return $this->hasRole('manager');
        }

        /** ---------------------------------------------
         * CLASSROOM GRUBU
         * -------------------------------------------- */

        if ($group->type === GroupType::Classroom) {

            // Öğrenci veya Öğretmen explicit group_members içinde olmalı
            $isExplicitMember = $group->members()
                ->where('user_id', $this->id)
                ->exists();

            if ($isExplicitMember) {
                return true;
            }

            // Manager (okul manager’ı) classroom grubuna implicit üye
            if ($this->hasRole('manager')) {
                return $this->school_id === $group->school_id;
            }

            return false;
        }

        return false;
    }
}
