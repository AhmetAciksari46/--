<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\MediaPoolType;


class MediaPool extends Model
{
    protected $table = 'media_pools';

    protected $fillable = [
        'url',
        'type',
        'name',
    ];

    protected $casts = [
        'type' => MediaPoolType::class,
    ];
}
