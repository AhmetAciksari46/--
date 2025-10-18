<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    // Okulun sınıfları
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    public function teachers()
    {
        return $this->hasMany(TeacherProfile::class, 'schoolId');
    }

    public function additionalClassRooms()
    {
        return $this->hasMany(AdditionalClassRoom::class);
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->latest('end_date')
            ->first();
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
        return $this->hasMany(SchoolStudentProfile::class, 'schoolId', 'id');
    }
}
