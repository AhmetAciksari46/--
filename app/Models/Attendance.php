<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Attendance",
 *     type="object",
 *     title="Attendance",
 *     description="Ders yoklama kayıtlarını temsil eder.",
 *     @OA\Property(property="id", type="integer", example=15),
 *     @OA\Property(property="class_schedule_id", type="integer", example=7, description="Yoklamanın ait olduğu dersin programı ID’si"),
 *     @OA\Property(property="student_id", type="integer", example=32, description="Yoklaması alınan öğrenci ID’si (users tablosundan)"),
 *     @OA\Property(property="date", type="string", format="date", example="2025-11-12", description="Yoklamanın alındığı tarih"),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"present", "absent", "late", "excused"},
 *         example="present",
 *         description="Öğrencinin derse katılım durumu"
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-12T09:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-12T10:00:00Z")
 * )
 */
class Attendance extends Model
{
    protected $fillable = ['class_schedule_id', 'student_id', 'date', 'status'];

    public function schedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function student()
    {
        return $this->belongsTo(SchoolStudentProfile::class, 'student_id');
    }
}
