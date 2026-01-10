<?php

namespace App\Http\Resources\Minis;

use Illuminate\Http\Resources\Json\JsonResource;

class ParentMiniResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'is_parent'    => (bool) $this->is_parent,
            'type'         => $this->type,
            'relationship' => $this->relationship,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'profile_id' => $this->school_student_profile_id,

        ];
    }
}
