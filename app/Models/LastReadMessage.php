<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="LastReadMessage",
 *     title="LastReadMessage Schema",
 *     description="Tracks last message a user read in each group",
 *     @OA\Property(property="user_id", type="integer", example=10),
 *     @OA\Property(property="group_id", type="integer", example=4),
 *     @OA\Property(property="last_read_message_id", type="integer", example=100),
 *     @OA\Property(property="last_read_at", type="string", format="date-time")
 * )
 */
class LastReadMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'last_read_message_id',
        'last_read_at'
    ];

    protected $casts = [
        'last_read_at' => 'datetime'
    ];
}
