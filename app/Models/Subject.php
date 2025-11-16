<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Subject",
 *     type="object",
 *     title="Subject",
 *     description="Ders bilgilerini ve hiyerarşisini temsil eder.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="English Reading"),
 *     @OA\Property(property="branch_id", type="integer", example=2),
 *     @OA\Property(property="parent_id", type="integer", example=1),
 *     @OA\Property(property="grade_id", type="integer", example=3),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'branch_id', 'parent_id', 'grade_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function parent()
    {
        return $this->belongsTo(Subject::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Subject::class, 'parent_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
