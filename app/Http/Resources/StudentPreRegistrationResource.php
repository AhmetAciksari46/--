<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentPreRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'tc' => $this->tc,

            'grade_id' => $this->grade_id,
            'grade' => $this->whenLoaded('grade', fn() => [
                'id' => $this->grade->id,
                'name' => $this->grade->name,
            ]),

            'gender' => $this->gender,
            'birth_date' => optional($this->birth_date)->format('Y-m-d'),
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,

            'mother' => [
                'full_name' => $this->mother_full_name,
                'phone' => $this->mother_phone,
                'job' => $this->mother_job,
                'birth_date' => optional($this->mother_birth_date)->format('Y-m-d'),
                'email' => $this->mother_email,
            ],

            'father' => [
                'full_name' => $this->father_full_name,
                'phone' => $this->father_phone,
                'job' => $this->father_job,
                'birth_date' => optional($this->father_birth_date)->format('Y-m-d'),
                'email' => $this->father_email,
            ],

            'parents_status' => $this->parents_status?->value,
            'status' => $this->status?->value,

            'description' => $this->description,
            'note_1' => $this->note_1,
            'note_2' => $this->note_2,
            'note_3' => $this->note_3,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
