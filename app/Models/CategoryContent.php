<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryContent extends Pivot
{
    protected $table = 'category_content';

    protected $fillable = [
        'category_id',
        'content_id',
        'is_primary',
        'sort_order',
    ];
}
