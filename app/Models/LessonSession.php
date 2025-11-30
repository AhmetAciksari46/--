<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="LessonSession",
 *     type="object",
 *     title="Lesson Session",
 *     description="Haftalık planlanan dersin belirli bir hafta için gerçek ders işlemesidir.",
 *
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="class_schedule_id", type="integer", example=1),
 *     @OA\Property(property="week_number", type="integer", example=5),
 *     @OA\Property(property="date", type="string", format="date", example="2025-11-11"),
 *     @OA\Property(property="teacher_id", type="integer", example=22),
 *     @OA\Property(property="physical_classroom_id", type="integer", example=7),
 *     @OA\Property(
 *          property="status",
 *          type="string",
 *          enum={"scheduled","completed","cancelled","skipped","no_school"},
 *          example="scheduled"
 *     ),
 *     @OA\Property(property="is_attendance_required", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class LessonSession extends Model
{
    protected $fillable = [
        'class_schedule_id',
        'week_number',
        'date',
        'teacher_id',
        'physical_classroom_id',
        'status',
        'is_attendance_required'
    ];
    public function schedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function physicalClassroom()
    {
        return $this->belongsTo(PhysicalClassroom::class, 'physical_classroom_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'lesson_session_id');
    }

    /**
     * Türkçe durum etiketi
     */
    public function getStatusLabelAttribute()
    {
        return [
            'scheduled' => 'Planlandı',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
            'skipped' => 'İşlenmedi',
            'no_school' => 'Tatil',
        ][$this->status] ?? $this->status;
    }
}
