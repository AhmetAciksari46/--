<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'parent_id'
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }


    public function contents()
    {
        return $this->belongsToMany(Content::class, 'category_content')
            ->using(\App\Models\CategoryContent::class)
            ->withPivot(['is_primary', 'sort_order'])
            ->withTimestamps();
    }
}
