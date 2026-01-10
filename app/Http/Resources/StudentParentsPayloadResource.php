<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentParentsPayloadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'student' => [
                'id'   => $this->resource['student']->id,
                'name' => $this->resource['student']->name,
            ],

            'parents' => StudentParentListResource::collection(
                $this->resource['parents']
            ),
        ];
    }
}
