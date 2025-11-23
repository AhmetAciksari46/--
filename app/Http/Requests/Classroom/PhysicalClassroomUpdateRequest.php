<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="PhysicalClassroomUpdateRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="A Blok 101"),
 *     @OA\Property(property="location", type="string", example="1. Kat B Koridoru"),
 *     @OA\Property(property="capacity", type="integer", example=25)
 * )
 */
class PhysicalClassroomUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'sometimes|string|max:255',
            'location'  => 'sometimes|string|max:255',
            'capacity'  => 'sometimes|integer|min:1',
        ];
    }
    public function messages(): array
    {
        return [
            'name.string'       => 'Sınıf adı metin olmalıdır.',
            'name.max'          => 'Sınıf adı en fazla 255 karakter olabilir.',

            'location.string'   => 'Lokasyon metin olmalıdır.',
            'location.max'      => 'Lokasyon en fazla 255 karakter olabilir.',

            'capacity.integer'  => 'Kapasite sadece sayı olabilir.',
            'capacity.min'      => 'Kapasite en az 1 olmalıdır.',
        ];
    }
}
