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
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası (1-52)", example=5),
 * @OA\Property(property="start_date", type="string", format="date", example="2025-11-24", description="Haftanın başlangıç tarihi"),
 * @OA\Property(property="is_holiday", type="boolean", example=false, description="Bu haftanın tatil olup olmadığı"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SchoolWeek extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'week_no',
        'start_date',
        'is_holiday',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
        'start_date' => 'date',
    ];

    /** 🔗 İlişkiler */

    // 1️⃣ Bir hafta bir okula aittir
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // 2️⃣ Haftanın günleri
    public function days()
    {
        return $this->hasMany(SchoolWeekDay::class, 'week_no', 'week_no')
            ->whereColumn('school_week_days.school_id', 'school_weeks.school_id')
            ->orderBy('day_index');
    }
}
