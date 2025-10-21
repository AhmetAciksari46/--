<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePackageRequest extends FormRequest
{
    public function authorize()
    {
        // Yalnızca manager veya individualstudent rolü izinli
        return in_array($this->user()->role, ['manager', 'individualstudent']);
    }
    public function rules()
    {
        // Route parametresi zaten Package modeli, id check'e gerek yok
        return [];
    }

    public function messages()
    {
        return [];
    }
}
