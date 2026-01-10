<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Minis\StudentMiniResource;
use App\Http\Resources\Minis\ParentMiniResource;

class StudentParentFlatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'student' => new StudentMiniResource($this->profile?->user),
            'parent'  => new ParentMiniResource($this),
        ];
    }
}
