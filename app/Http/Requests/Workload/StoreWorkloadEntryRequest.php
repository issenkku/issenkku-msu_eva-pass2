<?php

namespace App\Http\Requests\Workload;

use App\Models\WorkloadForm;
use App\Models\WorkloadFormItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWorkloadEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('workload_form_id') && $this->filled('workload_form_item_id')) {
            $item = WorkloadFormItem::find($this->input('workload_form_item_id'));
            if ($item) {
                $this->merge(['workload_form_id' => $item->workload_form_id]);
            }
        }

        if (! $this->has('field_values')) {
            $this->merge(['field_values' => []]);
        }
    }

    public function rules(): array
    {
        return [
            'field_values' => ['nullable', 'array'],
            'calculated_score' => ['nullable', 'numeric'],
            'report_id' => ['required', 'integer', 'exists:reports,id'],
            'workload_form_id' => ['required_without:workload_form_item_id', 'integer', 'exists:workload_forms,id'],
            'workload_form_item_id' => ['required_without:workload_form_id', 'integer', 'exists:workload_form_items,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'evidence_links' => ['nullable', 'array'],
            'evidence_links.*' => ['nullable', 'string'],
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
                if ($type !== 'text' && $type !== 'item') {
                    $validator->errors()->add('field_values', "กรุณากรอกค่าตัวแปร: {$name}");
                }
                continue;
            }
            $value = $normalized[$name];
            if ($type === 'number' || $type === 'item') {
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
