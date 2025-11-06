<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageWeekSubjectRule extends Model
{
    protected $fillable = ['package_id', 'grade', 'week_no', 'subject_id', 'hours'];
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
