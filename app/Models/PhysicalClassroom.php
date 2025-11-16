<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PhysicalClassroom",
 *     type="object",
 *     title="Physical Classroom",
 *     description="Okul içindeki fiziksel derslikleri temsil eder.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="school_id", type="integer", example=2, description="Dersliğin ait olduğu okul ID"),
 *     @OA\Property(property="name", type="string", example="2. Kat A Sınıfı"),
 *     @OA\Property(property="location", type="string", example="2. Kat, Koridor A"),
 *     @OA\Property(property="capacity", type="integer", example=30),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PhysicalClassroom extends Model
{
    protected $fillable = ['school_id', 'name', 'location', 'capacity'];

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
