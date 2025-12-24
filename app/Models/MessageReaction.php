<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="MessageReaction",
 *     title="MessageReaction Schema",
 *     description="Reactions given by users to messages",
 *     
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="message_id", type="integer", example=10),
 *     @OA\Property(property="user_id", type="integer", example=45),
 *     @OA\Property(property="reaction", type="string", example="like"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class MessageReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'reaction',
    ];

    /** RELATIONSHIPS **/

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
