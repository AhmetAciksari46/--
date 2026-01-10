<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ParentsStatus;
use App\Enums\PreRegistrationStatus;

/**
 * @OA\Schema(
 *     schema="StoreStudentPreRegistrationRequest",
 *     required={"first_name","last_name","grade_id"},
 *
 *     @OA\Property(property="first_name", type="string", example="Ahmet"),
 *     @OA\Property(property="last_name", type="string", example="Açıksarı"),
 *     @OA\Property(property="tc", type="string", example="12345678901", nullable=true),
 *     @OA\Property(property="grade_id", type="integer", example=9),
 *     @OA\Property(property="gender", type="string", example="male", nullable=true, description="male|female"),
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
 *
 *     @OA\Property(
 *        property="parents_status",
 *        type="string",
 *        example="together_alive",
 *        nullable=true,
 *        description="together_alive|separate_alive|mother_deceased|father_deceased|both_deceased"
 *     ),
 *
 *     @OA\Property(
 *        property="status",
 *        type="string",
 *        example="in_progress",
 *        nullable=true,
 *        description="in_progress|form_request|saved|cancelled"
 *     ),
 *
 * @OA\Property(property="school_id", type="integer", example=1, nullable=true, description="Okul ID (backend otomatik set edebilir)"),
 *     @OA\Property(property="description", type="string", example="Öğrenci ile ilk görüşme yapıldı.", nullable=true),
 *     @OA\Property(property="note_1", type="string", example="Not 1 örnek", nullable=true),
 *     @OA\Property(property="note_2", type="string", example="Not 2 örnek", nullable=true),
 *     @OA\Property(property="note_3", type="string", example="Not 3 örnek", nullable=true)
 * )
 */
class StoreStudentPreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Student
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'tc'         => ['nullable', 'string', 'size:11', 'unique:student_pre_registrations,tc'],
            'grade_id'   => ['required', 'exists:grades,id'],

            'gender'     => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email'],
            'address'    => ['nullable', 'string', 'max:500'],
            'school_id' => ['nullable', 'exists:schools,id'],

            // Mother
            'mother_full_name' => ['nullable', 'string', 'max:255'],
            'mother_phone'     => ['nullable', 'string', 'max:50'],
            'mother_job'       => ['nullable', 'string', 'max:255'],
            'mother_birth_date' => ['nullable', 'date'],
            'mother_email'     => ['nullable', 'email'],

            // Father
            'father_full_name' => ['nullable', 'string', 'max:255'],
            'father_phone'     => ['nullable', 'string', 'max:50'],
            'father_job'       => ['nullable', 'string', 'max:255'],
            'father_birth_date' => ['nullable', 'date'],
            'father_email'     => ['nullable', 'email'],

            // Enums
            'parents_status' => ['nullable', new Enum(ParentsStatus::class)],
            'status'         => ['nullable', new Enum(PreRegistrationStatus::class)],

            // Notes
            'description' => ['nullable', 'string'],
            'note_1'      => ['nullable', 'string'],
            'note_2'      => ['nullable', 'string'],
            'note_3'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // Student
            'first_name.required' => 'Öğrenci adı zorunludur.',
            'first_name.string'   => 'Öğrenci adı metin olmalıdır.',
            'first_name.max'      => 'Öğrenci adı en fazla 255 karakter olabilir.',

            'last_name.required' => 'Öğrenci soyadı zorunludur.',
            'last_name.string'   => 'Öğrenci soyadı metin olmalıdır.',
            'last_name.max'      => 'Öğrenci soyadı en fazla 255 karakter olabilir.',
            'school_id.exists' => 'Seçilen okul geçerli değil.',

            'tc.size'   => 'TC Kimlik No 11 haneli olmalıdır.',
            'tc.unique' => 'Bu TC Kimlik No ile kayıt zaten mevcut.',
            'tc.string' => 'TC Kimlik No metin olmalıdır.',

            'grade_id.required' => 'Sınıf seviyesi (grade) zorunludur.',
            'grade_id.exists'   => 'Seçilen sınıf seviyesi (grade) geçerli değildir.',

            'gender.in'    => 'Cinsiyet sadece "male" veya "female" olabilir.',
            'birth_date.date' => 'Doğum tarihi geçerli bir tarih olmalıdır.',

            'phone.max'  => 'Telefon numarası en fazla 50 karakter olabilir.',
            'phone.string' => 'Telefon numarası metin olmalıdır.',

            'email.email' => 'E-posta adresi geçerli değil.',
            'address.max' => 'Adres en fazla 500 karakter olabilir.',

            // Mother
            'mother_full_name.max' => 'Anne adı soyadı en fazla 255 karakter olabilir.',
            'mother_phone.max'     => 'Anne telefonu en fazla 50 karakter olabilir.',
            'mother_job.max'       => 'Anne mesleği en fazla 255 karakter olabilir.',
            'mother_birth_date.date' => 'Anne doğum tarihi geçerli bir tarih olmalıdır.',
            'mother_email.email'   => 'Anne e-posta adresi geçerli değil.',

            // Father
            'father_full_name.max' => 'Baba adı soyadı en fazla 255 karakter olabilir.',
            'father_phone.max'     => 'Baba telefonu en fazla 50 karakter olabilir.',
            'father_job.max'       => 'Baba mesleği en fazla 255 karakter olabilir.',
            'father_birth_date.date' => 'Baba doğum tarihi geçerli bir tarih olmalıdır.',
            'father_email.email'   => 'Baba e-posta adresi geçerli değil.',

            // Enums
            'parents_status.enum' => 'Anne baba durumu geçerli değil.',
            'status.enum'         => 'Statü geçerli değil.',

            // Notes
            'description.string' => 'Açıklama metin olmalıdır.',
            'note_1.string'      => 'Not 1 metin olmalıdır.',
            'note_2.string'      => 'Not 2 metin olmalıdır.',
            'note_3.string'      => 'Not 3 metin olmalıdır.',
        ];
    }
}
