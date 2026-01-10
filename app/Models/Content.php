<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ContentDetail;

class Content extends Model
{
    protected $fillable = ['type', 'status', 'cloned_from_id'];

    public function detail()
    {
        return $this->hasOne(ContentDetail::class);
    }

    public function details()
    {
        return $this->hasMany(ContentDetail::class);
    }

    public function clonedFrom()
    {
        return $this->belongsTo(Content::class, 'cloned_from_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_content');
    }
}
