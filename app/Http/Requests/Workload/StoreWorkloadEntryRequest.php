<?php

namespace App\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkloadEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field_values' => ['required', 'array'],
            'calculated_score' => ['nullable', 'numeric'],
            'report_id' => ['required', 'integer', 'exists:reports,id'],
            'workload_form_id' => ['required', 'integer', 'exists:workload_forms,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ];
    }
}
