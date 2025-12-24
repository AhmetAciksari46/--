<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Comment",
 *     title="Comment Schema",
 *     description="Comments under classroom posts",
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="message_id", type="integer", example=33),
 *     @OA\Property(property="user_id", type="integer", example=120),
 *     @OA\Property(property="content", type="string", example="Teşekkür ederiz öğretmenim!"),
 *     @OA\Property(property="edited_at", type="string", format="date-time", nullable=true)
 * )
 */
class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'message_id',
        'user_id',
        'content',
        'status',
        'edited_at'
    ];

    protected $casts = [
        'edited_at' => 'datetime'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(CommentAttachment::class);
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class);
    }
}
