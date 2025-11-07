<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="SchoolSession",
 * title="School Session Model",
 * description="Belirli bir sınıf, öğretmen, ders, gün ve saate ait ders oturumu.",
 * @OA\Property(property="id", type="integer", example=15),
 * @OA\Property(property="school_id", type="integer", example=1),
 * @OA\Property(property="class_model_id", type="integer", example=2, description="Dersin verildiği sınıf"),
 * @OA\Property(property="teacher_id", type="integer", example=5, description="Dersi veren öğretmenin User ID'si"),
 * @OA\Property(property="subject_id", type="integer", example=3, description="Dersin konusu"),
 * @OA\Property(property="school_week_id", type="integer", example=10, description="Dersin gerçekleştiği haftanın kaydı"),
 * @OA\Property(property="school_day_id", type="integer", example=2, description="Dersin gerçekleştiği günün kaydı"),
 * @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
 * @OA\Property(property="end_time", type="string", format="time", example="10:00:00"),
 * @OA\Property(property="date", type="string", format="date", example="2025-11-25", nullable=true, description="Dersin gerçekleştiği somut tarih (otomatik hesaplanabilir)"),
 * @OA\Property(property="is_cancelled", type="boolean", example=false, description="Dersin iptal edilip edilmediği"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
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
