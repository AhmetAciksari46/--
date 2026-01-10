<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="AcademicYear",
 *     type="object",
 *     title="AcademicYear",
 *     description="Akademik yıl bilgisini temsil eder (örn: 2024-2025). Admin tarafından yönetilir.",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="2024-2025", description="Akademik yıl etiketi"),
 *
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         example="2024-09-01",
 *         nullable=true,
 *         description="Akademik yıl başlangıç tarihi"
 *     ),
 *
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         example="2025-06-30",
 *         nullable=true,
 *         description="Akademik yıl bitiş tarihi"
 *     ),
 *
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         example=true,
 *         description="Bu akademik yıl aktif mi? Listelerde gösterilir mi?"
 *     ),

 *
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2026-01-10T12:00:00Z"
 *     ),
 *
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2026-01-10T12:00:00Z"
 *     )
 * )
 */
class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    /* -------------------------------------------------------
     |  Optional Helper
     |-------------------------------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
