<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndividualStudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'active_course_id',
        'parent_name',
        'parent_phone',
        'payment_reminder',
        'birth_date',
    ];

        public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activeCourse()
    {
        return $this->belongsTo(Course::class, 'active_course_id');
    }
}
