<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Branch",
 *     type="object",
 *     title="Branch",
 *     description="Akademik branşları temsil eder (örneğin: İngilizce, Matematik).",
 *     @OA\Property(property="id", type="integer", example=3),
 *     @OA\Property(property="name", type="string", example="Matematik"),
 *     @OA\Property(property="slug", type="string", example="matematik"),
 *     @OA\Property(property="code", type="string", example="MATH"),
 *     @OA\Property(property="description", type="string", example="Sayısal ağırlıklı dersleri kapsar."),
 *     @OA\Property(property="color", type="string", example="#0088cc"),
 *     @OA\Property(property="icon", type="string", example="pi pi-calculator"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Branch extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'color',
        'icon',
        'is_active',
    ];

    public function teachers()
    {
        return $this->hasMany(TeacherProfile::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
