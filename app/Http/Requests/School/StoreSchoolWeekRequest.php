<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 * schema="StoreSchoolWeekRequest",
 * title="Hafta Oluşturma İsteği",
 * required={"week_no", "start_date", "end_date"},
 * @OA\Property(property="week_no", type="integer", description="Müfredat haftası numarası", example=5),
 * @OA\Property(property="start_date", type="string", format="date", example="2025-11-24", description="Haftanın başlangıç tarihi"),
 * @OA\Property(property="end_date", type="string", format="date", example="2025-11-30", description="Haftanın bitiş tarihi"),
 * @OA\Property(property="is_holiday", type="boolean", example=false, description="Bu haftanın tatil olup olmadığı"),
 * )
 */
class StoreSchoolWeekRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Okul ID'sine ve week_no'ya göre benzersizlik kontrolü
        $schoolId = $this->route('school')->id;

        return [
            'week_no' => [
                'required',
                'integer',
                'min:1',
                'unique:school_weeks,week_no,NULL,id,school_id,' . $schoolId
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_holiday' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'week_no.unique' => 'Bu hafta numarası bu okul için zaten tanımlanmış.',
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
            'end_date.after_or_equal' => 'Bitiş tarihi başlangıç tarihine eşit veya daha sonra olmalıdır.',
        ];
    }
}
