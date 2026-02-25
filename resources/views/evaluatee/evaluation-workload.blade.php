@extends('layouts.app')

@section('title', 'ภาระงานด้านงานสอน')

@section('content')
{{-- บล็อกเนื้อหา --}}
<div class="max-w-6xl mx-auto space-y-6">


    {{-- @if(isset($quantitySubCriteria) && $quantitySubCriteria)
        <section class="workload-panel">
            <div class="workload-panel-header">
                <div class="workload-summary-value">
                            {{ $quantitySubCriteria->name ?? '-' }}
                        </div>
            </div>
            <div class="workload-panel-body">
                <div class="workload-summary-card" style="background: #eef2ff; color: #0f172a;">
                    <div>
                        <div class="workload-summary-title">Quantity Sub Criteria</div>
                        <div class="workload-summary-value">
                            {{ $quantitySubCriteria->name ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="workload-table-wrap" style="margin-top: 16px;">
                    {{-- ตารางข้อมูล --}}
                    <table class="workload-table">
                        <thead>
                            <tr>
                                <th>Group ID</th>
                                <th>ชื่อกลุ่ม</th>
                                <th>ลำดับ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quantitySubCriteria->groups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td>{{ $group->name ?? '-' }}</td>
                                    <td>{{ $group->sequence ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">ไม่พบข้อมูล quantity_sub_criteria_groups</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="workload-table-wrap" style="margin-top: 16px;">
                    {{-- ตารางข้อมูล --}}
                    <table class="workload-table">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>ชื่อรายการ</th>
                                <th>ลำดับ</th>
                                <th>score_a</th>
                                <th>score_b</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupItems = $quantitySubCriteria->groups
                                    ->flatMap(function ($group) {
                                        return $group->items ?? collect();
                                    });
                            @endphp
                            @forelse($groupItems as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name ?? '-' }}</td>
                                    <td>{{ $item->sequence ?? '-' }}</td>
                                    <td>{{ $item->score_a ?? '-' }}</td>
                                    <td>{{ $item->score_b ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">ไม่พบข้อมูล quantity_sub_criteria_items</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="workload-table-wrap" style="margin-top: 16px;">
                    {{-- ตารางข้อมูล --}}
                    <table class="workload-table">
                        <thead>
                            <tr>
                                <th>Form ID</th>
                                <th>formula_logic</th>
                                <th>quantity_sub_criteria_item_id</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workloadForms as $form)
                                <tr>
                                    <td>{{ $form->id }}</td>
                                    <td>{{ $form->formula_logic ?? '-' }}</td>
                                    <td>{{ $form->quantity_sub_criteria_item_id ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">ไม่พบข้อมูล workload_forms</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="workload-table-wrap" style="margin-top: 16px;">
                    {{-- ตารางข้อมูล --}}
                    <table class="workload-table">
                        <thead>
                            <tr>
                                <th>Field ID</th>
                                <th>label</th>
                                <th>variable_name</th>
                                <th>field_type</th>
                                <th>workload_form_id</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $formFields = $workloadForms->flatMap(function ($form) {
                                    return $form->fields ?? collect();
                                });
                            @endphp
                            @forelse($formFields as $field)
                                <tr>
                                    <td>{{ $field->id }}</td>
                                    <td>{{ $field->label ?? '-' }}</td>
                                    <td>{{ $field->variable_name ?? '-' }}</td>
                                    <td>{{ $field->field_type ?? '-' }}</td>
                                    <td>{{ $field->workload_form_id ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">ไม่พบข้อมูล workload_form_fields</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="workload-table-wrap" style="margin-top: 16px;">
                    {{-- ตารางข้อมูล --}}
                    <table class="workload-table">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>label</th>
                                <th>score</th>
                                <th>sequence</th>
                                <th>workload_form_id</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($formItems as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->label ?? '-' }}</td>
                                    <td>{{ $item->score ?? '-' }}</td>
                                    <td>{{ $item->sequence ?? '-' }}</td>
                                    <td>{{ $item->workload_form_id ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">ไม่พบข้อมูล workload_form_items</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif --}}

    {{--  --}}
    <div class="workload-page-header">
        <h1 class="workload-page-title">  {{ $quantitySubCriteria->name ?? '-' }}</h1>
        <p class="workload-page-subtitle">กรอกข้อมูลภาระงานและตรวจสอบผลรวม</p>
    </div>

    @if(session('success'))
    {{--  --}}
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        {{-- บล็อกเนื้อหา --}}
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endif

    @if(isset($quantitySubCriteria) && $quantitySubCriteria)
        @forelse($quantitySubCriteria->groups as $group)
            {{-- ส่วนย่อยของหน้า --}}
            <section class="workload-panel">
                <div class="workload-panel-header">
                    <h2>{{ $group->name ?? '' }}</h2>
                </div>
                <div class="workload-panel-body">
                    @php
                        $firstItem = $group->items->first();
                    @endphp
                    <details class="workload-dropdown">
                        <summary class="workload-toolbar">
                            <button type="button" class="workload-select">
                                <span>{{ $group->name ?? '' }}</span>
                                {{-- <i class="fas fa-chevron-down"></i> --}}
                            </button>
                    @php
                        $groupFormIds = $group->items
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
                            <div class="workload-toolbar-columns">
                                @forelse($fieldDefinitions as $field)
                                    <span>{{ $field->label ?? $field->variable_name }}</span>
                                @empty
                                    <span>-</span>
                                @endforelse
                            </div>
                        </summary>

                        @php
                            $fieldColumnCount = max(1, $fieldDefinitions->count());
                            $tableColumnCount = 5 + $fieldColumnCount;
                        @endphp
                        @forelse($group->items as $item)
                            @php
                                $itemForm = $workloadForms->firstWhere('quantity_sub_criteria_item_id', $item->id);
                                $itemEntries = $itemForm ? ($workloadEntriesByFormId[$itemForm->id] ?? collect()) : collect();
                                $formFields = $itemForm?->fields
                                    ? $itemForm->fields->filter(function ($field) {
                                        return strtolower((string) ($field->field_type ?? 'number')) !== 'item';
                                    })->values()
                                    : collect();
                                $itemHasGroupField = $formFields->contains(function ($field) use ($isGroupField) {
                                    return $isGroupField($field);
                                });
                                $tableFields = $itemHasGroupField
                                    ? $fieldDefinitions
                                    : $fieldDefinitions->reject(function ($field) use ($isGroupField) {
                                        return $isGroupField($field);
                                    })->values();
                                $showLevelColumn = $itemEntries->contains(function ($entry) {
                                    return !empty($entry?->subject_id);
                                });
                                $itemTotalScore = $itemEntries->sum(function ($entry) {
                                    return (float) ($entry->calculated_score ?? 0);
                                });
                                $itemTotalDisplay = is_numeric($itemTotalScore)
                                    ? number_format((float) $itemTotalScore, 0, '.', '')
                                    : ($itemTotalScore ?? '-');
                            @endphp
                            <details class="workload-item-dropdown">
                                <summary class="workload-item-summary">
                                    <div class="workload-item-summary-left">
                                        <span class="workload-item-summary-title">{{ $item->name ?? '-' }}</span>
                                    </div>
                                    {{-- <div class="workload-item-summary-right">
                                        <span class="workload-item-summary-score">คะแนน {{ $itemTotalDisplay }}</span>
                                        <span class="workload-item-summary-icon"></span>
                                    </div> --}}
                                </summary>
                                <div class="workload-subtable">
                                    <div class="workload-table-wrap">
                                    {{-- ตารางข้อมูล --}}
                                    <table class="workload-table">
                                        <thead>
                                            <tr>
                                                <th>กิจกรรม/โครงการ/งาน</th>
                                                @if($showLevelColumn)
                                                    <th>ระดับ</th>
                                                @endif
                                                @forelse($tableFields as $field)
                                                    @php
                                                        $fieldLabel = $field->label ?? $field->variable_name;
                                                        if ($isGroupField($field)) {
                                                            $fieldLabel = preg_replace('/\\d+$/', '', (string) $fieldLabel);
                                                            $fieldLabel = trim($fieldLabel) !== '' ? trim($fieldLabel) : 'กลุ่ม';
                                                        }
                                                    @endphp
                                                    <th>{{ $fieldLabel }}</th>
                                                @empty
                                                    <th>-</th>
                                                @endforelse
                                                <th>ภาระงาน</th>
                                                <th>ลิงก์เอกสาร</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($itemEntries as $itemEntry)
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
                                                    $selectedItemId = null;
                                                    if ($itemForm && $itemForm->items) {
                                                        foreach ($itemForm->items as $formItem) {
                                                            $sequence = (int) ($formItem->sequence ?? 0);
                                                            if ($sequence <= 0) {
                                                                continue;
                                                            }
                                                            $varName = 'item_' . $sequence;
                                                            if (array_key_exists($varName, $normalizedFieldValues)) {
                                                                $selectedItemId = $formItem->id;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $item->name ?? '-' }}</td>
                                                    @if($showLevelColumn)
                                                        <td>
                                                            {{ $itemLabel ?? $item->description ?? '-' }}
                                                            @if($itemScore !== null)
                                                                <span class="workload-item-score">({{ $itemScore }})</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    @forelse($tableFields as $field)
                                                        @php
                                                            $fieldKey = strtolower((string) $field->variable_name);
                                                        @endphp
                                                        <td>{{ $displayMap[$fieldKey] ?? '-' }}</td>
                                                    @empty
                                                        <td>-</td>
                                                    @endforelse
                                                    <td>
                                                        @php
                                                            $scoreValue = $itemEntry?->calculated_score;
                                                            $scoreDisplay = is_numeric($scoreValue)
                                                                ? number_format((float) $scoreValue, 0, '.', '')
                                                                : ($scoreValue ?? '-');
                                                        @endphp
                                                        {{ $scoreDisplay }}
                                                    </td>
                                                    <td>
                                                        @forelse($evidenceLinks as $link)
                                                            <a href="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $link }}</a><br>
                                                        @empty
                                                            -
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        <div class="workload-row-actions">
                                                            <button
                                                                type="button"
                                                                class="workload-mini-btn workload-edit-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#workloadAddModal"
                                                                data-entry-id="{{ $itemEntry->id }}"
                                                                data-form-id="{{ $itemEntry->workload_form_id }}"
                                                                data-item-id="{{ $selectedItemId ?? '' }}"
                                                                data-subject-id="{{ $itemEntry->subject_id }}"
                                                                data-group-id="{{ $group->id }}"
                                                                data-group-name="{{ $group->name ?? '' }}"
                                                                data-field-values='@json($normalizedFieldValues)'
                                                                data-evidence-links='@json($evidenceLinks->values())'
                                                            >
                                                                แก้ไข
                                                            </button>
                                                            <x-button
                                                                type="danger"
                                                                text="ลบ"
                                                                class="workload-mini-btn workload-delete-btn"
                                                                icon="fas fa-trash-alt"
                                                                buttonType="button"
                                                                onclick="confirmDelete({{ $itemEntry->id }})"
                                                            />
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td>{{ $item->name ?? '-' }}</td>
                                                    @if($showLevelColumn)
                                                        <td>{{ $item->description ?? '-' }}</td>
                                                    @endif
                                                        @forelse($tableFields as $field)
                                                        <td>-</td>
                                                        @empty
                                                            <td>-</td>
                                                    @endforelse
                                                    <td>-</td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        <div class="workload-row-actions">
                                                            <button
                                                                class="workload-mini-btn workload-add-btn"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#workloadAddModal"
                                                                data-default-form-id="{{ $itemForm->id ?? '' }}"
                                                                data-group-id="{{ $group->id }}"
                                                                data-group-name="{{ $group->name ?? '' }}"
                                                                data-item-id="{{ $item->id }}"
                                                            >
                                                                <i class="fas fa-plus"></i>
                                                                เพิ่มข้อมูล
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </details>
                        @empty
                            <div class="workload-subtable">
                                <div class="workload-subtable-title">ไม่มีรายการภาระงาน</div>
                                <div class="workload-table-wrap">
                                    {{-- ตารางข้อมูล --}}
                                    <table class="workload-table">
                                        <thead>
                                            @php
                                                $showLevelColumn = false;
                                                $tableColumnCount = 4 + $fieldColumnCount;
                                            @endphp
                                            <tr>
                                                <th>กิจกรรม/โครงการ/งาน</th>
                                                @if($showLevelColumn)
                                                    <th>ระดับ</th>
                                                    @php
                                                        $tableColumnCount = 5 + $fieldColumnCount;
                                                    @endphp
                                                @endif
                                                @forelse($fieldDefinitions as $field)
                                                    <th>{{ $field->label ?? $field->variable_name }}</th>
                                                @empty
                                                    <th>-</th>
                                                @endforelse
                                                <th>ภาระงาน</th>
                                                <th>ลิงก์เอกสาร</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ $tableColumnCount }}">ไม่พบข้อมูลในหมวดย่อยนี้</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforelse
                    </details>

                    @php
                        $groupTotalScore = 0;
                        $groupItems = $group->items ?? collect();
                        foreach ($groupItems as $groupItem) {
                            $itemForm = $workloadForms->firstWhere('quantity_sub_criteria_item_id', $groupItem->id);
                            $itemEntries = $itemForm ? ($workloadEntriesByFormId[$itemForm->id] ?? collect()) : collect();
                            $groupTotalScore += $itemEntries->sum(function ($entry) {
                                return (float) ($entry->calculated_score ?? 0);
                            });
                        }
                    @endphp
                    <div class="workload-total-row">
                        <span>รวมภาระงาน</span>
                        <span class="workload-total-value">{{ $groupTotalScore }}</span>
                    </div>
                </div>
            </section>
        @empty
            {{-- ส่วนย่อยของหน้า --}}
            <section class="workload-panel">
                <div class="workload-panel-header">
                    <h2>ไม่พบกลุ่มภาระงาน</h2>
                </div>
                <div class="workload-panel-body">
                    <p class="text-sm text-gray-600">ไม่มีข้อมูลสำหรับรายการนี้</p>
                </div>
            </section>
        @endforelse
    @endif


    {{-- ส่วนย่อยของหน้า --}}
    <section class="workload-panel">
        {{--  --}}
        <div class="workload-panel-header">
            <h2>รวมภาระงาน</h2>
        </div>
        {{--  --}}
        <div class="workload-panel-body">
            <div class="workload-summary-card">
                <div>
                    <div class="workload-summary-title">ภาระงานรวมทั้งหมด</div>
                    @php
                        $totalDisplay = is_numeric($workloadTotalScore ?? null)
                            ? number_format((float) $workloadTotalScore, 0, '.', '')
                            : ($workloadTotalScore ?? '-');
                    @endphp
                    <div class="workload-summary-value">{{ $totalDisplay }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- บล็อกเนื้อหา --}}
    <div class="workload-actions">
        <a href="{{ route('evaluation.show', $reportId) }}" class="workload-back-btn">
            <i class="fas fa-arrow-left"></i>
            ย้อนกลับ
        </a>
        {{-- ฟอร์ม --}}
        <form method="POST" action="{{ route('evaluatee.workload-score.store') }}">
            @csrf
            <input type="hidden" name="report_id" value="{{ $reportId }}">
            <input type="hidden" name="quantity_sub_criteria_id" value="{{ $quantitySubCriteriaId }}">
            <button type="submit" class="workload-save-btn">
                <i class="fas fa-save"></i>
                บันทึก
            </button>
        </form>
    </div>
</div>

{{--  --}}
<div class="modal fade" id="workloadAddModal" tabindex="-1" aria-labelledby="workloadAddModalLabel" aria-hidden="true" data-bs-focus="false">
    {{--  --}}
    <div class="modal-dialog modal-dialog-centered modal-lg">
        {{--  --}}
        <div class="modal-content workload-modal-content">
            {{-- ฟอร์ม --}}
            <form method="POST" id="workloadEntryForm" action="{{ route('evaluatee.workload-entries.store') }}" data-store-url="{{ route('evaluatee.workload-entries.store') }}" data-update-url="{{ route('evaluatee.workload-entries.update', '__id__') }}">
                @csrf
                <input type="hidden" id="workloadFormMethod" name="_method" value="">
                <div class="modal-header workload-modal-header">
                <h5 class="modal-title w-100 text-center" id="workloadAddModalLabel">เพิ่มข้อมูลภาระงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body workload-modal-body">
                <input type="hidden" name="report_id" value="{{ $reportId }}">
                <div class="workload-modal-section">
                    <label class="workload-modal-label">รายวิชา</label>
                    <select class="workload-modal-select" name="subject_id">
                        <option value="">-- เลือกรายวิชา --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" data-credits="{{ $subject->credits ?? '' }}">
                                {{ $subject->code }} {{ $subject->name_th }}{{ $subject->name_en ? ' ' . $subject->name_en : '' }} ({{ $subject->credits ?? '-' }} หน่วยกิต)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="workload-alert-box">
                    <div class="workload-alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="workload-alert-text">
                        หากไม่พบรายวิชาที่ต้องการเพิ่ม <a href="#" class="workload-alert-link workload-open-subject-modal">คลิกที่นี่</a> เพื่อเพิ่มด้วยตนเอง
                    </div>
                </div>

                <div class="workload-modal-grid">
                    <div class="workload-modal-field workload-modal-field-workload">
                        <label class="workload-modal-label">ภาระงาน <span class="required">*</span></label>
                        <select class="workload-modal-select" name="workload_form_item_id" id="workloadFormItemSelect" required>
                            <option value="">-- เลือกภาระงาน --</option>
                              @foreach($workloadForms as $form)
                                @foreach($form->items as $item)
                                    @php
                                        $sequence = (int) ($item->sequence ?? 0);
                                        $varName = "item_" . $sequence;
                                    @endphp
                                    <option
                                        value="{{ $item->id }}"
                                        data-form-id="{{ $form->id }}"
                                        data-group-id="{{ $form->subCriteriaItem->quantity_sub_criteria_group_id ?? '' }}"
                                        data-sequence="{{ $sequence }}"
                                        data-variable-name="{{ $varName }}"
                                        data-score="{{ $item->score }}"
                                    >
                                        {{ $form->subCriteriaItem->name ?? ("รายการ #" . $form->id) }} - {{ $item->label }} ({{ $item->score }})
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <input type="hidden" name="workload_form_id" id="workloadFormIdField" value="">
                        <input type="hidden" id="selectedItemField" value="">
                    </div>
             
                    <div class="workload-modal-field workload-modal-field-link">
                        <label class="workload-modal-label">แบบลิงก์หลักฐาน</label>
                        <div id="workload-evidence-links">
                            <div class="workload-evidence-row">
                                <input type="text" class="workload-modal-input" name="evidence_links[]" placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้" />
                                <button type="button" class="workload-evidence-remove-btn" title="ลบลิงก์">ลบ</button>
                            </div>
                        </div>
                        <button type="button" class="workload-link-btn" id="workload-add-evidence-link">
                            <i class="fas fa-link"></i> เพิ่มลิงก์หลักฐาน
                        </button>
                    </div>
                    <div class="workload-modal-field workload-modal-field-group">
                        <label class="workload-modal-label">หมวดย่อย</label>
                        <input type="text" class="workload-modal-input" id="workloadGroupLabel" value="-" readonly />
                        <span class="workload-modal-hint">ระบบจะกำหนดตามปุ่ม “เพิ่มข้อมูล” ที่กดจากแต่ละหมวดย่อย</span>
                    </div>
                    <div class="workload-modal-note workload-modal-note-box">
                        <h6>หมายเหตุ:</h6>
                        <ul>
                            <li>กรอกชื่อเอกสารที่ใช้</li>
                            <li>แบบลิงก์ URL สามารถแก้ไขได้</li>
                            <li>เช่น Google Drive, Dropbox, OneDrive</li>
                        </ul>
                    </div>
                    <div class="workload-modal-field workload-modal-field-credit" id="workload-detail-fields">
                        <label class="workload-modal-label">รายละเอียดที่ต้องกรอก</label>
                        @foreach($workloadForms as $form)
                            <div class="workload-form-fields" data-form-id="{{ $form->id }}" style="display:none;">
                                <div class="workload-modal-subfields">
                                @forelse($form->fields as $field)
                                    @php
                                        $fieldType = strtolower((string) ($field->field_type ?? "number"));
                                    @endphp
                                    @if($fieldType !== "item")
                                        <div class="workload-modal-subfield">
                                            <label class="workload-modal-sub-label">{{ $field->label ?? $field->variable_name }}</label>
                                            <input type="{{ $fieldType === 'text' ? 'text' : 'number' }}" class="workload-modal-input" name="field_values[{{ $field->variable_name }}]" />
                                        </div>
                                    @endif
                                @empty
                                    <div class="workload-modal-subfield">
                                        <label class="workload-modal-sub-label">หน่วยกิต</label>
                                        <input type="number" class="workload-modal-input" name="field_values[credits]" value="1" />
                                    </div>
                                @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                 
                </div>
            </div>
                <div class="modal-footer workload-modal-footer">
                <button type="button" class="workload-cancel-btn" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="workload-save-btn">บันทึก</button>
            </div>
            </form>
        </div>
    </div>
</div>
<x-subject-modal />
<style>
    .workload-page-header {
        background: linear-gradient(135deg, #f3e8ff 0%, #ffffff 60%, #e0f2fe 100%);
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px 28px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        text-align: center;
    }

    .workload-page-title {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .workload-page-subtitle {
        color: #6b7280;
        font-size: 15px;
    }

    .workload-panel {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .workload-panel-header {
        background: linear-gradient(90deg, #ede9fe 0%, #e0f2fe 100%);
        padding: 16px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .workload-panel-header h2 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }

    .workload-panel-body {
        padding: 20px;
        background: #ffffff;
    }

    .workload-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 10px 14px;
        margin-bottom: 16px;
        list-style: none;
        cursor: pointer;
        width: 100%;
    }

    .workload-toolbar-columns {
        display: flex;
        gap: 32px;
        color: #6b7280;
        font-weight: 600;
        font-size: 13px;
        flex: 1;
        justify-content: center;
    }

    .workload-select {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 600;
        color: #111827;
        font-size: 14px;
        cursor: pointer;
        list-style: none;
    }

    .workload-select::-webkit-details-marker {
        display: none;
    }

    .workload-dropdown > summary {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        margin-bottom: 16px;
    }

    .workload-dropdown > summary::-webkit-details-marker {
        display: none;
    }

    .workload-dropdown {
        border: none;
    }

    .workload-dropdown[open] > summary {
        margin-bottom: 16px;
    }

    .workload-dropdown:not([open]) > summary {
        margin-bottom: 0;
    }

    .workload-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #7c3aed;
        color: #ffffff;
        border-radius: 10px;
        padding: 8px 14px;
        font-weight: 600;
        border: none;
        font-size: 14px;
    }

    .workload-table-wrap {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    
    .workload-subtable + .workload-subtable {
        margin-top: 18px;
    }

    .workload-item-dropdown {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #f8fafc;
        margin-bottom: 16px;
        overflow: hidden;
        box-shadow: 0 6px 12px rgba(15, 23, 42, 0.06);
    }

    .workload-item-dropdown:last-child {
        margin-bottom: 0;
    }

    .workload-item-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 18px;
        cursor: pointer;
        list-style: none;
        background: #eff6ff;
    }

    .workload-item-summary::-webkit-details-marker {
        display: none;
    }

    .workload-item-summary-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    .workload-item-summary-right {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .workload-item-summary-score {
        font-size: 12px;
        font-weight: 700;
        color: #0f766e;
        background: #d1fae5;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .workload-item-summary-icon {
        width: 10px;
        height: 10px;
        border-right: 2px solid #475569;
        border-bottom: 2px solid #475569;
        transform: rotate(45deg);
        transition: transform 0.2s ease;
    }

    .workload-item-dropdown[open] .workload-item-summary-icon {
        transform: rotate(-135deg);
    }

    .workload-item-dropdown .workload-subtable {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        border-radius: 0;
        box-shadow: none;
        margin: 0;
    }

    .workload-subtable-title {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 10px;
        padding-left: 4px;
    }

    .workload-table {
        width: 100%;
        border-collapse: collapse;
    }

    .workload-table thead {
        background: #94a3b8;
        color: #ffffff;
    }

    .workload-table th,
    .workload-table td {
        padding: 12px 10px;
        font-size: 13px;
        text-align: center;
    }

    .workload-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    .workload-table td:first-child,
    .workload-table th:first-child {
        text-align: left;
        padding-left: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .workload-mini-btn {
        background: #8b5cf6;
        color: #ffffff;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 12px !important;
        min-height: 30px;
        min-width: 64px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        box-sizing: border-box;
    }

    .workload-mini-btn:hover {
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
        transform: translateY(-1px);
        filter: brightness(1.03);
    }

    .workload-mini-btn:active {
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
        transform: translateY(0);
    }

    .workload-item-score {
        color: #6b7280;
        font-weight: 600;
        margin-left: 6px;
    }

    .workload-row-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }

    .workload-delete-form {
        margin: 0;
    }

    .workload-delete-btn {
        background: #e29d9d80;
        color: #b91c1c;
    }

    .workload-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 14px;
        font-weight: 600;
        color: #111827;
    }

    .workload-total-value {
        font-size: 16px;
        color: #111827;
    }

    .workload-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .page-btn {
        border: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        color: #374151;
    }

    .page-btn.is-active {
        background: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
    }

    .page-ellipsis {
        color: #6b7280;
    }

    .workload-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .workload-list-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
    }

    .workload-list-columns {
        display: flex;
        align-items: center;
        gap: 24px;
        font-size: 13px;
        color: #6b7280;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .workload-summary-card {
        background: linear-gradient(90deg, #bbf7d0 0%, #22d3ee 100%);
        color: #0f172a;
        padding: 24px;
        border-radius: 14px;
        display: flex;
        justify-content: center;
        text-align: center;
        font-weight: 700;
    }

    .workload-summary-title {
        font-size: 18px;
        margin-bottom: 6px;
    }

    .workload-summary-value {
        font-size: 28px;
    }

    .workload-actions {
        display: flex;
        justify-content: center;
        gap: 16px;
        padding-bottom: 10px;
    }

    .workload-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        background: #e5e7eb;
        color: #111827;
        text-decoration: none;
        font-weight: 600;
    }

    .workload-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        background: #7c3aed;
        color: #ffffff;
        font-weight: 600;
        border: none;
    }
    .workload-modal-content {
        border-radius: 18px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
    }

    .workload-modal-header {
        background: linear-gradient(90deg, #ede9fe 0%, #e0f2fe 100%);
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 20px;
    }

    .workload-modal-header .modal-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .workload-modal-body {
        padding: 18px 22px 10px;
        background: #ffffff;
    }

    .workload-modal-section {
        background: #f5f3ff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .workload-modal-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .workload-modal-hint {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    .workload-modal-select,
    .workload-modal-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        background: #ffffff;
    }

    .workload-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 16px;
        margin-top: 8px;
        grid-template-areas:
            "workload link"
            "item link"
            "group note"
            "credit note"
            "instructors note";
    }

    .workload-modal-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .workload-modal-subfields {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .workload-modal-sub-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .workload-link-btn {
        align-self: flex-start;
        margin-top: 4px;
        background: #e0f2fe;
        color: #1d4ed8;
        border: none;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .workload-evidence-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    #workload-evidence-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .workload-evidence-remove-btn {
        background: #fee2e2;
        color: #b91c1c;
        border: none;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .workload-modal-note {
        grid-column: span 1;
        background: #ecfdf3;
        border: 1px solid #86efac;
        border-radius: 12px;
        padding: 10px 12px;
        color: #166534;
        font-size: 12px;
    }

    .workload-modal-field-workload {
        grid-area: workload;
    }

    .workload-modal-field-item {
        grid-area: item;
    }

    .workload-modal-field-link {
        grid-area: link;
    }

    .workload-modal-field-group {
        grid-area: group;
    }

    .workload-modal-field-credit {
        grid-area: credit;
    }

    .workload-modal-field-instructors {
        grid-area: instructors;
    }

    .workload-modal-note-box {
        grid-area: note;
    }

    .workload-modal-note h6 {
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .workload-modal-note ul {
        margin: 0;
        padding-left: 16px;
    }

    .workload-alert-box {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        background: #fffbeb;
        border: 1px solid #fbbf24;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 12px;
        color: #92400e;
    }

    .workload-alert-icon {
        color: #f59e0b;
        margin-top: 2px;
    }

    .workload-alert-link {
        color: #4f46e5;
        font-weight: 600;
        text-decoration: none;
    }

    .workload-modal-footer {
        padding: 12px 18px 16px;
        border-top: 1px solid #e5e7eb;
        justify-content: flex-end;
        gap: 10px;
    }

    #subjectModal {
        z-index: 1065;
    }

    #subjectModal .subject-modal-dialog {
        margin-top: 450px;
    }

    .workload-backdrop-inert {
        pointer-events: none;
    }

    .workload-cancel-btn {
        background: #e5e7eb;
        color: #111827;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
    }

    .required {
        color: #ef4444;
    }

    @media (max-width: 1024px) {
        .workload-toolbar,
        .workload-list-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .workload-toolbar-columns,
        .workload-list-columns {
            gap: 12px;
        }
    }

    @media (max-width: 768px) {
        .workload-modal-grid {
            grid-template-columns: 1fr;
            grid-template-areas:
                "workload"
                "item"
                "link"
                "group"
                "credit"
                "instructors"
                "note";
        }

        .workload-modal-note {
            grid-column: span 1;
        }
    }
</style>
    <x-delete-warning-modal
        text="รายการภาระงาน"
        formAction="{{ route('evaluatee.workload-entries.destroy', ':id') }}"
    />
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formSelect = document.querySelector('select[name="workload_form_id"]');
    const formIdField = document.getElementById('workloadFormIdField');
    const itemSelect = document.getElementById('workloadFormItemSelect');
    const hiddenItemField = document.getElementById('selectedItemField');
    const formFieldBlocks = document.querySelectorAll('.workload-form-fields');
    const workloadModalEl = document.getElementById('workloadAddModal');
    const workloadForm = document.getElementById('workloadEntryForm');
    const methodField = document.getElementById('workloadFormMethod');
    const modalTitle = document.getElementById('workloadAddModalLabel');
    const subjectSelect = document.querySelector('select[name="subject_id"]');
    const evidenceContainer = document.getElementById('workload-evidence-links');
    const detailFieldsContainer = document.getElementById('workload-detail-fields');
    const groupLabelInput = document.getElementById('workloadGroupLabel');
    let lastDefaultFormId = '';
    let lastDefaultGroupId = '';
    let lastDefaultGroupName = '';
    let activeGroupId = '';
    let pendingEditPayload = null;

    document.addEventListener('click', function (event) {
        const btn = event.target.closest && event.target.closest('.workload-add-btn');
        if (!btn) {
            return;
        }
        lastDefaultFormId = btn.dataset.defaultFormId || '';
        lastDefaultGroupId = btn.dataset.groupId || '';
        lastDefaultGroupName = btn.dataset.groupName || '';
    });

    if (itemSelect) {
        const placeholderOption = Array.from(itemSelect.options).find(function (opt) {
            return !opt.value;
        });
        if (placeholderOption) {
            itemSelect.insertBefore(placeholderOption, itemSelect.firstChild);
        }
    }

    function getActiveFormId() {
        if (formSelect) {
            return formSelect.value;
        }
        if (formIdField) {
            return formIdField.value;
        }
        return '';
    }

    function setActiveFormId(formId) {
        if (formSelect) {
            formSelect.value = formId;
        }
        if (formIdField) {
            formIdField.value = formId;
        }
    }

    function setActiveGroup(groupId, groupName) {
        activeGroupId = groupId || '';
        if (groupLabelInput) {
            groupLabelInput.value = groupName || (activeGroupId ? 'หมวดย่อย #' + activeGroupId : '-');
        }
    }

    function updateFormFields() {
        const formId = getActiveFormId();
        formFieldBlocks.forEach(function (block) {
            const isActive = block.dataset.formId === formId;
            block.style.display = isActive ? 'block' : 'none';
            block.querySelectorAll('input, select, textarea').forEach(function (input) {
                input.disabled = !isActive;
            });
        });

        if (!itemSelect) {
            return;
        }

        const options = Array.from(itemSelect.options);
        options.forEach(function (opt) {
            if (!opt.value) {
                opt.hidden = false;
                opt.disabled = false;
                return;
            }
            const matchForm = !formId || opt.dataset.formId === formId;
            const matchGroup = !activeGroupId || opt.dataset.groupId === activeGroupId;
            const match = matchForm && matchGroup;
            opt.hidden = !match;
            opt.disabled = !match;
        });

        if (itemSelect.value) {
            const selected = itemSelect.selectedOptions[0];
            if (selected && (!selected.dataset.formId || selected.dataset.formId !== formId || (activeGroupId && selected.dataset.groupId !== activeGroupId))) {
                itemSelect.value = '';
            }
        }
        updateSelectedItemField();
        updateCreditsFromSubject();
    }

    function updateSelectedItemField() {
        if (!hiddenItemField) {
            return;
        }
        if (!itemSelect || !itemSelect.value) {
            hiddenItemField.removeAttribute('name');
            hiddenItemField.value = '';
            return;
        }
        const selected = itemSelect.selectedOptions[0];
        if (!selected) {
            hiddenItemField.removeAttribute('name');
            hiddenItemField.value = '';
            return;
        }
        const variableName = selected.dataset.variableName || (selected.dataset.sequence ? 'item_' + selected.dataset.sequence : '');
        if (!variableName) {
            hiddenItemField.removeAttribute('name');
            hiddenItemField.value = '';
            return;
        }
        hiddenItemField.setAttribute('name', 'field_values[' + variableName + ']');
        hiddenItemField.value = selected.dataset.score || '';
    }

    function getActiveCreditsInput() {
        const formId = getActiveFormId();
        let scope = null;
        if (detailFieldsContainer && formId) {
            scope = detailFieldsContainer.querySelector('.workload-form-fields[data-form-id="' + formId + '"]');
        }
        const container = scope || document;
        const explicit = Array.from(container.querySelectorAll('input[name="field_values[credits]"]'));
        const byName = Array.from(container.querySelectorAll('input[name^="field_values["]')).filter(function (input) {
            return /credit/i.test(input.getAttribute('name'));
        });
        const byLabel = Array.from(container.querySelectorAll('.workload-modal-subfield')).map(function (field) {
            const label = field.querySelector('.workload-modal-sub-label');
            if (!label) {
                return null;
            }
            const text = (label.textContent || '').trim();
            if (!text.includes('หน่วยกิต')) {
                return null;
            }
            return field.querySelector('input');
        }).filter(Boolean);
        const candidates = explicit.concat(byName, byLabel);
        return candidates.find(function (input) {
            return !input.disabled;
        }) || candidates[0] || null;
    }

    function updateCreditsFromSubject() {
        if (!subjectSelect) {
            return;
        }
        const selected = subjectSelect.selectedOptions ? subjectSelect.selectedOptions[0] : null;
        if (!selected) {
            return;
        }
        const credits = selected.dataset ? selected.dataset.credits : '';
        if (credits === undefined || credits === null || credits === '') {
            return;
        }
        const creditsInput = getActiveCreditsInput();
        if (!creditsInput) {
            return;
        }
        const wasAuto = creditsInput.dataset ? creditsInput.dataset.autofill === 'true' : false;
        const isEmpty = creditsInput.value === '' || creditsInput.value === '1';
        if (isEmpty || wasAuto) {
            creditsInput.value = credits;
            if (creditsInput.dataset) {
                creditsInput.dataset.autofill = 'true';
            }
        }
    }

    function setFormMode(mode, entryId) {
        if (!workloadForm) {
            return;
        }
        const storeUrl = workloadForm.dataset.storeUrl || workloadForm.getAttribute('action');
        const updateUrlTemplate = workloadForm.dataset.updateUrl || '';

        if (mode === 'edit' && entryId) {
            if (updateUrlTemplate) {
                workloadForm.setAttribute('action', updateUrlTemplate.replace('__id__', entryId));
            }
            if (methodField) {
                methodField.value = 'PUT';
                methodField.setAttribute('name', '_method');
            }
            if (modalTitle) {
                modalTitle.textContent = 'แก้ไขข้อมูลภาระงาน';
            }
            return;
        }

        workloadForm.setAttribute('action', storeUrl);
        if (methodField) {
            methodField.value = '';
            methodField.removeAttribute('name');
        }
        if (modalTitle) {
            modalTitle.textContent = 'เพิ่มข้อมูลภาระงาน';
        }
    }

    function fillFields(values, formId, clearAll) {
        const container = detailFieldsContainer || workloadForm;
        if (!container) {
            return;
        }
        const scope = formId
            ? container.querySelectorAll('.workload-form-fields[data-form-id="' + formId + '"]')
            : container.querySelectorAll('.workload-form-fields');
        const blocks = scope.length ? Array.from(scope) : [container];
        const normalized = {};
        if (values && typeof values === 'object') {
            Object.keys(values).forEach(function (key) {
                const raw = String(key);
                const keys = [raw, raw.toLowerCase(), raw.trim(), raw.trim().replace(/\s+/g, '')];
                keys.forEach(function (k) {
                    normalized[k] = values[key];
                });
            });
        }
        const escapeCss = function (value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(value);
            }
            return value.replace(/([\\!"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~])/g, '\\$1');
        };
        blocks.forEach(function (block) {
            const inputs = Array.from(block.querySelectorAll('input[name^="field_values["]')).filter(function (input) {
                return input.id !== 'selectedItemField';
            });
            if (clearAll) {
                inputs.forEach(function (input) {
                    input.value = '';
                });
            }
            inputs.forEach(function (input) {
                const match = input.getAttribute('name').match(/^field_values\\[(.+)\\]$/);
                const key = match ? String(match[1]) : '';
                if (key && Object.prototype.hasOwnProperty.call(normalized, key)) {
                    input.value = normalized[key];
                }
            });
            if (values && typeof values === 'object') {
                Object.keys(values).forEach(function (key) {
                    const selector = 'input[name="' + escapeCss('field_values[' + key + ']') + '"]';
                    block.querySelectorAll(selector).forEach(function (input) {
                        input.value = values[key];
                    });
                });
            }
        });
    }

    function setEvidenceLinks(links) {
        if (!evidenceContainer) {
            return;
        }
        evidenceContainer.innerHTML = '';
        const values = Array.isArray(links) && links.length ? links : [''];
        values.forEach(function (link) {
            const row = document.createElement('div');
            row.className = 'workload-evidence-row';
            row.innerHTML = `
                <input type="text" class="workload-modal-input" name="evidence_links[]" placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้" />
                <button type="button" class="workload-evidence-remove-btn" title="ลบลิงก์">ลบ</button>
            `;
            const input = row.querySelector('input');
            if (input) {
                input.value = link || '';
            }
            evidenceContainer.appendChild(row);
        });

        const rows = evidenceContainer.querySelectorAll('.workload-evidence-row');
        rows.forEach(function (row) {
            const removeBtn = row.querySelector('.workload-evidence-remove-btn');
            if (removeBtn) {
                removeBtn.disabled = rows.length === 1;
            }
        });
    }

    function parseDatasetJson(value) {
        if (!value) {
            return null;
        }
        let rawValue = value;
        if (typeof rawValue === 'string') {
            rawValue = rawValue.trim();
            if ((rawValue.startsWith("'") && rawValue.endsWith("'")) || (rawValue.startsWith('"') && rawValue.endsWith('"'))) {
                rawValue = rawValue.slice(1, -1);
            }
        }
        try {
            return JSON.parse(rawValue);
        } catch (error) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = rawValue;
            const decoded = textarea.value;
            let cleaned = decoded.trim();
            if ((cleaned.startsWith("'") && cleaned.endsWith("'")) || (cleaned.startsWith('"') && cleaned.endsWith('"'))) {
                cleaned = cleaned.slice(1, -1);
            }
            try {
                return JSON.parse(cleaned);
            } catch (innerError) {
                return null;
            }
        }
    }

    function selectDefaultForForm(formId) {
        if (formId) {
            setActiveFormId(formId);
        } else if (formSelect && !formSelect.value) {
            const firstForm = Array.from(formSelect.options).find(function (opt) {
                return opt.value && !opt.disabled;
            });
            if (firstForm) {
                setActiveFormId(firstForm.value);
            }
        } else if (!formSelect && itemSelect) {
            const firstItem = Array.from(itemSelect.options).find(function (opt) {
                return opt.value && opt.dataset.formId;
            });
            if (firstItem) {
                setActiveFormId(firstItem.dataset.formId);
            }
        }

        if (itemSelect) {
            itemSelect.value = '';
        }

        updateFormFields();
    }

    if (itemSelect) {
        itemSelect.addEventListener('change', function () {
            const selected = itemSelect.selectedOptions[0];
            if (selected && selected.dataset.formId) {
                setActiveFormId(selected.dataset.formId);
            }
            if (selected && selected.dataset.groupId) {
                setActiveGroup(selected.dataset.groupId, groupLabelInput ? groupLabelInput.value : '');
            }
            updateFormFields();
        });
    }

    if (subjectSelect) {
        subjectSelect.addEventListener('change', function () {
            updateCreditsFromSubject();
        });
    }

    if (detailFieldsContainer) {
        detailFieldsContainer.addEventListener('input', function (event) {
            const target = event.target;
            if (!target || target.tagName !== 'INPUT') {
                return;
            }
            const name = target.getAttribute('name') || '';
            if (/credit/i.test(name) || (target.closest('.workload-modal-subfield')?.querySelector('.workload-modal-sub-label')?.textContent || '').includes('หน่วยกิต')) {
                if (target.dataset) {
                    target.dataset.autofill = 'false';
                }
            }
        });
    }

    if (formSelect) {
        formSelect.addEventListener('change', function () {
            setActiveFormId(formSelect.value);
            updateFormFields();
        });
    }

    if (workloadModalEl) {
        workloadModalEl.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const isEdit = trigger && trigger.classList && trigger.classList.contains('workload-edit-btn');
            if (isEdit) {
                const entryId = trigger.dataset.entryId;
                const formId = trigger.dataset.formId || '';
                const itemId = trigger.dataset.itemId || '';
                const subjectId = trigger.dataset.subjectId || '';
                const groupId = trigger.dataset.groupId || '';
                const groupName = trigger.dataset.groupName || '';
                const fieldValues = parseDatasetJson(trigger.dataset.fieldValues) || {};
                const evidenceLinks = parseDatasetJson(trigger.dataset.evidenceLinks) || [];

                setFormMode('edit', entryId);
                setActiveGroup(groupId, groupName);
                if (subjectSelect) {
                    subjectSelect.value = subjectId;
                }
                setActiveFormId(formId);
                updateFormFields();
                if (itemSelect && itemId) {
                    itemSelect.value = itemId;
                }
                updateSelectedItemField();
                fillFields(fieldValues, formId, true);
                setEvidenceLinks(evidenceLinks);
                pendingEditPayload = { fieldValues: fieldValues, evidenceLinks: evidenceLinks, formId: formId };
                return;
            }

            setFormMode('create');
            if (workloadForm) {
                workloadForm.reset();
            }
            if (subjectSelect) {
                subjectSelect.value = '';
            }
            fillFields({}, '', true);
            setEvidenceLinks([]);
            pendingEditPayload = null;
            const defaultFormId = trigger && trigger.dataset ? trigger.dataset.defaultFormId : (lastDefaultFormId || '');
            const defaultGroupId = trigger && trigger.dataset ? (trigger.dataset.groupId || '') : (lastDefaultGroupId || '');
            const defaultGroupName = trigger && trigger.dataset ? (trigger.dataset.groupName || '') : (lastDefaultGroupName || '');
            setActiveGroup(defaultGroupId, defaultGroupName);
            selectDefaultForForm(defaultFormId || '');
            const presetItemId = trigger && trigger.dataset ? (trigger.dataset.itemId || '') : '';
            if (itemSelect && presetItemId) {
                itemSelect.value = presetItemId;
                const selected = itemSelect.selectedOptions[0];
                if (selected && selected.dataset.formId) {
                    setActiveFormId(selected.dataset.formId);
                }
                updateFormFields();
                itemSelect.value = presetItemId;
                updateSelectedItemField();
            }
        });

        workloadModalEl.addEventListener('shown.bs.modal', function () {
            if (!pendingEditPayload) {
                return;
            }
            fillFields(pendingEditPayload.fieldValues || {}, pendingEditPayload.formId, true);
            setEvidenceLinks(pendingEditPayload.evidenceLinks || []);
        });
    }

    updateFormFields();
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subjectModalEl = document.getElementById('subjectModal');
        const openLinks = document.querySelectorAll('.workload-open-subject-modal');

        if (!subjectModalEl || openLinks.length === 0 || typeof bootstrap === 'undefined') {
            return;
        }

        const subjectModal = bootstrap.Modal.getOrCreateInstance(subjectModalEl, {
            backdrop: false,
            focus: false,
        });

        function setBackdropInert(isInert) {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function (backdrop) {
                if (isInert) {
                    backdrop.classList.add('workload-backdrop-inert');
                } else {
                    backdrop.classList.remove('workload-backdrop-inert');
                }
            });
        }

        subjectModalEl.addEventListener('show.bs.modal', function () {
            setBackdropInert(true);
        });

        subjectModalEl.addEventListener('hidden.bs.modal', function () {
            setBackdropInert(false);
        });

        openLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const form = document.getElementById('subjectForm');
                if (form) {
                    form.action = "{{ route('subjects.store.evaluatee') }}?redirect_to=" + encodeURIComponent(window.location.href);
                    const methodInput = document.getElementById('form_method');
                    if (methodInput) {
                        methodInput.value = 'POST';
                    }
                    const redirectInput = document.getElementById('subjectRedirectTo');
                    if (redirectInput) {
                        redirectInput.value = window.location.href;
                    }
                }
                subjectModal.show();
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.submitForm === 'function') {
            return;
        }

        function validateSubjectForm() {
            const codeInput = document.getElementById('code');
            const nameThInput = document.getElementById('name_th');
            const creditsInput = document.getElementById('credits');

            if (!codeInput || !nameThInput || !creditsInput) {
                return true;
            }

            const codeValue = codeInput.value.trim();
            const nameThValue = nameThInput.value.trim();
            const creditsValue = creditsInput.value.trim();

            let isValid = true;

            if (codeValue === '') {
                codeInput.classList.add('is-invalid');
                const codeError = document.getElementById('codeError');
                if (codeError) {
                    codeError.style.display = 'block';
                }
                isValid = false;
            } else {
                codeInput.classList.remove('is-invalid');
                const codeError = document.getElementById('codeError');
                if (codeError) {
                    codeError.style.display = 'none';
                }
            }

            if (nameThValue === '') {
                nameThInput.classList.add('is-invalid');
                const nameThError = document.getElementById('nameThError');
                if (nameThError) {
                    nameThError.style.display = 'block';
                }
                isValid = false;
            } else {
                nameThInput.classList.remove('is-invalid');
                const nameThError = document.getElementById('nameThError');
                if (nameThError) {
                    nameThError.style.display = 'none';
                }
            }

            if (creditsValue === '' || Number.isNaN(Number(creditsValue))) {
                creditsInput.classList.add('is-invalid');
                const creditsError = document.getElementById('creditsError');
                if (creditsError) {
                    creditsError.style.display = 'block';
                }
                isValid = false;
            } else {
                creditsInput.classList.remove('is-invalid');
                const creditsError = document.getElementById('creditsError');
                if (creditsError) {
                    creditsError.style.display = 'none';
                }
            }

            return isValid;
        }

        window.submitForm = function () {
            const form = document.getElementById('subjectForm');
            if (!form) return;

            const redirectInput = document.getElementById('subjectRedirectTo');
            if (redirectInput) {
                redirectInput.value = window.location.href;
            }
            form.action = "{{ route('subjects.store.evaluatee') }}?redirect_to=" + encodeURIComponent(window.location.href);

            if (!validateSubjectForm()) {
                return;
            }

            form.submit();
        };
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('workload-evidence-links');
        const addBtn = document.getElementById('workload-add-evidence-link');

        if (!container || !addBtn) {
            return;
        }

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.workload-evidence-row');
            rows.forEach(function (row) {
                const removeBtn = row.querySelector('.workload-evidence-remove-btn');
                if (removeBtn) {
                    removeBtn.disabled = rows.length === 1;
                }
            });
        }

        addBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'workload-evidence-row';
            row.innerHTML = `
                <input type="text" class="workload-modal-input" name="evidence_links[]" placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้" />
                <button type="button" class="workload-evidence-remove-btn" title="ลบลิงก์">ลบ</button>
            `;
            container.appendChild(row);
            updateRemoveButtons();
        });

        container.addEventListener('click', function (event) {
            const btn = event.target.closest('.workload-evidence-remove-btn');
            if (!btn) {
                return;
            }
            const rows = container.querySelectorAll('.workload-evidence-row');
            if (rows.length <= 1) {
                return;
            }
            const row = btn.closest('.workload-evidence-row');
            if (row) {
                row.remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons();
    });
</script>
<script>
    // Auto-hide success/error messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
        messages.forEach(function(message) {
            setTimeout(function() {
                if (message.parentElement) {
                    message.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        if (message.parentElement) {
                            message.remove();
                        }
                    }, 300);
                }
            }, 5000);
        });
    });
</script>
@endsection











































