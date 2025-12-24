<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateCommentRequest",
 *     title="Create Comment Request",
 *     description="Body required for creating a comment under a message",
 *
 *     @OA\Property(
 *          property="content",
 *          type="string",
 *          example="Teşekkür ederiz öğretmenim!"
 *     )
 * )
 */
class CreateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller içinde authorization yapılacak
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.string' => 'Content must be a valid string.',
        ];
    }
}
