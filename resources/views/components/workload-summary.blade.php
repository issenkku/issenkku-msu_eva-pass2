@props([
    'subCriteria' => null,
    'workloadForms' => collect(),
    'workloadEntriesByFormId' => collect(),
    'evidenceLinksByEntryId' => collect(),
])

@if (!$subCriteria)
    {{-- แสดงข้อความเมื่อไม่มีข้อมูลภาระงาน --}}
    <div class="text-sm text-gray-500">ไม่พบข้อมูลภาระงาน</div>
@else
    @forelse ($subCriteria->groups ?? [] as $group)
        @php
            $groupFormIds = ($group->items ?? collect())
                ->map(function ($item) use ($workloadForms) {
                    return $workloadForms
                        ->firstWhere('quantity_sub_criteria_item_id', $item->id)
                        ?->id;
                })
                ->filter()
                ->unique()
                ->values();

            $fieldDefinitions = $workloadForms
                ->filter(function ($form) use ($groupFormIds) {
                    return $groupFormIds->contains($form->id);
                })
                ->flatMap(function ($form) {
                    return $form->fields ?? collect();
                })
                ->filter(function ($field) {
                    return !empty($field->variable_name);
                })
                ->unique(function ($field) {
                    $label = trim((string) ($field->label ?? ''));
                    return $label !== '' ? mb_strtolower($label) : strtolower((string) $field->variable_name);
                })
                ->values();

            $isGroupField = function ($field) {
                $label = trim((string) ($field->label ?? ''));
                $varName = trim((string) ($field->variable_name ?? ''));
                $labelLower = $label !== '' ? mb_strtolower($label) : '';
                $varLower = $varName !== '' ? strtolower($varName) : '';

                return ($labelLower !== '' && str_contains($labelLower, 'กลุ่ม'))
                    || ($varLower !== '' && str_contains($varLower, 'group'));
            };
        @endphp

        {{-- บล็อกกลุ่มภาระงาน --}}
        <div class="mb-6">
            <div class="mb-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="h-7 w-1.5 rounded-full bg-gradient-to-b from-sky-400 to-indigo-500"></span>
                    <div class="text-lg font-semibold tracking-wide text-slate-900">
                        {{ $group->name ?? '-' }}
                    </div>
                </div>
            </div>

            @forelse ($group->items ?? [] as $item)
                @php
                    $itemForm = $workloadForms->firstWhere('quantity_sub_criteria_item_id', $item->id);
                    $itemEntries = $itemForm ? ($workloadEntriesByFormId[$itemForm->id] ?? collect()) : collect();
                    $formFields = $itemForm?->fields
                        ? $itemForm->fields->filter(function ($field) {
                            return strtolower((string) ($field->field_type ?? 'number')) !== 'item'
                                && !empty($field->variable_name);
                        })->unique(function ($field) {
                            $label = trim((string) ($field->label ?? ''));
                            return $label !== '' ? mb_strtolower($label) : strtolower((string) $field->variable_name);
                        })->values()
                        : collect();
                    $itemHasGroupField = $formFields->contains(function ($field) use ($isGroupField) {
                        return $isGroupField($field);
                    });
                    $tableFields = $itemHasGroupField
                        ? $formFields
                        : $formFields->reject(function ($field) use ($isGroupField) {
                            return $isGroupField($field);
                        })->values();
                @endphp

                <div class="mb-4">
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        {{-- ตารางข้อมูลภาระงาน --}}
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-200 text-slate-800">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">กิจกรรม/โครงการ/งาน</th>
                                    <th class="px-3 py-2 text-left font-semibold">ระดับ</th>
                                    @forelse ($tableFields as $field)
                                        @php
                                            $fieldLabel = $field->label ?? $field->variable_name;
                                            if ($isGroupField($field)) {
                                                $fieldLabel = preg_replace('/\\d+$/', '', (string) $fieldLabel);
                                                $fieldLabel = trim($fieldLabel) !== '' ? trim($fieldLabel) : 'กลุ่ม';
                                            }
                                        @endphp
                                        <th class="px-3 py-2 text-left font-semibold">{{ $fieldLabel }}</th>
                                    @empty
                                        <th class="px-3 py-2 text-left font-semibold">-</th>
                                    @endforelse
                                    <th class="px-3 py-2 text-left font-semibold">ภาระงาน</th>
                                    <th class="px-3 py-2 text-left font-semibold">ลิงก์เอกสาร</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($itemEntries as $itemEntry)
                                    @php
                                        $field_values = $itemEntry?->field_values ?? [];
                                        $normalizedFieldValues = [];
                                        foreach ($field_values as $key => $value) {
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

                                        $itemLabel = null;
                                        $itemScore = null;
                                        $selectedFormItem = null;
                                        if ($itemForm && $itemForm->items) {
                                            foreach ($itemForm->items as $formItem) {
                                                $sequence = (int) ($formItem->sequence ?? 0);
                                                if ($sequence <= 0) {
                                                    continue;
                                                }
                                                $varName = 'item_' . $sequence;
                                                if (array_key_exists($varName, $normalizedFieldValues)) {
                                                    $selectedFormItem = $formItem;
                                                    break;
                                                }
                                            }
                                        }

                                        if ($selectedFormItem) {
                                            $itemLabel = $selectedFormItem->label;
                                            $itemScore = $selectedFormItem->score;
                                        }

                                        $displayMap = [];
                                        foreach ($tableFields as $field) {
                                            $name = strtolower(trim((string) $field->variable_name));
                                            if ($name === 'item_*') {
                                                $name = 'item_star';
                                            }
                                            $lookupKeys = [$name, str_replace(' ', '', $name)];
                                            $value = null;
                                            foreach ($lookupKeys as $lookupKey) {
                                                if ($lookupKey !== '' && array_key_exists($lookupKey, $normalizedFieldValues)) {
                                                    $value = $normalizedFieldValues[$lookupKey];
                                                    break;
                                                }
                                            }
                                            $displayMap[$name] = $value;
                                        }

                                        $evidenceLinks = isset($evidenceLinksByEntryId)
                                            ? ($evidenceLinksByEntryId[$itemEntry->id] ?? collect())
                                            : collect();
                                        $scoreValue = $itemEntry?->calculated_score;
                                        $scoreDisplay = is_numeric($scoreValue)
                                            ? number_format((float) $scoreValue, 0, '.', '')
                                            : ($scoreValue ?? '-');
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2">{{ $item->name ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            {{ $itemLabel ?? $item->description ?? '-' }}
                                            @if ($itemScore !== null)
                                                <span class="text-xs text-gray-500">({{ $itemScore }})</span>
                                            @endif
                                        </td>
                                        @forelse ($tableFields as $field)
                                            @php
                                                $fieldKey = strtolower((string) $field->variable_name);
                                            @endphp
                                            <td class="px-3 py-2">{{ $displayMap[$fieldKey] ?? '-' }}</td>
                                        @empty
                                            <td class="px-3 py-2">-</td>
                                        @endforelse
                                        <td class="px-3 py-2">{{ $scoreDisplay }}</td>
                                        <td class="px-3 py-2">
                                            @forelse ($evidenceLinks as $link)
                                                <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="break-all text-blue-600 hover:underline">
                                                    {{ $link }}
                                                </a><br>
                                            @empty
                                                -
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-3 py-2 text-gray-500" colspan="{{ 4 + max(1, $tableFields->count()) }}">
                                            ไม่พบข้อมูลในหมวดย่อยนี้
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500">ไม่พบรายการภาระงาน</div>
            @endforelse
        </div>
    @empty
        {{-- แสดงข้อความเมื่อไม่พบกลุ่มภาระงาน --}}
        <div class="text-sm text-gray-500">ไม่พบกลุ่มภาระงาน</div>
    @endforelse
@endif
