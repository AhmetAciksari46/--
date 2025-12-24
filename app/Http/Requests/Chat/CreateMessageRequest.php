<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateMessageRequest",
 *     title="Create Message Request",
 *     description="Body required to send a message inside a group",
 *
 *     @OA\Property(property="content", type="string", nullable=true, example="Bugünkü ödeviniz sayfa 23."),
 *     @OA\Property(property="message_type", type="string", example="normal"),
 * )
 */
class CreateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller içinde ayrıca yetki kontrolü yapılacak
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
            'message_type' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'message_type.required' => 'Message type is required.',
        ];
    }
}
