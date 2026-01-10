<?php

namespace App\Http\Resources\Minis;

use Illuminate\Http\Resources\Json\JsonResource;

class SubjectMiniResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name'   => $this->name,
            'branch' => new BranchMiniResource($this->whenLoaded('branch')),
            'grade'  => new GradeMiniResource($this->whenLoaded('grade')),
        ];
    }
}
