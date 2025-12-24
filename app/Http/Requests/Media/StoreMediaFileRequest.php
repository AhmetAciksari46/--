<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="StoreMediaFileRequest",
 *   type="object",
 *   required={"file","type"},
 *   @OA\Property(
 *     property="file",
 *     type="string",
 *     format="binary",
 *     description="Yüklenecek dosya"
 *   ),
 *   @OA\Property(
 *     property="type",
 *     type="string",
 *     example="avatar"
 *   ),
 *   @OA\Property(
 *     property="file_name",
 *     type="string",
 *     example="profil_resmi"
 *   )
 * )
 */
class StoreMediaFileRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'type' => ['required', 'in:image,video,audio,document'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'file' => [
                'required',
                'file',
                'max:51200', // 50MB
                'mimetypes:image/*,video/*,audio/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'type.required' => 'Dosya türü zorunludur.',
            'type.in' => 'Dosya türü geçersiz. (image, video, audio, document)',
            'file.required' => 'Yüklenecek dosya zorunludur.',
            'file.file' => 'Geçerli bir dosya yüklemelisiniz.',
            'file.max' => 'Dosya boyutu en fazla 50MB olabilir.',
            'file.mimetypes' => 'Bu dosya türü desteklenmiyor.',
            'file_name.max' => 'Dosya adı en fazla 255 karakter olabilir.',
        ];
    }
}
