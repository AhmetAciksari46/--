<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="Attendance",
 * title="Attendance Model",
 * description="Ders Oturumu (SchoolSession) İçin Öğrenci Yoklama Kaydı.",
 * @OA\Property(property="id", type="integer", example=50),
 * @OA\Property(property="session_id", type="integer", example=15, description="Bağlı olduğu ders oturumu ID'si"),
 * @OA\Property(property="school_student_profile_id", type="integer", example=45, description="Yoklaması alınan öğrencinin profil ID'si"),
 * @OA\Property(property="status", type="string", enum={"present", "absent", "late", "excused"}, example="present", description="Yoklama Durumu"),
 * @OA\Property(property="note", type="string", example="Öğrenci 15 dakika geç geldi.", nullable=true),
 * @OA\Property(property="taken_by_user_id", type="integer", example=5, description="Yoklamayı alan kullanıcının (Öğretmen/Manager) ID'si"),
 * @OA\Property(property="taken_at", type="string", format="date-time", description="Yoklamanın alındığı zaman"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Attendance extends Model
{
    protected $fillable = ['session_id', 'school_student_profile_id', 'status', 'taken_at', 'note'];

    public function session()
    {
        return $this->belongsTo(SchoolSession::class);
    }
    public function studentProfile()
    {
        return $this->belongsTo(SchoolStudentProfile::class, 'school_student_profile_id');
    }
}
