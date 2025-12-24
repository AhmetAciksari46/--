<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @OA\Schema(
 *     schema="School",
 *     title="School Model",
 *     description="Okul bilgisi şeması",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Açıksarı Koleji"),
 *     @OA\Property(property="nickname", type="string", example="ackolej"),
 *     @OA\Property(property="address", type="string", example="Kahramanmaraş"),
 *     @OA\Property(property="manager_id", type="integer", example=5),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="img_path", type="string", example="uploads/schools/logo.png"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'manager_id',
        'is_active',
        'img_path',
        'nickname',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    /* ======================
     |   Relationships
     ====================== */

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscribable');
    }

    // Okul yöneticisi (manager rolündeki user)
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    public function teachers()
    {
        return $this->hasMany(TeacherProfile::class, 'school_id');
    }


    public function grades()
    {
        return $this->hasMany(SchoolHasGrade::class);
    }
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('is_active', true)
            ->where('payment_status', 'paid')
            ->where(function ($q) {
                // 1) Bitiş tarihi NULL → süresiz abonelik
                $q->whereNull('end_date')
                    // 2) Ya da end_date gelecekte
                    ->orWhere('end_date', '>=', now());
            })
            ->where('start_date', '<=', now())
            ->orderByRaw('end_date IS NULL DESC') // Süresiz abonelikler önce gelsin
            ->orderBy('end_date', 'desc')
            ->first();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function class_models()
    {
        return $this->hasMany(ClassModel::class, 'school_id');
    }

    public function weeks()
    {
        return $this->hasMany(SchoolWeek::class);
    }

    public function days()
    {
        return $this->hasMany(SchoolWeekDay::class);
    }
    // 3️⃣ Okulun aktif paketi
    public function activePackage()
    {
        $subscription = $this->activeSubscription();
        return $subscription ? $subscription->package : null;
    }

    // 4️⃣ Aktiflik kontrolü
    public function hasActiveSubscription(): bool
    {
        return (bool) $this->activeSubscription();
    }
    public function students()
    {
        return $this->hasMany(SchoolStudentProfile::class, 'school_id');
    }
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class, 'school_id');
    }
    public function chatGroups()
    {
        return $this->hasMany(Group::class, 'school_id');
    }
}
