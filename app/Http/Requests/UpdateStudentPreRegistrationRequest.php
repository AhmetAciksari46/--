<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ParentsStatus;
use App\Enums\PreRegistrationStatus;

/**
 * @OA\Schema(
 *     schema="UpdateStudentPreRegistrationRequest",
 *     @OA\Property(property="first_name", type="string", example="Ahmet"),
 *     @OA\Property(property="last_name", type="string", example="Açıksarı"),
 *     @OA\Property(property="tc", type="string", example="12345678901", nullable=true),
 *     @OA\Property(property="grade_id", type="integer", example=9),
 *     @OA\Property(property="gender", type="string", example="male", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="2010-05-10", nullable=true),
 *     @OA\Property(property="phone", type="string", example="05551234567", nullable=true),
 *     @OA\Property(property="email", type="string", example="ogrenci@mail.com", nullable=true),
 *     @OA\Property(property="address", type="string", example="İstanbul / Kadıköy", nullable=true),
 *
 *     @OA\Property(property="mother_full_name", type="string", example="Ayşe Açıksarı", nullable=true),
 *     @OA\Property(property="mother_phone", type="string", example="05550001122", nullable=true),
 *     @OA\Property(property="mother_job", type="string", example="Öğretmen", nullable=true),
 *     @OA\Property(property="mother_birth_date", type="string", format="date", example="1985-03-22", nullable=true),
 *     @OA\Property(property="mother_email", type="string", example="anne@mail.com", nullable=true),
 *
 *     @OA\Property(property="father_full_name", type="string", example="Mehmet Açıksarı", nullable=true),
 *     @OA\Property(property="father_phone", type="string", example="05553334455", nullable=true),
 *     @OA\Property(property="father_job", type="string", example="Mühendis", nullable=true),
 *     @OA\Property(property="father_birth_date", type="string", format="date", example="1982-07-10", nullable=true),
 *     @OA\Property(property="father_email", type="string", example="baba@mail.com", nullable=true),
 * @OA\Property(property="school_id", type="integer", example=1, nullable=true, description="Okul ID (backend otomatik set edebilir)"),
 *     @OA\Property(property="parents_status", type="string", example="together_alive", nullable=true),
 *     @OA\Property(property="status", type="string", example="saved", nullable=true),
 *
 *     @OA\Property(property="description", type="string", example="Güncellendi", nullable=true),
 *     @OA\Property(property="note_1", type="string", example="Not 1", nullable=true),
 *     @OA\Property(property="note_2", type="string", example="Not 2", nullable=true),
 *     @OA\Property(property="note_3", type="string", example="Not 3", nullable=true)
 * )
 */
class UpdateStudentPreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('studentPreRegistration')?->id ?? $this->route('student_pre_registration');

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name'  => ['sometimes', 'required', 'string', 'max:255'],
            'tc'         => ['nullable', 'string', 'size:11', "unique:student_pre_registrations,tc,{$id}"],
            'grade_id'   => ['sometimes', 'required', 'exists:grades,id'],
            'school_id' => ['nullable', 'exists:schools,id'],

            'gender'     => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email'],
            'address'    => ['nullable', 'string', 'max:500'],

            'mother_full_name' => ['nullable', 'string', 'max:255'],
            'mother_phone'     => ['nullable', 'string', 'max:50'],
            'mother_job'       => ['nullable', 'string', 'max:255'],
            'mother_birth_date' => ['nullable', 'date'],
            'mother_email'     => ['nullable', 'email'],

            'father_full_name' => ['nullable', 'string', 'max:255'],
            'father_phone'     => ['nullable', 'string', 'max:50'],
            'father_job'       => ['nullable', 'string', 'max:255'],
            'father_birth_date' => ['nullable', 'date'],
            'father_email'     => ['nullable', 'email'],

            'parents_status' => ['nullable', new Enum(ParentsStatus::class)],
            'status'         => ['nullable', new Enum(PreRegistrationStatus::class)],

            'description' => ['nullable', 'string'],
            'note_1'      => ['nullable', 'string'],
            'note_2'      => ['nullable', 'string'],
            'note_3'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        // Store ile aynı mesajlar
        return (new StoreStudentPreRegistrationRequest())->messages();
    }
}
