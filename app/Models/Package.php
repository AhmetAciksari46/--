<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_students',
        'max_teachers',
        'duration_days',
        'price',
    ];

    /* ======================
     |   Relationships
     ====================== */

    // Bu pakete ait abonelikler
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
