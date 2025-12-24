<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateMessageRequest",
 *     title="Update Message Request",
 *     description="Body required to update a message",
 *
 *     @OA\Property(property="content", type="string", example="Güncellenmiş mesaj içeriği."),
 *     @OA\Property(property="message_type", type="string", example="announcement")
 * )
 */
class UpdateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
            'message_type' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.string' => 'Content must be a valid string.',
            'message_type.string' => 'Message type must be a valid string.',
        ];
    }
}
