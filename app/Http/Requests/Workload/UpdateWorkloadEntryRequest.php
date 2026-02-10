<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkloadEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field_values' => ['sometimes', 'required', 'array'],
            'calculated_score' => ['nullable', 'numeric'],
            'report_id' => ['sometimes', 'required', 'integer', 'exists:reports,id'],
            'workload_form_id' => ['sometimes', 'required', 'integer', 'exists:workload_forms,id'],
            'subject_id' => ['sometimes', 'required', 'integer', 'exists:subjects,id'],
        ];
    }
}
