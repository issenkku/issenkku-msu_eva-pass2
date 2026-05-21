<?php

// ไฟล์คลาสของระบบ: app/Http/Requests/Workload/StoreWorkloadFormFieldRequest.php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkloadFormFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
            'default_value' => ['nullable', 'string', 'max:255'],
            'variable_name' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'string', 'max:255'],
        ];
    }
}
