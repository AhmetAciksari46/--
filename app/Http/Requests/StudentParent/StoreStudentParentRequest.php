<?php

namespace App\Http\Requests\StudentParent;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreStudentParentRequest",
 *     type="object",
 *     required={"type","name","status"},
 *
 *     @OA\Property(property="type", type="string", example="anne"),
 *     @OA\Property(property="relationship", type="string", example="öz anne"),
 *     @OA\Property(property="name", type="string", example="Fatma Yıldız"),
 *     @OA\Property(property="phone", type="string", example="+905551112233"),
 *     @OA\Property(property="tc_no", type="string", example="11122233344"),
 *     @OA\Property(property="job", type="string", example="Öğretmen"),
 *     @OA\Property(property="address", type="string", example="Bursa"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1980-02-10"),
 *     @OA\Property(property="status", type="string", example="hayatta"),
 *     @OA\Property(property="is_parent", type="boolean", example=true)
 * )
 */
class StoreStudentParentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => 'required|in:anne,baba,vasi',
            'relationship' => 'nullable|string',
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'tc_no' => 'nullable|string',
            'job' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'status' => 'required|in:hayatta,vefat',
            'is_parent' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Veli tipi zorunludur.',
            'name.required' => 'Veli adı zorunludur.',
            'status.required' => 'Veli durumu zorunludur.',
            'birth_date.date' => 'Doğum tarihi geçersiz format.',
        ];
    }
}
