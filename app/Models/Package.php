<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_students',
        'max_teachers',
        'duration_days',
        'price',
        'type',
        'is_active',
        'has_homework_module',
        'has_exam_module',
        'has_chat_module',
        'has_analytics_module',
        'has_certificate_module',
        'is_visible',
        'is_trial',
        'trial_days',
        'sort_order',
        'img_path',
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
