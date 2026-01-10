<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Profiles\TeacherProfileResource;

class TeacherListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,

            'teacherProfile' => new TeacherProfileResource(
                $this->whenLoaded('teacherProfile')
            ),
        ];
    }
}
