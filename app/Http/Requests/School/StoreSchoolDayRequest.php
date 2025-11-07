<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="StoreSchoolDayRequest",
 * title="Okul Günü Oluşturma İsteği",
 * required={"day_of_week_no", "name"},
 * @OA\Property(property="day_of_week_no", type="integer", description="Haftanın sayısal günü (1=Pazartesi, 7=Pazar)", example=6),
 * @OA\Property(property="name", type="string", example="Cumartesi", description="Günün adı"),
 * @OA\Property(property="is_open", type="boolean", example=true, description="Bu günün açık olup olmadığı"),
 * )
 */
class StoreSchoolDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->route('school')->id;

        return [
            'day_of_week_no' => [
                'required',
                'integer',
                'min:1',
                'max:7',
                'unique:school_days,day_of_week_no,NULL,id,school_id,' . $schoolId
            ],
            'name' => ['required', 'string', 'max:50'],
            'is_open' => ['nullable', 'boolean'],
            'start_time' => ['nullable', 'date_format:H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i:s', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'day_of_week_no.unique' => 'Bu gün numarası (day_of_week_no) bu okul için zaten tanımlanmış.',
            'name.required' => 'Gün adı zorunludur.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
        ];
    }
}
