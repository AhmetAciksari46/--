<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="PhysicalClassroomStoreRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="2. Kat A Sınıfı"),
 *     @OA\Property(property="location", type="string", example="2. Kat Koridor A"),
 *     @OA\Property(property="capacity", type="integer", example=30)
 * )
 */
class PhysicalClassroomStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'location'  => 'nullable|string|max:255',
            'capacity'  => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Sınıf adı gereklidir.',
            'name.string'       => 'Sınıf adı metin olmalıdır.',
            'name.max'          => 'Sınıf adı en fazla 255 karakter olabilir.',

            'location.string'   => 'Lokasyon metin olmalıdır.',
            'location.max'      => 'Lokasyon en fazla 255 karakter olabilir.',

            'capacity.integer'  => 'Kapasite sadece sayı olabilir.',
            'capacity.min'      => 'Kapasite en az 1 olmalıdır.',
        ];
    }
}
