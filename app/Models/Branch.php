<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function teachers()
    {
        return $this->hasMany(TeacherProfile::class);
    }
}
