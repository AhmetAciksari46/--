<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ClassSchedule",
 *     type="object",
 *     title="Class Schedule",
 *     description="Sınıfın haftalık ders programı girişlerini temsil eder.",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="class_model_id", type="integer", example=3, description="Sınıfın id’si (ClassModel)"),
 *     @OA\Property(property="teacher_id", type="integer", example=5, description="Dersi veren öğretmen ID"),
 *     @OA\Property(property="physical_classroom_id", type="integer", example=7, description="Dersin yapıldığı fiziksel sınıf ID"),
 *     @OA\Property(property="subject_id", type="integer", example=12, description="Ders konusu ID (Subject)"),
 *     @OA\Property(property="day_of_week", type="string", example="Monday"),
 *     @OA\Property(property="start_time", type="string", example="09:00"),
 *     @OA\Property(property="end_time", type="string", example="10:00"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="is_successful", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ClassSchedule extends Model
{
    protected $fillable = [
        'class_model_id',
        'teacher_id',
        'physical_classroom_id',
        'subject_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'is_successful'

    ];
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function physicalClassroom()
    {
        return $this->belongsTo(PhysicalClassroom::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
