<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateCommentRequest",
 *     title="Update Comment Request",
 *     description="Body required for updating a comment",
 *
 *     @OA\Property(
 *          property="content",
 *          type="string",
 *          example="Hocam içerik güncellendi."
 *     )
 * )
 */
class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Comment content is required.',
            'content.string'   => 'Content must be a valid string.',
        ];
    }
}
