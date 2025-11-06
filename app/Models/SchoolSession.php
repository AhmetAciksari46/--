<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSession extends Model
{
    protected $fillable = [
        'school_id',
        'class_model_id',
        'week_no',
        'day_index',
        'subject_id',
        'teacher_id',
        'start_time',
        'end_time'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_model_id');
    }
}
