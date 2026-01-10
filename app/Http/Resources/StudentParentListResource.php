<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentParentListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'is_parent'    => (bool) $this->is_parent,
            'type'         => $this->type,
            'relationship' => $this->relationship,
            'name'         => $this->name,
            'phone'        => $this->phone,
        ];
    }
}
