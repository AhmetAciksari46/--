<?php

namespace App\Http\Resources\Minis;

use Illuminate\Http\Resources\Json\JsonResource;

class UserNameOnlyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
