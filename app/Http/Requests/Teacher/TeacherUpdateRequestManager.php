<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="TeacherUpdateRequestManager",
 *     type="object",
 *     title="Teacher Update Request (Manager)",
 *     description="Manager tarafından öğretmen güncelleme isteği",
 *
 *     @OA\Property(property="name", type="string", maxLength=255, example="Ahmet Can"),
 *     @OA\Property(property="email", type="string", example="ahmet@okul.com"),
 *
 *     @OA\Property(property="phone", type="string", maxLength=50, example="+905551112233"),
 *     @OA\Property(property="address", type="string", maxLength=255, example="İstanbul"),
 *     @OA\Property(property="img_path", type="string", example="uploads/teachers/profile.jpg"),
 *
 *     @OA\Property(property="status", type="string", enum={"active","passive"}, example="active"),
 *     @OA\Property(property="branch_id", type="integer", example=3),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="gender", type="string", enum={"male","female","other"}, example="male"),
 *
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-05-04"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-09-01"),
 *
 *     @OA\Property(property="description", type="string", example="Deneyimli matematik öğretmeni"),
 *     @OA\Property(property="color_code", type="string", maxLength=20, example="#FF5733"),
 *
 *     @OA\Property(property="emergency_contact_name", type="string", maxLength=255, example="Ali Can"),
 *     @OA\Property(property="emergency_contact_phone", type="string", maxLength=50, example="+905551234567"),
 *     @OA\Property(property="emergency_contact_relationship", type="string", maxLength=100, example="Kardeş"),
 *     @OA\Property(property="emergency_contact_description", type="string", example="Acil durumda arayın"),
 * )
 */
class TeacherUpdateRequestManager extends FormRequest
{


    public function authorize()
    {
        return true; // Controller + Policy içinde ayrıca kontrol edilecek
    }

    public function rules()
    {
        $teacherId = $this->route('teacher')?->id;

        return [
            // USER TABLOSU
            'name'       => 'nullable|string|max:255',
            'email'      => 'nullable|email|unique:users,email,' . $teacherId,

            // TEACHER PROFILE
            'phone'                           => 'nullable|string|max:50',
            'address'                         => 'nullable|string|max:255',
            'img_path'                        => 'nullable|string|max:255',
            'status'                          => 'nullable|string|in:active,passive',
            'branch_id'                       => 'nullable|exists:branches,id',
            'is_active'                       => 'nullable|boolean',
            'gender'                          => 'nullable|string|in:male,female,other',
            'birth_date'                      => 'nullable|date',
            'start_date'                      => 'nullable|date',
            'description'                     => 'nullable|string',
            'color_code'                      => 'nullable|string|max:20',
            'emergency_contact_name'          => 'nullable|string|max:255',
            'emergency_contact_phone'         => 'nullable|string|max:50',
            'emergency_contact_relationship'  => 'nullable|string|max:100',
            'emergency_contact_description'   => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta başka bir kullanıcı tarafından kullanılıyor.',
            'branch_id.exists' => 'Seçilen branş sistemde kayıtlı değil.',
            'birth_date.date' => 'Doğum tarihi geçerli bir tarih formatında olmalıdır.',
            'start_date.date' => 'Başlangıç tarihi geçerli bir tarih formatında olmalıdır.',
            'status.in' => 'Durum yalnızca active veya passive olabilir.',
            'gender.in' => 'Cinsiyet male, female veya other olabilir.',
        ];
    }
}
