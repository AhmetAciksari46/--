<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'course_id',
    ];

    /* ======================
     |   Relationships
     ====================== */

    // Bu ödev hangi derse bağlı
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
