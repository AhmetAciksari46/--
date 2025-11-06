<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolWeekDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'class_id',
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
            ->whereColumn('school_sessions.class_id', 'school_week_days.class_id');
    }
}
