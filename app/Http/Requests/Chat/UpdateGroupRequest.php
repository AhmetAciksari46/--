<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UpdateGroupRequest",
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="Yeni Grup Adı"
 *   ),
 *   @OA\Property(
 *     property="description",
 *     type="string",
 *     nullable=true,
 *     example="Grup açıklaması"
 *   )
 * )
 */
class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Grup adı metin olmalıdır.',
            'name.max' => 'Grup adı en fazla 255 karakter olabilir.',
            'description.string' => 'Grup açıklaması metin olmalıdır.',
        ];
    }
}
