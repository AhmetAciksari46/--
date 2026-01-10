<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Minis\UserMiniResource;
use App\Http\Resources\Minis\GradeMiniResource;
use App\Http\Resources\Minis\AcademicYearMiniResource;

class ClassModelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'is_active'     => $this->is_active,
            'academicYear' => new AcademicYearMiniResource($this->whenLoaded('academicYear')),

            'grade' => new GradeMiniResource($this->whenLoaded('grade')),
            'teacher' => new UserMiniResource($this->whenLoaded('teacher')),

            'grade_id'   => $this->grade_id,
            'teacher_id' => $this->teacher_id,
        ];
    }
}
