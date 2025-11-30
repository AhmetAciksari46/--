<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ClassSchedule",
 *     type="object",
 *     title="Class Schedule",
 *     description="Sınıfın haftalık ders planını temsil eder.",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="school_id", type="integer", example=3, description="Planın ait olduğu okul ID"),
 *     @OA\Property(property="class_model_id", type="integer", example=5, description="Planın ait olduğu sınıf ID"),
 *     @OA\Property(property="subject_id", type="integer", example=2, description="Ders ID"),
 *     @OA\Property(property="day_of_week", type="string", example="monday"),
 *     @OA\Property(property="start_time", type="string", example="08:00"),
 *     @OA\Property(property="end_time", type="string", example="10:00"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ClassSchedule extends Model
{
    protected $fillable = [
        'school_id',
        'class_model_id',
        'subject_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_model_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function lessonSessions()
    {
        return $this->hasMany(LessonSession::class, 'class_schedule_id');
    }
}
