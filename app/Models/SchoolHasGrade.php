<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="SchoolHasGrade",
 *     type="object",
 *     title="SchoolHasGrade Model",
 *     description="Okulun sahip olduğu seviye (grade) ilişkisini temsil eder",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="school_id", type="integer", example=2),
 *     @OA\Property(property="grade_id", type="integer", example=4),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SchoolHasGrade extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'grade_id'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
