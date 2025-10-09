<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolStudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'tc_no',
        'phone',
        'address',
        'birth_date',
        'active_course_id',
        'active_class_id',
        'parent_name',
        'parent_phone',
        'schoolId',
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
        return $this->belongsTo(User::class);
    }

    public function activeClass()
    {
        return $this->belongsTo(ClassModel::class, 'active_class_id');
    }

    public function activeCourse()
    {
        return $this->belongsTo(Course::class, 'active_course_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class, 'schooldId');
    }
    public function parents()
    {
        return $this->hasMany(StudentParent::class);
    }

    public function healthProfile()
    {
        return $this->hasOne(StudentHealthProfile::class);
    }
}
