<?php

// ไฟล์คลาสของระบบ: app/Http/Requests/Workload/UpdateWorkloadEntryRequest.php

namespace App\Http\Requests\Workload;

use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'calculated_score' => ['nullable', 'numeric', 'min:0'],
            'report_id' => ['sometimes', 'required', 'integer', 'exists:reports,id'],
            'workload_form_id' => ['sometimes', 'required', 'integer', 'exists:workload_forms,id'],
            'subject_id' => ['sometimes', 'nullable', 'integer', 'exists:subjects,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->has('field_values')) {
                return;
            }

            $formId = $this->input('workload_form_id');
            if (! $formId) {
                $entryId = $this->route('id');
                if ($entryId) {
                    $entry = WorkloadEntry::find($entryId);
                    $formId = $entry?->workload_form_id;
                }
            }

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
