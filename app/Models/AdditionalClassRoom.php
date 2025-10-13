<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalClassRoom extends Model
{
    protected $fillable = [
        'name',
        'school_id',
        'teacher_id',
    ];
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function courses()
    {
        return $this->hasMany(Course::class, 'class_id');
    }
    public function students()
    {
        return $this->hasMany(SchoolStudentProfile::class, 'active_additional_class_id');
    }
}
