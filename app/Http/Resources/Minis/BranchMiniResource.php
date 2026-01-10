<?php

namespace App\Http\Resources\Minis;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchMiniResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
