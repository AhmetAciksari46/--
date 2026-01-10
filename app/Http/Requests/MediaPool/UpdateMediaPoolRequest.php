<?php

namespace App\Http\Requests\MediaPool;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="MediaPoolUpdateRequest",
 *     type="object",
 *     @OA\Property(property="url", type="string", example="https://cdn.example.com/uploads/new.jpg"),
 *     @OA\Property(property="type", type="string", enum={"image","video","audio","link","sound"}, example="image"),
 *     @OA\Property(property="name", type="string", nullable=true, example="Yeni isim")
 * )
 */
class UpdateMediaPoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url'  => ['sometimes', 'string'],
            'type' => ['sometimes', Rule::in(['image', 'video', 'audio', 'link', 'sound'])],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.string'    => 'Medya URL alanı metin olmalıdır.',
            'type.in'       => 'Medya tipi geçersiz. Sadece: image, video, audio, link, sound değerleri kabul edilir.',
            'name.string'   => 'Medya adı metin olmalıdır.',
            'name.max'      => 'Medya adı en fazla 255 karakter olabilir.',
        ];
    }

    public function attributes(): array
    {
        return [
            'url'  => 'Medya URL',
            'type' => 'Medya tipi',
            'name' => 'Medya adı',
        ];
    }
}
