<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolStudentProfile extends Model
{
     use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'birth_date',
        'active_course_id',
        'active_class_id',
        'parent_name',
        'parent_phone',
        'schoolId',
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
}
