<?php

namespace App\Models;

use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Message",
 *     title="Message Schema",
 *     description="Chat Message model",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="group_id", type="integer", example=2),
 *     @OA\Property(property="user_id", type="integer", example=42),
 *     @OA\Property(property="content", type="string", example="Merhaba sınıf!"),
 *     @OA\Property(property="message_type", type="string", example="announcement"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="edited_at", type="string", format="date-time", nullable=true)
 * )
 */
class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id',
        'user_id',
        'content',
        'message_type',
        'status',
        'edited_at',
    ];

    protected $casts = [
        'message_type' => MessageType::class,
        'edited_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }
}
