<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="StoreMessageAttachmentRequest",
 *   required={"file"},
 *   @OA\Property(
 *     property="file",
 *     type="string",
 *     format="binary",
 *     description="Mesaja eklenecek dosya"
 *   )
 * )
 */
class StoreMessageAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Dosya seçilmelidir.',
            'file.file'     => 'Geçerli bir dosya yükleyin.',
            'file.max'      => 'Dosya boyutu en fazla 10MB olabilir.',
            'file.mimes'    => 'Bu dosya türü desteklenmiyor.',
        ];
    }
}
