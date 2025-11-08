<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 * schema="SchoolDay",
 * title="School Day Model",
 * description="Okulun Haftalık Çalışma Günlerini Temsil Eder.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="school_id", type="integer", example=1),
 * @OA\Property(property="class_model_id", type="integer", example=5, description="Haftalık planın ait olduğu sınıf modeli"),
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=3),
 * @OA\Property(property="day_index", type="integer", description="Haftanın günü (1=Pazartesi, 7=Pazar)", example=1),
 * @OA\Property(property="date", type="string", format="date", description="Takvim tarihi", example="2025-10-06"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SchoolWeekDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'class_model_id',
        'week_no',
        'day_index',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** 🔗 İlişkiler */

    // 1️⃣ Gün bir okula aittir
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // 2️⃣ Gün bir sınıfa aittir
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_model_id');
    }

    // 3️⃣ Günün ait olduğu haftayı bulmak istersek
    public function week()
    {
        return $this->belongsTo(SchoolWeek::class, 'week_no', 'week_no')
            ->whereColumn('school_weeks.school_id', 'school_week_days.school_id');
    }

    // 4️⃣ Bu günün ders oturumları
    public function sessions()
    {
        return $this->hasMany(SchoolSession::class, 'day_index', 'day_index')
            ->whereColumn('school_sessions.week_no', 'school_week_days.week_no')
            ->whereColumn('school_sessions.class_model_id', 'school_week_days.class_model_id');
    }
}
