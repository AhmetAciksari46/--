<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Subscription",
 *     type="object",
 *     title="Subscription",
 *     description="Abonelik modeli",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="subscribable_type", type="string", example="App\\Models\\User"),
 *     @OA\Property(property="subscribable_id", type="integer", example=1),
 *     @OA\Property(property="package_id", type="integer", example=2),
 *     @OA\Property(property="price", type="number", format="float", example=199.99),
 *     @OA\Property(property="currency", type="string", example="TRY"),
 *     @OA\Property(property="payment_method", type="string", example="credit_card", nullable=true),
 *     @OA\Property(property="payment_reference", type="string", example="TX123456", nullable=true),
 *     @OA\Property(property="payment_status", type="string", enum={"pending","paid","failed","refunded"}, example="paid"),
 *     @OA\Property(property="status", type="string", enum={"active","expired","cancelled"}, example="active"),
 *     @OA\Property(property="start_date", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="end_date", type="string", format="date-time", example="2025-11-21T12:00:00Z"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="auto_renew", type="boolean", example=false),
 *     @OA\Property(property="note", type="string", example="Özel not", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T12:00:00Z")
 * )
 */

class Subscription extends Model
{
    use HasFactory;

    protected $dates = ['start_date', 'end_date'];
    protected $fillable = [
        'subscribable_type',   // <-- buraya ekle
        'subscribable_id',
        'subscribable',
        'package_id',
        'price',
        'currency',
        'payment_method',
        'payment_reference',
        'payment_status',
        'status',
        'start_date',
        'end_date',
        'is_active',
        'auto_renew',
        'note'

    ];
    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function isActive()
    {
        return $this->is_active && now()->between($this->start_date, $this->end_date);
    }
    public function daysRemaining(): ?int
    {
        return $this->end_date ? now()->diffInDays($this->end_date, false) : null;
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }
    public static function activeForUser($user)
    {
        return self::where('subscribable_type', get_class($user))
            ->where('subscribable_id', $user->id)
            ->where('payment_status', 'paid')
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Kullanıcının mevcut paketi var mı ve yükseltme yapılabilir mi?
     */
    public static function upgradeOrCreate($user, $package)
    {
        $current = self::activeForUser($user);

        if ($current) {
            if ($current->package_id !== $package->id) {
                $current->update(['is_active' => false]);
            } else {
                return $current; // Aynı paket varsa yeniden oluşturma
            }
        }

        return self::create([
            'subscribable_type' => get_class($user),  // <-- burası zorunlu
            'subscribable_id' => $user->id,           // <-- burası zorunlu
            'package_id' => $package->id,
            'price' => $package->price,
            'currency' => 'TRY',
            'payment_status' => 'pending',
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($package->duration_days),
            'auto_renew' => false,
        ]);
    }
}
