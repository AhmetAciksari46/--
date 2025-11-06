<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /** 🔗 İlişkiler **/

    // 1️⃣ Bu dersin hangi paket kurallarında geçtiği
    public function packageRules()
    {
        return $this->hasMany(PackageWeekSubjectRule::class);
    }

    // 2️⃣ Bu dersin ait olduğu tüm oturumlar (yani okul ders programı)
    public function sessions()
    {
        return $this->hasMany(SchoolSession::class);
    }

    // 3️⃣ Bu derse ait içerikler (ödev/sınav/soru seti)
    public function contents()
    {
        return $this->hasMany(Content::class);
    }
}
