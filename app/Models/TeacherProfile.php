<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'schoolId',
        'branch_id',
        'img_path',
        'status',
        'gender',
        'is_active',
        'birth_date',
        'start_date',
        'description',
        'color_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schooldId');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
