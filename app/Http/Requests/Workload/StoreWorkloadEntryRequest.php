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
            'calculated_score' => ['nullable', 'numeric', 'min:0'],
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
            if (! $formId) {
                return;
            }

            $form = WorkloadForm::with(['fields', 'quantitySubCriteria', 'subCriteriaItem.group'])->find($formId);
            if (! $form) {
                return;
            }

            $requiresSubject = $form->subCriteriaItem
                ? ! empty($form->subCriteriaItem->require_subject)
                : ! empty($form->quantitySubCriteria?->require_subject);

            if ($requiresSubject && ! $this->filled('subject_id')) {
                $validator->errors()->add('subject_id', 'กรุณาเลือกรายวิชาก่อนบันทึกข้อมูลภาระงาน');
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

            if (! array_key_exists($name, $normalized)) {
                $validator->errors()->add('field_values', "กรุณากรอกข้อมูลให้ครบ: {$name}");

                continue;
            }

            $value = $normalized[$name];

            if ($type === 'number' || $type === 'item') {
                if ($value === null || $value === '' || ! is_numeric($value)) {
                    $validator->errors()->add('field_values', "กรุณากรอกข้อมูลตัวเลขให้ครบ: {$name}");
                } elseif ((float) $value < 0) {
                    $validator->errors()->add('field_values', "ข้อมูลตัวเลขต้องไม่ติดลบ: {$name}");
                }

                continue;
            }

            if ($value === null || $value === '') {
                $validator->errors()->add('field_values', "กรุณากรอกข้อมูลให้ครบ: {$name}");

                continue;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                $validator->errors()->add('field_values', "ข้อมูลไม่ถูกต้อง: {$name}");
            }
        }
    }
}
