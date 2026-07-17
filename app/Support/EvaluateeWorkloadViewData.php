<?php

namespace App\Support;

use Illuminate\Support\Collection;

class EvaluateeWorkloadViewData
{
    public static function build($quantitySubCriteria, Collection $workloadForms, Collection $workloadEntriesByFormId, Collection $evidenceLinksByEntryId): array
    {
        $groups = collect($quantitySubCriteria?->groups ?? [])->map(function ($group) use ($workloadForms, $workloadEntriesByFormId, $evidenceLinksByEntryId) {
            $groupFormIds = collect($group->items ?? [])
                ->map(function ($item) use ($workloadForms) {
                    return $workloadForms->firstWhere('quantity_sub_criteria_item_id', $item->id)?->id;
                })
                ->filter()
                ->unique()
                ->values();

            $fieldDefinitions = $workloadForms
                ->filter(fn ($form) => $groupFormIds->contains($form->id))
                ->flatMap(fn ($form) => $form->fields ?? collect())
                ->filter(fn ($field) => ! empty($field->variable_name))
                ->unique(fn ($field) => self::fieldIdentity($field))
                ->values();

            $itemViews = collect($group->items ?? [])->map(function ($item) use ($group, $workloadForms, $workloadEntriesByFormId, $evidenceLinksByEntryId) {
                $requiresSubject = ! empty($item?->require_subject);
                $itemForm = $workloadForms->firstWhere('quantity_sub_criteria_item_id', $item->id);
                $itemEntries = $itemForm ? ($workloadEntriesByFormId[$itemForm->id] ?? collect()) : collect();
                $formFields = $itemForm?->fields
                    ? $itemForm->fields
                        ->filter(fn ($field) => strtolower((string) ($field->field_type ?? 'number')) !== 'item' && ! empty($field->variable_name))
                        ->unique(fn ($field) => self::fieldIdentity($field))
                        ->values()
                    : collect();

                $itemHasGroupField = $formFields->contains(fn ($field) => self::isGroupField($field));
                $tableFields = $itemHasGroupField
                    ? $formFields
                    : $formFields->reject(fn ($field) => self::isGroupField($field))->values();

                $showLevelColumn = $itemEntries->contains(fn ($entry) => ! empty($entry?->subject_id));
                $itemTotalScore = (float) $itemEntries->sum(fn ($entry) => max(0, (float) ($entry->calculated_score ?? 0)));

                $rows = $itemEntries->map(function ($itemEntry) use ($item, $itemForm, $tableFields, $evidenceLinksByEntryId, $requiresSubject, $showLevelColumn, $group) {
                    $normalizedFieldValues = self::normalizeFieldValues((array) ($itemEntry?->field_values ?? []));
                    [$selectedFormItem, $selectedItemId] = self::resolveSelectedFormItem($itemForm, $normalizedFieldValues);

                    $displayColumns = $tableFields->map(function ($field) use ($normalizedFieldValues) {
                        $fieldKey = strtolower((string) $field->variable_name);

                        return [
                            'key' => $fieldKey,
                            'value' => self::lookupFieldValue($normalizedFieldValues, $fieldKey) ?? '-',
                        ];
                    })->values();

                    $subjectDisplay = null;
                    if ($itemEntry?->subject) {
                        $subjectDisplay = trim(collect([
                            $itemEntry->subject->code ?? null,
                            $itemEntry->subject->display_name
                                ?? $itemEntry->subject->name_th
                                ?? $itemEntry->subject->name_en
                                ?? null,
                        ])->filter()->implode(' '));
                    }

                    $evidenceLinks = $evidenceLinksByEntryId[$itemEntry->id] ?? collect();
                    $scoreValue = $itemEntry?->calculated_score;
                    $scoreValue = is_numeric($scoreValue) ? max(0, (float) $scoreValue) : $scoreValue;
                    $scoreDisplay = is_numeric($scoreValue)
                        ? number_format((float) $scoreValue, 2, '.', '')
                        : ($scoreValue ?? '-');

                    return [
                        'id' => $itemEntry->id,
                        'item_name' => $item->name ?? '-',
                        'subject_display' => $subjectDisplay !== '' && $subjectDisplay !== null ? $subjectDisplay : '-',
                        'requires_subject' => $requiresSubject,
                        'show_level_column' => $showLevelColumn,
                        'level_text' => $selectedFormItem?->label ?? $item->description ?? '-',
                        'item_score' => $selectedFormItem?->score,
                        'display_columns' => $displayColumns,
                        'score_display' => $scoreDisplay,
                        'evidence_links' => $evidenceLinks->values(),
                        'selected_item_id' => $selectedItemId,
                        'subject_id' => $itemEntry->subject_id,
                        'form_id' => $itemEntry->workload_form_id,
                        'group_id' => $group->id,
                        'group_name' => $group->name ?? '',
                        'field_values' => $normalizedFieldValues,
                    ];
                })->values();

                $emptyColumnCount = 4 + max(1, $tableFields->count());
                if ($showLevelColumn) {
                    $emptyColumnCount = 5 + max(1, $tableFields->count());
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name ?? '-',
                    'description' => $item->description ?? '-',
                    'form_id' => $itemForm->id ?? '',
                    'table_fields' => $tableFields->map(function ($field) {
                        $fieldLabel = $field->label ?? $field->variable_name;
                        if (self::isGroupField($field)) {
                            $fieldLabel = trim((string) $fieldLabel) !== '' ? trim((string) $fieldLabel) : 'กลุ่ม';
                        }

                        return [
                            'key' => strtolower((string) $field->variable_name),
                            'label' => $fieldLabel,
                        ];
                    })->values(),
                    'rows' => $rows,
                    'show_level_column' => $showLevelColumn,
                    'requires_subject' => $requiresSubject,
                    'item_total_display' => number_format($itemTotalScore, 2, '.', ''),
                    'empty_column_count' => $emptyColumnCount,
                ];
            })->values();

            $groupTotalScore = $itemViews->sum(function ($itemView) {
                return collect($itemView['rows'] ?? [])->sum(function ($row) {
                    return (float) str_replace(',', '', (string) ($row['score_display'] ?? 0));
                });
            });

            return [
                'id' => $group->id,
                'name' => $group->name ?? '',
                'requires_subject' => $itemViews->contains(fn ($itemView) => ! empty($itemView['requires_subject'])),
                'field_definitions' => $fieldDefinitions->map(fn ($field) => $field->label ?? $field->variable_name)->values(),
                'items' => $itemViews,
                'group_total_score' => $groupTotalScore,
            ];
        })->values();

        return [
            'requires_subject' => $groups->contains(fn ($group) => ! empty($group['requires_subject'])),
            'groups' => $groups,
            'total_display' => is_numeric($groups->sum('group_total_score'))
                ? number_format((float) $groups->sum('group_total_score'), 2, '.', '')
                : '-',
        ];
    }

