<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['session_id', 'school_student_profile_id', 'status', 'taken_at', 'note'];

    public function session()
    {
        return $this->belongsTo(SchoolSession::class);
    }
    public function studentProfile()
    {
        return $this->belongsTo(SchoolStudentProfile::class, 'school_student_profile_id');
    }
}
