<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateLastReadMessageRequest",
 *     title="Update Last Read Message Request",
 *     description="Request body for updating last read message in a group",
 *
 *     @OA\Property(
 *         property="last_read_message_id",
 *         type="integer",
 *         nullable=true,
 *         example=120,
 *     description="Okundu olarak işaretlenecek son mesaj ID (opsiyonel)"
 *     )
 * )
 */
class UpdateLastReadMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_read_message_id' => ['nullable', 'integer', 'exists:messages,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'last_read_message_id.integer' => 'Last read message ID must be a valid integer.',
        ];
    }
}
