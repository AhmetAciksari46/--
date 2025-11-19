<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 * schema="SchoolWeek",
 * title="School Week Model",
 * description="Okulun Yıllık Takvimindeki Bir Haftayı Temsil Eder.",
 * @OA\Property(property="id", type="integer", example=10),
 * @OA\Property(property="school_id", type="integer", example=1),
 * @OA\Property(property="package_week_grade_rule_id", type="integer", description="Package week grade rule id", example=5),
 * @OA\Property(property="start_date", type="string", format="date", example="2025-11-24", description="Haftanın başlangıç tarihi"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SchoolWeek extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'package_week_grade_rule_id',
        'start_date',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function rule()
    {
        return $this->belongsTo(PackageWeekGradeRule::class, 'package_week_grade_rule_id');
    }

    public function days()
    {
        return $this->hasMany(SchoolWeekDay::class);
    }

    public function validateDayCount()
    {
        $expected = $this->rule->days_required;
        $actual = $this->days()->count();

        return $expected === $actual;
    }
}
