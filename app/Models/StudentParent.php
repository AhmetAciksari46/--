<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use app\Models\SchoolStudentProfile;

class StudentParent extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'type',
        'relationship',
        'name',
        'phone',
        'tc_no',
        'job',
        'birth_date',
        'status',
        'address',
        'is_parent'
    ];


    public function profile()
    {
        return $this->belongsTo(SchoolStudentProfile::class, 'school_student_profile_id');
    }
}
