<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkloadFormFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'note' => ['sometimes', 'nullable', 'string', 'max:255'],
            'default_value' => ['sometimes', 'nullable', 'string', 'max:255'],
            'variable_name' => ['sometimes', 'required', 'string', 'max:255'],
            'field_type' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
