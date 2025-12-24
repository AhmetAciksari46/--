<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'is_default',
        'is_assignable',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_assignable' => 'boolean',
    ];
}
