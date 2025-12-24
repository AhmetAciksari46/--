<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="GroupMember",
 *     title="GroupMember Schema",
 *     description="Members of classroom groups",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="group_id", type="integer", example=22),
 *     @OA\Property(property="user_id", type="integer", example=150),
 *     @OA\Property(property="role_in_group", type="string", example="student")
 * )
 */
class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'role_in_group'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
