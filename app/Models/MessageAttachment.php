<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="MessageAttachment",
 *     title="MessageAttachment Schema",
 *     description="Files attached to messages",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="message_id", type="integer", example=10),
 *     @OA\Property(property="file_path", type="string", example="/uploads/messages/abc.pdf"),
 *     @OA\Property(property="type", type="string", example="pdf")
 * )
 */
class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_path',
        'file_name',
        'mime_type',
        'size',
        'type'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
