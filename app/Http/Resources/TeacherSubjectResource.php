<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Minis\UserNameOnlyResource;
use App\Http\Resources\Minis\SubjectMiniResource;

class TeacherSubjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'teacher' => new UserNameOnlyResource($this->whenLoaded('teacher')),
            'subject' => new SubjectMiniResource($this->whenLoaded('subject')),
        ];
    }
}
