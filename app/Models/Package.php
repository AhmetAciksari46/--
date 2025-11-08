<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="Package",
 * type="object",
 * title="Package",
 * description="Admin tarafından tanımlanan abonelik paketi şablonu.",
 * required={"id", "name", "price", "duration_days", "week_count", "type"},
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="Komple Eğitim Paketi"),
 * @OA\Property(property="description", type="string", example="Tüm modülleri içeren yıllık paket", nullable=true),
 * @OA\Property(property="duration_days", type="integer", example=365, description="Paketin geçerlilik süresi (gün)"),
 * @OA\Property(property="price", type="number", format="float", example=2499.99),
 * @OA\Property(property="type", type="string", enum={"school", "student", "other"}, example="school"),
 * @OA\Property(property="is_active", type="boolean", example=true),
 * @OA\Property(property="has_schedule_module", type="boolean", example=true),
 * @OA\Property(property="week_count", type="integer", example=40, description="Müfredat hafta sayısı"),
 * @OA\Property(property="has_homework_module", type="boolean", example=true),
 * @OA\Property(property="has_exam_module", type="boolean", example=true),
 * @OA\Property(property="has_chat_module", type="boolean", example=false),
 * @OA\Property(property="has_analytics_module", type="boolean", example=true),
 * @OA\Property(property="has_certificate_module", type="boolean", example=false),
 * @OA\Property(property="is_visible", type="boolean", example=true),
 * @OA\Property(property="is_trial", type="boolean", example=false),
 * @OA\Property(property="trial_days", type="integer", example=0, description="Deneme süresi (gün)"),
 * @OA\Property(property="is_sequential_content_required", type="boolean", example=false, description="Sıralı içerik zorunlu mu"),
 * @OA\Property(property="sort_order", type="integer", example=1, nullable=true),
 * @OA\Property(property="img_path", type="string", example="/img/package-basic.png", nullable=true),
 * @OA\Property(property="subscriptions_count", type="integer", example=3, nullable=true, description="Pakete bağlı abonelik sayısı"),
 * @OA\Property(
 *     property="grade_rules",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/PackageWeekGradeRule"),
 *     description="Paketin haftalık sınıf kuralları"
 * ),
 * @OA\Property(
 *     property="subject_rules",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/PackageWeekSubjectRule"),
 *     description="Paketin haftalık ders kuralları"
 * ),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
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
