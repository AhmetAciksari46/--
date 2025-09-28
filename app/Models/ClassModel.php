<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes'; // migration’da tablo adı classes

    protected $fillable = [
        'name',
        'school_id',
        'teacher_id',
    ];

    /* ======================
     |   Relationships
     ====================== */

    // Bu sınıfın bağlı olduğu okul
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Sınıfın öğretmeni
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Sınıftaki dersler
    public function courses()
    {
        return $this->hasMany(Course::class, 'class_id');
    }

    // Bu sınıfta okuyan öğrenciler (user -> profile_settings ile bağlanacak)
    public function students()
    {
        return $this->hasMany(ProfileSetting::class, 'active_class_id');
    }
}
