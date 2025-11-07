<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="PackageWeekGradeRule",
 * title="Package Week Grade Rule Model",
 * description="Bir Paketin Belirli Bir Haftasında İzin Verilen Derece Seviyesi Kuralı.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="package_id", type="integer", example=1),
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=3),
 * @OA\Property(property="grade_level", type="string", description="İzin verilen derece/seviye", example="HighSchoolGrade10"),
 * @OA\Property(property="is_mandatory", type="boolean", example=true, description="Bu kuralın zorunlu olup olmadığı"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PackageWeekGradeRule extends Model
{

    protected $fillable = ['package_id', 'grade', 'week_no', 'days_required'];
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
