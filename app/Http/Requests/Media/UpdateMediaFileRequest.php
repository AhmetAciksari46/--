<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UpdateMediaFileRequest",
 *   type="object",
 *   @OA\Property(
 *     property="file_name",
 *     type="string",
 *     example="yeni_dosya_adi"
 *   )
 * )
 */
class UpdateMediaFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_name' => ['required', 'string', 'max:255'],
        ];
    }
}
