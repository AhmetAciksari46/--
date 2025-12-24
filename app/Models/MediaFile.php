<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="MediaFile",
 *   title="MediaFile",
 *   description="Genel dosya yükleme kaydı",
 *   @OA\Property(property="id", type="integer", example=12),
 *   @OA\Property(property="type", type="string", example="image"),
 *   @OA\Property(property="file_name", type="string", nullable=true),
 *   @OA\Property(property="file_path", type="string"),
 *   @OA\Property(property="mime_type", type="string"),
 *   @OA\Property(property="size", type="integer"),
 *   @OA\Property(property="url", type="string")
 * )
 */
class MediaFile extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'file_name',
        'file_path',
        'mime_type',
        'size',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
