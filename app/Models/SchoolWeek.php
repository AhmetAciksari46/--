<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
            ->whereColumn('school_week_days.school_id', 'school_weeks.school_id');
    }
}
