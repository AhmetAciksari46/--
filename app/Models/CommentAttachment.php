<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CommentAttachment",
 *     title="CommentAttachment Schema",
 *     description="Files attached to comments",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="comment_id", type="integer", example=5),
 *     @OA\Property(property="file_path", type="string", example="/uploads/comments/a.png")
 * )
 */
class CommentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'file_path',
        'file_name',
        'mime_type',
        'size',
        'type'
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
