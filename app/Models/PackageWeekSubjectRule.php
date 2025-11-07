<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="PackageWeekSubjectRule",
 * title="Package Week Subject Rule Model",
 * description="Bir Paketin Belirli Bir Haftasında Yer Alması Gereken Ders Kuralı.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="package_id", type="integer", example=1),
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=3),
 * @OA\Property(property="subject_id", type="integer", description="İzin verilen dersin ID'si", example=5),
 * @OA\Property(property="is_mandatory", type="boolean", example=true, description="Bu kuralın zorunlu olup olmadığı"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PackageWeekSubjectRule extends Model
{
    protected $fillable = ['package_id', 'grade', 'week_no', 'subject_id', 'hours'];
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
