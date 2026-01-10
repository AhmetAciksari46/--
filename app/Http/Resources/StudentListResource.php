<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Profiles\SchoolStudentProfileListResource;

class StudentListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'userName' => $this->userName,

            // email istemiyorsan kaldır:
            // 'email' => $this->email,

            'profile' => new SchoolStudentProfileListResource(
                $this->whenLoaded('schoolStudentProfile')
            ),
        ];
    }
}
