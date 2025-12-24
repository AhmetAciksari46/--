<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateGroupMemberRoleRequest",
 *     title="Update Group Member Role Request",
 *     description="Update role of an existing group member",
 * 
 *     @OA\Property(property="role_in_group", type="string", example="student")
 * )
 */
class UpdateGroupMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_in_group' => ['required', 'string', 'in:student,teacher'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_in_group.required' => 'Role field is required.',
            'role_in_group.in' => 'Role must be teacher or student.',
        ];
    }
}