    private static function fieldIdentity($field): string
    {
        $label = trim((string) ($field->label ?? ''));

        return $label !== '' ? mb_strtolower($label) : strtolower((string) ($field->variable_name ?? ''));
    }

    private static function isGroupField($field): bool
    {
        $label = trim((string) ($field->label ?? ''));
        $varName = trim((string) ($field->variable_name ?? ''));
        $labelLower = $label !== '' ? mb_strtolower($label) : '';
        $varLower = $varName !== '' ? strtolower($varName) : '';

        return ($labelLower !== '' && str_contains($labelLower, 'กลุ่ม'))
            || ($varLower !== '' && str_contains($varLower, 'group'));
    }

    private static function normalizeFieldValues(array $fieldValues): array
    {
        $normalizedFieldValues = [];
        foreach ($fieldValues as $key => $value) {
            $rawKey = (string) $key;
            $trimKey = trim($rawKey);
            $noSpaceKey = str_replace(' ', '', $trimKey);
            $candidates = [
                $rawKey,
                strtolower($rawKey),
                $trimKey,
                strtolower($trimKey),
                $noSpaceKey,
                strtolower($noSpaceKey),
            ];

            foreach ($candidates as $candidate) {
                if ($candidate === '') {
                    continue;
                }
                $normalizedFieldValues[$candidate] = $value;
            }

            if (strtolower($trimKey) === 'item_*') {
                $normalizedFieldValues['item_star'] = $value;
            }
        }

        return $normalizedFieldValues;
    }

    private static function resolveSelectedFormItem($itemForm, array $normalizedFieldValues): array
    {
        $selectedFormItem = null;
        $selectedItemId = null;

        if ($itemForm && $itemForm->items) {
            foreach ($itemForm->items as $formItem) {
                $sequence = (int) ($formItem->sequence ?? 0);
                if ($sequence <= 0) {
                    continue;
                }

                $varName = 'item_'.$sequence;
                if (array_key_exists($varName, $normalizedFieldValues)) {
                    $selectedFormItem = $formItem;
                    $selectedItemId = $formItem->id;
                    break;
                }
            }
        }

        return [$selectedFormItem, $selectedItemId];
    }

    private static function lookupFieldValue(array $normalizedFieldValues, string $fieldKey)
    {
        $lookupKeys = [$fieldKey, str_replace(' ', '', $fieldKey)];

        foreach ($lookupKeys as $lookupKey) {
            if ($lookupKey !== '' && array_key_exists($lookupKey, $normalizedFieldValues)) {
                return $normalizedFieldValues[$lookupKey];
            }
        }

        return null;
    }
}
