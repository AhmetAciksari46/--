<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="BranchUpdateRequest",
 *     title="Branş Güncelleme İsteği",
 *     description="Bir branşın bilgilerini güncellemek için kullanılan istek şeması.",
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Matematik",
 *         description="Branşın adı. (örnek: Matematik)"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         example="matematik",
 *         description="Branşın kısa adı (slug). Eğer gönderilmezse otomatik oluşturulur."
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         example="MATH",
 *         description="Branş kodu. Genellikle kısa bir kısaltma."
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Sayısal alan dersleri için branş.",
 *         description="Branşın açıklaması (isteğe bağlı)."
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         example="#1E90FF",
 *         description="Arayüzde kullanılacak renk kodu (isteğe bağlı)."
 *     ),
 *     @OA\Property(
 *         property="icon",
 *         type="string",
 *         example="calculator",
 *         description="Branşı temsil eden ikon adı (isteğe bağlı)."
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         example=true,
 *         description="Branş aktif mi değil mi. true = aktif, false = pasif."
 *     )
 * )
 */
class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch');
        $branchId = is_object($branchId) ? $branchId->id : $branchId;

        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'slug'        => ['sometimes', 'string', 'max:255', Rule::unique('branches', 'slug')->ignore($branchId)],
            'code'        => ['sometimes', 'nullable', 'string', 'max:10'],
            'description' => ['sometimes', 'nullable', 'string'],
            'color'       => ['sometimes', 'nullable', 'string', 'max:20'],
            'icon'        => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
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
