<?php

namespace App\Models;

use App\Enums\GroupType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Group",
 *     title="Group Schema",
 *     description="Chat Group structure",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="classroom"),
 *     @OA\Property(property="school_id", type="integer", nullable=true),
 *     @OA\Property(property="class_model_id", type="integer", nullable=true),
 *     @OA\Property(property="name", type="string", example="5A Sınıfı"),
 *     @OA\Property(property="description", type="string", example="5A sınıf duyuruları"),
 *     @OA\Property(property="pinned_message_id", type="integer", nullable=true),
 *     @OA\Property(property="visibility", type="string", example="restricted"),
 *     @OA\Property(property="created_by", type="integer", nullable=true)
 * )
 */
class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'school_id',
        'class_model_id',
        'name',
        'description',
        'pinned_message_id',
        'visibility',
        'created_by',
    ];

    protected $casts = [
        'type' => GroupType::class,
    ];

    /** RELATIONSHIPS **/

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function pinnedMessage()
    {
        return $this->belongsTo(Message::class, 'pinned_message_id');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_model_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
