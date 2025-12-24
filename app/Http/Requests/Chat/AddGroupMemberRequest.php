<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AddGroupMemberRequest",
 *     title="Add Group Member Request",
 *     description="Add a new member to a classroom chat group",
 * 
 *     @OA\Property(property="user_id", type="integer", example=150),
 *     @OA\Property(property="role_in_group", type="string", example="student")
 * )
 */
class AddGroupMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller içinde yetki kontrolü yapılacak
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'role_in_group' => ['required', 'string', 'in:student,teacher,manager'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required.',
            'role_in_group.required' => 'Role is required.',
            'role_in_group.in' => 'Role must be either student or teacher or manager.',
        ];
    }
}
