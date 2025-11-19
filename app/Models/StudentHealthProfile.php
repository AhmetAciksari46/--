<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SchoolStudentProfile;

class StudentHealthProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
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
