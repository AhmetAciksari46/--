<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Attendance",
 *     type="object",
 *     title="Attendance",
 *     description="Öğrencinin bir ders oturumundaki yoklama bilgisini temsil eder.",
 *
 *     @OA\Property(property="id", type="integer", example=100),
 *     @OA\Property(property="lesson_session_id", type="integer", example=15),
 *     @OA\Property(property="student_id", type="integer", example=55),
 *     @OA\Property(
 *          property="status",
 *          type="string",
 *          enum={"present","absent","late","excused"},
 *          example="present"
 *     ),
 *     @OA\Property(property="absent_excuse_note", type="string", nullable=true, example="Veli tarafından izinli"),
 *     @OA\Property(property="entered_at", type="string", format="date-time", example="2025-11-11T10:05:00"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Attendance extends Model
{
    protected $fillable = [
        'lesson_session_id',
        'student_id',
        'status',
        'absent_excuse_note',
        'entered_at'
    ];

    public function lessonSession()
    {
        return $this->belongsTo(LessonSession::class, 'lesson_session_id');
    }

    public function student()
    {
        return $this->belongsTo(SchoolStudentProfile::class, 'student_id');
    }

    /**
     * Türkçe label
     */
    public function getStatusLabelAttribute()
    {
        return [
            'present' => 'Var',
            'absent' => 'Yok',
            'late' => 'Geç',
            'excused' => 'İzinli',
        ][$this->status] ?? $this->status;
    }
}
