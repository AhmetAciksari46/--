<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentDetail extends Model
{
    protected $fillable = ['content_id', 'payload'];

    protected $casts = [
        'payload' => 'array',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
