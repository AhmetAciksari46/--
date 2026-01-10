<?php

namespace App\Http\Resources\Profiles;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Minis\ClassMiniResource;

class SchoolStudentProfileListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'student_number' => $this->student_number,
            'img_path'       => $this->img_path,
            'status'         => $this->status,
            'is_active'      => (bool) $this->is_active,
            'phone'      =>  $this->phone,

            'activeClass' => new ClassMiniResource($this->whenLoaded('activeClass')),

        ];
    }
}
