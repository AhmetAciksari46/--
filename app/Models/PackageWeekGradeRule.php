<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageWeekGradeRule extends Model
{
    protected $fillable = ['package_id', 'grade', 'week_no', 'days_required'];
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
