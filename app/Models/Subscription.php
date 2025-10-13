<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $dates = ['start_date', 'end_date'];
    protected $fillable = [
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
}
