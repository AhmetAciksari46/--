<?php

namespace App\Http\Requests\Permissions;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ManagerSelfPermissionRequest",
 *     type="object",
 *     required={"permissions"},
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         @OA\Items(type="string", example="teacher.view.detail"),
 *         example={"teacher.view.detail","studentpreregistration.approve"}
 *     )
 * )
 */
class ManagerSelfPermissionRequest extends FormRequest
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
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'En az 1 yetki göndermelisiniz.',
            'permissions.array' => 'Yetkiler dizi formatında olmalıdır.',
            'permissions.min' => 'En az 1 yetki seçmelisiniz.',
            'permissions.*.string' => 'Yetki adı metin olmalıdır.',
            'permissions.*.exists' => 'Gönderilen yetkilerden biri sistemde bulunamadı.',
        ];
    }
}
