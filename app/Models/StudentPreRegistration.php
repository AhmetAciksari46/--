<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPreRegistration extends Model
{
    protected $fillable = [
        'school_id',
        'student_name',
        'student_surname',
        'student_tc',
        'birth_date',
        'gender',
        'student_phone',
        'student_email',
        'address',
        'mother',
        'father',
        'parent_status',
        'description',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'mother' => 'array',
        'father' => 'array',
        'notes' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
