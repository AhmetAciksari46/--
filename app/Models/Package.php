<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Package",
 *     type="object",
 *     title="Package",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Full Paket"),
 *     @OA\Property(property="price", type="number", format="float", example=199.99),
 * )
 */
class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_days',
        'price',
        'type',
        'is_active',
        'has_schedule_module',
        'week_count',
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
        'is_sequential_content_required',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'has_homework_module' => 'boolean',
        'has_schedule_module' => 'boolean',
        'has_exam_module' => 'boolean',
        'has_chat_module' => 'boolean',
        'has_analytics_module' => 'boolean',
        'has_certificate_module' => 'boolean',
        'is_trial' => 'boolean',
        'is_sequential_content_required' => 'boolean',
    ];
    /* ======================
     |   Relationships
     ====================== */

    // Bu pakete ait abonelikler
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function gradeRules()
    {
        return $this->hasMany(PackageWeekGradeRule::class);
    }

    public function subjectRules()
    {
        return $this->hasMany(PackageWeekSubjectRule::class);
    }
    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
