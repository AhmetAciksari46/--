<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Grade",
 *     type="object",
 *     title="Grade Model",
 *     description="Sınıf seviyesini temsil eder",
 * )
 */
class Grade extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function schoolRelations()
    {
        return $this->hasMany(SchoolHasGrade::class);
    }

    public function classModels()
    {
        return $this->hasMany(ClassModel::class);
    }
    public function preRegistration()
    {
        return $this->hasMany(StudentPreRegistration::class);
    }
}
