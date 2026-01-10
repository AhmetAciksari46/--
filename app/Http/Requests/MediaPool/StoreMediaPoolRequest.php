<?php

namespace App\Http\Requests\MediaPool;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="MediaPoolStoreRequest",
 *     type="object",
 *     required={"url","type"},
 *     @OA\Property(property="url", type="string", example="https://cdn.example.com/uploads/abc.jpg"),
 *     @OA\Property(property="type", type="string", enum={"image","video","audio","link","sound"}, example="image"),
 *     @OA\Property(property="name", type="string", nullable=true, example="Elma görseli")
 * )
 */
class StoreMediaPoolRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string'],
            'type' => ['required', Rule::in(['image', 'video', 'audio', 'link', 'sound'])],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
    public function messages(): array
    {
        return [
            'url.required'  => 'Medya URL alanı zorunludur.',
            'url.string'    => 'Medya URL alanı metin olmalıdır.',

            'type.required' => 'Medya tipi zorunludur.',
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
