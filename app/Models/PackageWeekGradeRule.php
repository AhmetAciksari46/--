<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PackageWeekGradeRule",
 *     title="Package Week Grade Rule Model",
 *     description="Bir paketin belirli bir haftasında uygulanacak sınıf bazlı kural.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="package_id", type="integer", example=1),
 *     @OA\Property(property="grade", type="integer", description="Kuralın uygulandığı sınıf seviyesi", example=5),
 *     @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=3),
 *     @OA\Property(property="days_required", type="integer", description="Bu haftada gerekli minimum gün sayısı", example=4),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PackageWeekGradeRule extends Model
{

    protected $fillable = ['package_id', 'grade', 'week_no', 'days_required'];
    protected $casts = [
        'package_id' => 'integer',
        'grade' => 'integer',
        'week_no' => 'integer',
        'days_required' => 'integer',
    ];
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    // Bu kuralı kullanan okul haftaları
    public function schoolWeeks()
    {
        return $this->hasMany(SchoolWeek::class, 'package_week_grade_rule_id');
    }
    // Bu kuralın içerdiği günlere ait tüm contentler
    // (Ödev, Sınav, Video, Quiz vs.)
    public function contents()
    {
        return $this->hasMany(PackageContent::class, 'week_grade_rule_id');
        // Eğer content tablosu farklıysa burayı düzenleriz
    }
}
