<?php

namespace App\Http\Requests\Student\Health;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="StudentHealthRequest",
 *   required={"has_chronic_disease"},
 *   @OA\Property(property="has_chronic_disease", type="boolean", example=true),
 *   @OA\Property(property="chronic_disease_description", type="string", example="Astım, KOAH"),
 *   @OA\Property(property="allergies", type="string", example="Fıstık alerjisi"),
 *   @OA\Property(property="medications", type="string", example="Ventolin"),
 *   @OA\Property(property="special_needs", type="string", example="Tekerlekli sandalye kullanıyor"),
 *   @OA\Property(property="doctor_name", type="string", example="Dr. Mehmet Yalçın"),
 *   @OA\Property(property="doctor_phone", type="string", example="0555 444 3322"),
 *   @OA\Property(property="blood_type", type="string", example="A+"),
 *   @OA\Property(property="health_insurance", type="string", example="SGK")
 * )
 */
class StudentHealthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'has_chronic_disease' => 'required|boolean',
            'chronic_disease_description' => 'nullable|string|max:2000',
            'allergies' => 'nullable|string|max:2000',
            'medications' => 'nullable|string|max:2000',
            'special_needs' => 'nullable|string|max:2000',
            'doctor_name' => 'nullable|string|max:255',
            'doctor_phone' => 'nullable|string|max:255',
            'blood_type' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-,bilinmiyor',
            'health_insurance' => 'required|string|in:SGK,özel sağlık sigortası,yeşil kart,sigortasız,diğer',
        ];
    }

    public function messages()
    {
        return [
            'has_chronic_disease.required' => 'Kronik hastalık bilgisi zorunludur.',
            'blood_type.in' => 'Geçerli bir kan grubu seçiniz.',
            'health_insurance.in' => 'Geçerli bir sağlık sigortası tipi seçiniz.',
        ];
    }
}
