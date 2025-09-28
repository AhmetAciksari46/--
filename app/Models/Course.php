<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'class_id',
    ];

    /* ======================
     |   Relationships
     ====================== */

    // Bu dersin bağlı olduğu sınıf
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    // Dersin ödevleri
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
