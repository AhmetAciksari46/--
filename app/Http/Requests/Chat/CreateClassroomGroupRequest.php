<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateClassroomGroupRequest",
 *     title="Sınıf Sohbet Grubu Oluşturma İsteği",
 *     description="Sınıfa ait sohbet grubunun isteğe bağlı olarak özelleştirilebilmesi için kullanılan istek yapısı.",
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         nullable=true,
 *         example="5A Sınıfı Sohbet Grubu",
 *         description="Sohbet grubu için isteğe bağlı özel bir isim. Gönderilmezse sınıf adından otomatik üretilir."
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         example="5A sınıfı ders içi duyurular ve öğretmen paylaşımları için oluşturulan grup.",
 *         description="Grup hakkında isteğe bağlı açıklama alanı."
 *     )
 * )
 */
class CreateClassroomGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Not: İstersen bu izin kontrolünü "admin" veya "manager" ile sınırlayabilirsin.
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'         => 'Grup adı metin formatında olmalıdır.',
            'name.max'            => 'Grup adı en fazla 255 karakter olabilir.',
            'description.string'  => 'Grup açıklaması metin formatında olmalıdır.',
        ];
    }
}
