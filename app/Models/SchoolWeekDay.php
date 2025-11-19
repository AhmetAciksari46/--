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
 * @OA\Property(property="school_week_id", type="integer", description="hangi okul haftasına ait olduğu",example=1),
 * @OA\Property(property="day_no", type="integer", example=5, description="Haftalık hangi gününe ait olduğu"),
 * @OA\Property(property="real_date", type="string", format="date", description="Takvim tarihi", example="2025-10-06"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SchoolWeekDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_week_id',
        'day_no',
        'real_date',
    ];

    protected $casts = [
        'real_date' => 'date',
    ];

    /** 🔗 İlişkiler */

    public function week()
    {
        return $this->belongsTo(SchoolWeek::class, 'school_week_id');
    }

    // 4️⃣ Bu günün ders oturumları
    public function sessions()
    {
        return $this->hasMany(SchoolSession::class, 'day_index', 'day_index')
            ->whereColumn('school_sessions.week_no', 'school_week_days.week_no')
            ->whereColumn('school_sessions.class_model_id', 'school_week_days.class_model_id');
    }
}
