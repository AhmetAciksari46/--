<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SchoolStudentProfile;

/**
 * @OA\Schema(
 *     schema="ClassModel",
 *     type="object",
 *     title="Class Model",
 *     description="Sınıf modelini temsil eder",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="9-A"),
 *     @OA\Property(property="school_id", type="integer", example=1),
 *     @OA\Property(property="teacher_id", type="integer", example=3),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-01T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-01T12:00:00.000000Z")
 * )
 */
class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'class_models'; // migration’da tablo adı classes

    protected $fillable = [
        'name',
        'school_id',
        'teacher_id',
        'grade_id',
        'academic_year',
        'description',
        'is_active',
    ];



    // Bu sınıfın bağlı olduğu okul
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Sınıfın öğretmeni
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Sınıftaki dersler
    public function courses()
    {
        return $this->hasMany(Course::class, 'class_model_id');
    }

    // Bu sınıfta okuyan öğrenciler (user -> profile_settings ile bağlanacak)
    public function students()
    {
        return $this->hasMany(SchoolStudentProfile::class, 'active_class_id');
    }
    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'class_model_id');
    }
}
