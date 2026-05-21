<?php

// ไฟล์คลาสของระบบ: app/Support/EvaluateeWorkloadModalData.php

namespace App\Support;

use Illuminate\Support\Collection;

class EvaluateeWorkloadModalData
{
    public static function build($quantitySubCriteria, Collection $workloadForms, Collection $subjects): array
    {
        return [
            'requires_subject' => !empty($quantitySubCriteria?->require_subject),
            'requires_evidence' => !empty($quantitySubCriteria?->require_evidence),
            'subjects' => $subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'code' => $subject->code,
                    'name_th' => $subject->name_th,
                    'name_en' => $subject->name_en,
                    'credits' => $subject->credits ?? '',
                    'lecture_credits' => $subject->lecture_credits ?? 0,
                    'lab_credits' => $subject->lab_credits ?? 0,
                    'self_study_credits' => $subject->self_study_credits ?? 0,
                    'search' => mb_strtolower(trim(($subject->code ?? '') . ' ' . ($subject->name_th ?? '') . ' ' . ($subject->name_en ?? ''))),
                ];
            })->values(),
            'workload_item_options' => $workloadForms->flatMap(function ($form) {
                return collect($form->items ?? [])->map(function ($item) use ($form) {
                    $sequence = (int) ($item->sequence ?? 0);

                    return [
                        'id' => $item->id,
                        'form_id' => $form->id,
                        'group_id' => $form->subCriteriaItem->quantity_sub_criteria_group_id ?? '',
                        'sequence' => $sequence,
                        'variable_name' => 'item_' . $sequence,
                        'score' => $item->score,
                        'label' => $item->label,
                    ];
                });
            })->values(),
            'forms' => $workloadForms->map(function ($form) {
                $fields = collect($form->fields ?? [])->map(function ($field) {
                    $fieldType = strtolower((string) ($field->field_type ?? 'number'));
                    $defaultValue = $field->default_value ?? '';

                    return [
                        'field_type' => $fieldType,
                        'variable_name' => $field->variable_name,
                        'label' => $field->label ?? $field->variable_name,
                        'note' => $field->note,
                        'default_value' => $defaultValue,
                        'is_hidden_default' => $fieldType !== 'item' && $defaultValue !== '',
                        'is_renderable_input' => $fieldType !== 'item' && $defaultValue === '',
                        'input_type' => $fieldType === 'text' ? 'text' : 'number',
                    ];
                })->values();

                return [
                    'id' => $form->id,
                    'fields' => $fields,
                    'has_renderable_fields' => $fields->contains(fn ($field) => !empty($field['is_renderable_input'])),
                ];
            })->values(),
        ];
    }
}
