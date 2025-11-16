<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="BranchStoreRequest",
 *     type="object",
 *     required={"name", "slug"},
 *     @OA\Property(property="name", type="string", example="Matematik"),
 *     @OA\Property(property="slug", type="string", example="matematik"),
 *     @OA\Property(property="code", type="string", example="MATH"),
 *     @OA\Property(property="description", type="string", example="Sayısal dersleri kapsar"),
 *     @OA\Property(property="color", type="string", example="#0088cc"),
 *     @OA\Property(property="icon", type="string", example="pi pi-calculator"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 * )
 */
class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:branches,slug',
            'code'        => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:50',
            'is_active'   => 'nullable|boolean',
        ];
    }

    public function prepareForValidation(): void
    {
        // slug boşsa otomatik üret
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->input('name'), '-', 'tr'),
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'name'        => 'branş adı',
            'slug'        => 'kısa ad (slug)',
            'code'        => 'kod',
            'description' => 'açıklama',
            'color'       => 'renk',
            'icon'        => 'ikon',
            'is_active'   => 'aktiflik durumu',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => ':attribute alanı zorunludur.',
            'name.string'        => ':attribute metin türünde olmalıdır.',
            'name.max'           => ':attribute en fazla :max karakter olabilir.',
            'slug.string'        => ':attribute metin türünde olmalıdır.',
            'slug.max'           => ':attribute en fazla :max karakter olabilir.',
            'slug.unique'        => 'Bu :attribute zaten kullanımda.',
            'code.string'        => ':attribute metin türünde olmalıdır.',
            'code.max'           => ':attribute en fazla :max karakter olabilir.',
            'description.string' => ':attribute metin türünde olmalıdır.',
            'color.string'       => ':attribute metin türünde olmalıdır.',
            'color.max'          => ':attribute en fazla :max karakter olabilir.',
            'icon.string'        => ':attribute metin türünde olmalıdır.',
            'icon.max'           => ':attribute en fazla :max karakter olabilir.',
            'is_active.boolean'  => ':attribute yalnızca true veya false olabilir.',
        ];
    }
}
