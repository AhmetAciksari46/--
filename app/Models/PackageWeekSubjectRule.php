<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="PackageWeekSubjectRule",
 *     schema="PackageWeekSubjectRule",
 *     title="Package Week Subject Rule Model",
 *     description="Bir paketin belirli bir haftasında planlanan ders kuralı.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="package_id", type="integer", example=1),
 *     @OA\Property(property="grade", type="integer", description="Kuralın uygulandığı sınıf seviyesi", example=5),
 *     @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=3),
 *     @OA\Property(property="subject_id", type="integer", description="Planlanan dersin ID'si", example=7),
 *     @OA\Property(property="hours", type="integer", description="Hafta için toplam ders saati", example=6),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PackageWeekSubjectRule extends Model
{
    protected $fillable = ['package_id', 'grade', 'week_no', 'subject_id', 'hours'];
    protected $casts = [
        'package_id' => 'integer',
        'grade' => 'integer',
        'week_no' => 'integer',
        'subject_id' => 'integer',
        'hours' => 'integer',
    ];
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
