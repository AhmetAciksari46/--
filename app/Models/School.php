<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'manager_id',
    ];

    /* ======================
     |   Relationships
     ====================== */

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

    // Okulun abonelikleri (paketler)
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
