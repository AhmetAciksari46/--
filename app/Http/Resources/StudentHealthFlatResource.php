<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentHealthFlatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'student' => [
                'id'   => $this->profile?->user?->id,
                'name' => $this->profile?->user?->name,
            ],
            'health' => [
                'id' => $this->id,
                'school_student_profile_id' => $this->school_student_profile_id,
                'has_chronic_disease' => (bool) $this->has_chronic_disease,
                'chronic_disease_description' => $this->chronic_disease_description,
                'allergies' => $this->allergies,
                'medications' => $this->medications,
                'special_needs' => $this->special_needs,
                'blood_type' => $this->blood_type,
                'health_insurance' => $this->health_insurance,
            ],
        ];
    }
}
