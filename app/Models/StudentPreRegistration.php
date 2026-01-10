<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\ParentsStatus;
use App\Enums\StudentStatus;

class StudentPreRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'tc',
        'grade_id',
        'gender',
        'birth_date',
        'phone',
        'email',
        'address',
        'school_id',

        'mother_full_name',
        'mother_phone',
        'mother_job',
        'mother_birth_date',
        'mother_email',

        'father_full_name',
        'father_phone',
        'father_job',
        'father_birth_date',
        'father_email',

        'parents_status',

        'description',
        'note_1',
        'note_2',
        'note_3',

        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'mother_birth_date' => 'date',
        'father_birth_date' => 'date',

        // ✅ Enum casts
        'parents_status' => ParentsStatus::class,
        'status' => StudentStatus::class,
    ];

    // ✅ Grade ilişkisi
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
