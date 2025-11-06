<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'grade',
        'week_no',
        'day_index',
        'subject_id',
        'type',
        'title',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /** 🔗 İlişkiler **/

    // 1️⃣ Bu içerik hangi pakete ait
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // 2️⃣ İçerik hangi derse ait
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // 3️⃣ Öğrenci ödev bağlantısı (ödev yapılmış mı)
    public function studentHomeworks()
    {
        return $this->hasMany(StudentHomework::class);
    }

    // 4️⃣ Öğrenci sınav sonuç bağlantısı
    public function studentQuizzes()
    {
        return $this->hasMany(StudentQuiz::class);
    }

    /** 🎓 Scope: belirli sınıf, hafta ve ders için filtreleme */
    public function scopeForGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeForWeek($query, $week)
    {
        return $query->where('week_no', $week);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /** 🧭 Yardımcı: okunabilir tür adı */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'homework' => 'Ödev',
            'quiz' => 'Sınav',
            'note' => 'Not',
            default => ucfirst($this->type)
        };
    }
}
