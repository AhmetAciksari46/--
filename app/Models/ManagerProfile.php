<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="ManagerProfile",
 *     type="object",
 *     title="Manager Profile",
 *     description="Yönetici profil modeli",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="phone", type="string", example="+905551234567", nullable=true),
 *     @OA\Property(property="address", type="string", example="İstanbul, Türkiye", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01", nullable=true),
 *     @OA\Property(property="note", type="string", example="Özel not", nullable=true),
 *     @OA\Property(property="referance", type="string", example="ABC123", nullable=true),
 *     @OA\Property(property="school_id", type="integer", example=2, nullable=true),
 *     @OA\Property(property="payment_reminder", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="school",
 *         ref="#/components/schemas/School",
 *         nullable=true
 *     )
 * )
 */

class ManagerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'note',
        'referance',
        'school_id',
        'payment_reminder',
        'birth_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
}
