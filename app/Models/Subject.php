<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Subject",
 *     title="Subject Model",
 *     description="Global Ders Kaynağı (ör: Matematik, Türkçe). Tüm paketler ve okullar tarafından ortak kullanılır.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Matematik"),
 *     @OA\Property(property="code", type="string", example="MT101", nullable=true),
 *     @OA\Property(property="description", type="string", example="Sayısal ve mantıksal düşünme becerileri", nullable=true),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** 🔗 İlişkiler **/

    public function packageRules()
    {
        return $this->hasMany(PackageWeekSubjectRule::class);
    }

    public function sessions()
    {
        return $this->hasMany(SchoolSession::class);
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }
}
