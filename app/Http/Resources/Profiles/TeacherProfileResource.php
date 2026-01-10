<?php

namespace App\Http\Resources\Profiles;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Minis\BranchMiniResource;

class TeacherProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'img_path'  => $this->img_path,
            'status'    => $this->status,
            'is_active' => $this->is_active,
            'phone'  => $this->phone,

            'branch' => new BranchMiniResource($this->whenLoaded('branch')),
        ];
    }
}
