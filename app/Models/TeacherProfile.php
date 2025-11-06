<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="TeacherProfile",
 *     type="object",
 *     title="TeacherProfile",
 *     description="Öğretmen profil modeli",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=5),
 *     @OA\Property(property="phone", type="string", example="+905551234567", nullable=true),
 *     @OA\Property(property="address", type="string", example="Ankara, Türkiye", nullable=true),
 *     @OA\Property(property="schoolId", type="integer", example=3, nullable=true),
 *     @OA\Property(property="branch_id", type="integer", example=2, nullable=true),
 *     @OA\Property(property="img_path", type="string", example="/storage/teachers/1.png", nullable=true),
 *     @OA\Property(property="status", type="string", example="active", nullable=true),
 *     @OA\Property(property="gender", type="string", example="female", nullable=true),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1992-03-10", nullable=true),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-09-01", nullable=true),
 *     @OA\Property(property="description", type="string", example="Matematik öğretmeni", nullable=true),
 *     @OA\Property(property="color_code", type="string", example="#3490dc", nullable=true),
 *     @OA\Property(property="emergency_contact_name", type="string", example="Veli Öğretmen", nullable=true),
 *     @OA\Property(property="emergency_contact_phone", type="string", example="+905559998877", nullable=true),
 *     @OA\Property(property="emergency_contact_relationship", type="string", example="Eşi", nullable=true),
 *     @OA\Property(property="emergency_contact_description", type="string", example="Hafta içi ulaşılabilir.", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="user", ref="#/components/schemas/User", nullable=true)
 * )
 */
class TeacherProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'school_id',
        'branch_id',
        'img_path',
        'status',
        'gender',
        'is_active',
        'birth_date',
        'start_date',
        'description',
        'color_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
