<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CommentReaction",
 *     title="CommentReaction Schema",
 *     description="Reactions given by users to comment messages",
 *     
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="comment_id", type="integer", example=33),
 *     @OA\Property(property="user_id", type="integer", example=45),
 *     @OA\Property(property="reaction", type="string", example="heart"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CommentReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'user_id',
        'reaction',
    ];

    /** RELATIONSHIPS **/

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
