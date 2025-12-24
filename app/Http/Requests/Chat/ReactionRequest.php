<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ReactionRequest",
 *     title="Reaction Request",
 *     description="Schema for adding a reaction to a message or comment",
 *
 *     @OA\Property(
 *         property="reaction",
 *         type="string",
 *         example="like",
 *         description="Reaction type such as like, heart, wow, smile, etc."
 *     )
 * )
 */
class ReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller içinde ayrıca yetki kontrolü yapılacak
    }

    public function rules(): array
    {
        return [
            'reaction' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'reaction.required' => 'Reaction field is required.',
            'reaction.string' => 'Reaction must be a valid string.',
            'reaction.max' => 'Reaction cannot exceed 50 characters.',
        ];
    }
}
