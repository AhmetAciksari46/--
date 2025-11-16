<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="LessonSession",
 *     type="object",
 *     title="LessonSession",
 *     description="Gerçekleşen ders oturumu modeli",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="class_schedule_id", type="integer", example=3),
 *     @OA\Property(property="teacher_id", type="integer", example=5),
 *     @OA\Property(property="date", type="string", format="date", example="2025-11-10"),
 *     @OA\Property(property="is_completed", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class LessonSession extends Model
{
    protected $fillable = ['class_schedule_id', 'date', 'teacher_id', 'is_completed'];

    public function schedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
