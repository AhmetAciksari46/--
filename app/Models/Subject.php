<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 * schema="Subject",
 * title="Subject Model",
 * description="Ders Kaynağı (Örn: Matematik, Fizik). Okula özel olarak oluşturulabilir.",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="school_id", type="integer", example=1, nullable=true, description="Okula özelse okul ID, Global ise NULL"),
 * @OA\Property(property="name", type="string", example="Matematik", description="Dersin Adı"),
 * @OA\Property(property="code", type="string", example="MT101", nullable=true, description="Dersin Kodu"),
 * @OA\Property(property="description", type="string", example="Sayısal ve mantıksal düşünme becerileri", nullable=true),
 * @OA\Property(property="is_active", type="boolean", example=true),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
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
