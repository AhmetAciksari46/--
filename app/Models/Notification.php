<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Notification",
 *     title="Notification Schema",
 *     description="User notifications",
 *     @OA\Property(property="id", type="integer", example=33),
 *     @OA\Property(property="user_id", type="integer", example=150),
 *     @OA\Property(property="type", type="string", example="new_message"),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="read_at", type="string", nullable=true)
 * )
 */
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];
}
