<?php

namespace App\Http\Requests\Workload;

use App\Models\WorkloadForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $formId = $this->input('workload_form_id');
            if (!$formId) {
                return;
            }
            $form = WorkloadForm::with('fields')->find($formId);
            if (!$form) {
                return;
            }
            $this->validateFieldValues($validator, $form->fields, (array) $this->input('field_values', []));
        });
    }

    private function validateFieldValues(Validator $validator, $fields, array $values): void
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            $normalized[strtolower((string) $key)] = $value;
        }

        foreach ($fields as $field) {
            $name = strtolower((string) $field->variable_name);
            $type = strtolower((string) ($field->field_type ?? 'number'));
            if ($name === '') {
                continue;
            }
            if (!array_key_exists($name, $normalized)) {
                if ($type !== 'text') {
                    $validator->errors()->add('field_values', "กรุณากรอกค่าตัวแปร: {$name}");
                }
                continue;
            }
            $value = $normalized[$name];
            if ($type === 'number') {
                if ($value === null || $value === '' || !is_numeric($value)) {
                    $validator->errors()->add('field_values', "ค่าตัวแปรต้องเป็นตัวเลข: {$name}");
                }
            } else {
                if ($value !== null && $value !== '' && !is_string($value) && !is_numeric($value)) {
                    $validator->errors()->add('field_values', "ค่าตัวแปรต้องเป็นข้อความ: {$name}");
                }
            }
        }
    }
}
