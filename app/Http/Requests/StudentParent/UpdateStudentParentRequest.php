<?php

namespace App\Http\Requests\StudentParent;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateStudentParentRequest",
 *     type="object",
 *
 *     @OA\Property(property="type", type="string", example="baba"),
 *     @OA\Property(property="relationship", type="string", example="üvey baba"),
 *     @OA\Property(property="name", type="string", example="Ahmet Yıldız"),
 *     @OA\Property(property="phone", type="string", example="+905551234567"),
 *     @OA\Property(property="tc_no", type="string", example="23456789012"),
 *     @OA\Property(property="job", type="string", example="Mühendis"),
 *     @OA\Property(property="address", type="string", example="İzmir"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1979-08-12"),
 *     @OA\Property(property="status", type="string", example="hayatta"),
 *     @OA\Property(property="is_parent", type="boolean", example=true)
 * )
 */
class UpdateStudentParentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'sometimes|in:anne,baba,vasi',
            'relationship' => 'nullable|string',
            'name' => 'sometimes|string',
            'phone' => 'nullable|string',
            'tc_no' => 'nullable|string',
            'job' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'status' => 'sometimes|in:hayatta,vefat',
            'is_parent' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'birth_date.date' => 'Geçerli bir doğum tarihi giriniz.',
            'type.in' => 'Veli tipi sadece anne, baba veya vasi olabilir.',
        ];
    }
}
