<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;   
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles,HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'userName',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',

        ];
    }
   


// Öğretmen profili ilişkisi
public function teacherProfile()
{
    return $this->hasOne(TeacherProfile::class);
}

// Öğrenci profili ilişkisi
public function schoolStudentProfile()
{
    return $this->hasOne(SchoolStudentProfile::class);
}
// Öğrenci profili ilişkisi

public function individualStudentProfile()
{
    return $this->hasOne(IndividualStudentProfile::class);
}
// Yöneticilik profili ilişkisi
public function managerProfile()
{
    return $this->hasOne(ManagerProfile::class);
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
