<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="TeacherSubject",
 *     type="object",
 *     title="TeacherSubject",
 *     description="Öğretmen ile ders arasındaki ilişki",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="teacher_id", type="integer", example=5),
 *     @OA\Property(property="subject_id", type="integer", example=3),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TeacherSubject extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'subject_id'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
