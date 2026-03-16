@props([
    'categoryItems' => [],
    'readonly' => false,
    'evidenceMap' => [],
    'qualityEvidenceMap' => [],
    'report' => null,
    'workloadMap' => [],
])

@php
    $qualityMaxScore = 0;
    foreach ($categoryItems as $category) {
        foreach ($category['evaluation_lists'] as $evalList) {
            if (!empty($evalList['quality_items'])) {
                $qualityMaxScore += floatval($evalList['sum_score'] ?? 0);
            }
        }
    }
@endphp

<input type="hidden" id="quality-max-score" value="{{ $qualityMaxScore }}">
<input type="hidden" id="quality-readonly" value="{{ $readonly ? 1 : 0 }}">

{{-- บล็อกเนื้อหา --}}
<div class="space-y-8">
    @foreach ($categoryItems as $category)
        {{-- Category Container --}}
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
            {{-- Category Header --}}
            <div class="bg-purple-100 px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $category['main_categories'] }}
                </h1>
                @if (!empty($category['sub_categories']))
                    <p class="text-base text-gray-600 mt-1">{{ $category['sub_categories'] }}</p>
                @endif
            </div>

            <div class="p-6">
                @foreach ($category['evaluation_lists'] as $evaluationList)
                    {{-- Evaluation List Container --}}
                    <div class="mb-8 bg-gray-50 rounded-lg border border-gray-300">
                        {{-- Evaluation List Header --}}
                        <div
                            class="bg-gradient-to-r from-purple-100 to-blue-100 px-6 py-4 rounded-t-lg border-b border-gray-200">
                            <div class="flex items-center space-x-3">
                                <h2 class="text-xl font-bold text-gray-800">
                                    {{ $evaluationList['name'] }}
                                </h2>
                                @php
                                    $listSelectedQualitySum = 0;
                                    if (!empty($evaluationList['quality_items'])) {
                                        foreach ($evaluationList['quality_items'] as $mainCriteria) {
                                            foreach ($mainCriteria['sub_criterias'] as $subCriteria) {
                                                $hasScore =
                                                    isset($subCriteria['score']) &&
                                                    $subCriteria['score'] !== '' &&
                                                    $subCriteria['score'] !== null;
                                                $isSelected = $hasScore || ($subCriteria['user_selected'] ?? false);
                                                if ($isSelected) {
                                                    $listSelectedQualitySum += $hasScore
                                                        ? floatval($subCriteria['score'])
                                                        : floatval($subCriteria['num_score'] ?? 0);
                                                }
                                            }
                                        }
                                    }
                                    $listMaxScore = floatval($evaluationList['sum_score'] ?? 0);
                                    if ($listMaxScore > 0 && $listSelectedQualitySum > $listMaxScore) {
                                        $listSelectedQualitySum = $listMaxScore;
                                    }
                                @endphp
                                @if (!empty($evaluationList['quality_items']))
                                    @if (isset($evaluationList['sum_score']))
                                        <span
                                            class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                                            คะแนน{{ $evaluationList['sum_score'] }}
                                        </span>
                                    @endif
                                    @php
                                        $showSelectedQualitySum =
                                            $readonly ||
                                            (isset($report->status) && $report->status === 'Completed');
                                    @endphp
                                    @if ($showSelectedQualitySum)
                                        <span
                                            class="inline-block bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-1 rounded-full">
                                            คะแนนที่ได้
                                            {{ number_format($listSelectedQualitySum, 2) }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                            @if (!empty($evaluationList['annotation']))
                                <p class="text-sm text-gray-600 mt-1">{{ $evaluationList['annotation'] }}</p>
                            @endif
                        </div>

                        <div class="p-6">
                            {{-- Quantity Section --}}
                            @if (count($evaluationList['quantity_items']) > 0)
                                <div class="mb-8">
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                        <h3 class="text-lg font-semibold text-green-800 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                </path>
                                            </svg>
                                            ด้านปริมาณ
                                        </h3>
                                    </div>

                                    @foreach ($evaluationList['quantity_items'] as $mainCriteria)
                                        {{-- Main Criteria Header --}}
                                        <div class="mb-4 border-l-4 border-green-400 pl-4 py-2 bg-green-50">
                                            <h4 class="text-base font-semibold text-gray-800">
                                                {{ $mainCriteria['name'] }}
                                            </h4>
                                            @if (!empty($mainCriteria['tooltips']))
                                                <div class="text-sm text-gray-500 mt-1">{!! $mainCriteria['tooltips'] !!}</div>
                                            @endif
                                        </div>

                                        @if (!empty($mainCriteria['formulas']) && count($mainCriteria['formulas']) > 0)
                                            <div class="ml-6 mb-6">
                                                <div class="p-4 bg-green-50 rounded-xl shadow-sm">
                                                    <h4
                                                        class="text-lg font-semibold text-green-700 mb-3 flex items-center">
                                                        {{-- <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 17v-2a4 4 0 014-4h6M9 13H5v6h4v-2a4 4 0 014-4z"/>
                                                        </svg> --}}
                                                        สูตรการคำนวณ
                                                    </h4>

                                                    <div class="space-y-3">
                                                        @foreach ($mainCriteria['formulas'] as $formula)
                                                            @if (!empty($formula['condition']))
                                                                <div
                                                                    class="p-4 bg-white border border-green-200 rounded-lg text-center">
                                                                    <span
                                                                        class="text-sm md:text-lg font-medium text-gray-800 block">
                                                                        {{ $formula['condition'] }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Quantity Sub Criteria --}}
                                        <div class="space-y-3 ml-6 mb-6">
                                            @foreach (collect($mainCriteria['sub_criterias'])->sortBy('sequence') as $subCriteria)
                                                <details class="group border border-gray-300 rounded-lg bg-white">
                                                    <summary
                                                        class="flex items-center justify-between gap-4 px-4 py-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                                        <div class="min-w-0 flex items-center gap-3 flex-wrap">
                                                            <span class="text-sm font-semibold text-gray-800">
                                                                {{ $subCriteria['name'] }}
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('evaluatee.workload', ['report_id' => $report?->id, 'quantity_sub_criteria_id' => $subCriteria['id']]) }}"
                                                            class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-white bg-purple-600 rounded-md hover:bg-purple-700 transition">
                                                            จัดการข้อมูล
                                                        </a>
                                                    </summary>
                                                    @if (!$readonly)
                                                        <input type="hidden"
                                                            name="quantity_list[{{ $subCriteria['id'] }}][quantity_sub_criteria_id]"
                                                            value="{{ $subCriteria['id'] }}">
                                                        <input type="hidden"
                                                            name="quantity_list[{{ $subCriteria['id'] }}][score_C]"
                                                            value="{{ $subCriteria['tor_compliant'] ?? '' }}">
                                                        <input type="hidden"
                                                            name="quantity_list[{{ $subCriteria['id'] }}][description]"
                                                            value="{{ $subCriteria['score_description'] ?? '' }}">
                                                    @endif
                                                    @php
                                                        $workloadData = $workloadMap[$subCriteria['id']] ?? null;
                                                        $workloadSubCriteria = $workloadData['subCriteria'] ?? null;
                                                        $workloadForms = $workloadData['workloadForms'] ?? collect();
                                                        $workloadEntriesByFormId =
                                                            $workloadData['workloadEntriesByFormId'] ?? collect();
                                                        $evidenceLinksByEntryId =
                                                            $workloadData['evidenceLinksByEntryId'] ?? collect();
                                                    @endphp
                                                    <div class="border-t border-gray-200 px-4 py-4 bg-gray-50">
                                                        <x-workload-summary :sub-criteria="$workloadSubCriteria" :workload-forms="$workloadForms"
                                                            :workload-entries-by-form-id="$workloadEntriesByFormId" :evidence-links-by-entry-id="$evidenceLinksByEntryId" />
                                                    </div>
                                                </details>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Quality Section --}}
                            @if (count($evaluationList['quality_items']) > 0)
                                @php
                                    $qualityMainTotal = count($evaluationList['quality_items']);
                                    $qualityMainChecked = 0;
                                    foreach ($evaluationList['quality_items'] as $qualityMain) {
                                        $sorted = collect($qualityMain['sub_criterias'])->sortBy('sequence')->values();
                                        $hasAnyChecked = $sorted->contains(function ($sub) {
                                            $hasScore =
                                                !empty($sub['score']) && $sub['score'] !== '' && $sub['score'] !== null;
                                            return $hasScore || ($sub['user_selected'] ?? false);
                                        });
                                        if ($hasAnyChecked) {
                                            $qualityMainChecked++;
                                        }
                                    }
                                @endphp
                                <details class="group mb-8">
                                    <summary class="list-none [&::-webkit-details-marker]:hidden cursor-pointer">
                                        <div
                                            class="bg-purple-50 border border-purple-200 rounded-lg p-4 flex items-center justify-between">
                                            <div class="flex items-center gap-3 flex-wrap">
                                                <h3 class="text-lg font-semibold text-purple-800 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                                                        </path>
                                                    </svg>
                                                    ด้านคุณภาพ
                                                </h3>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $qualityMainTotal > 0 && $qualityMainChecked === $qualityMainTotal ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    เช็กแล้ว {{ $qualityMainChecked }}/{{ $qualityMainTotal }}
                                                </span>
                                            </div>
                                            <svg class="w-5 h-5 text-purple-600 chevron-up" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                            <svg class="w-5 h-5 text-purple-600 chevron-down" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </summary>

                                    <div class="mt-6">
                                        @foreach ($evaluationList['quality_items'] as $mainCriteria)
                                            @php
                                                $mainSorted = collect($mainCriteria['sub_criterias'])
                                                    ->sortBy('sequence')
                                                    ->values();
                                                $mainHasChecked = $mainSorted->contains(function ($sub) {
                                                    $hasScore =
                                                        !empty($sub['score']) &&
                                                        $sub['score'] !== '' &&
                                                        $sub['score'] !== null;
                                                    return $hasScore || ($sub['user_selected'] ?? false);
                                                });
                                            @endphp
                                            <details class="group border border-gray-300 rounded-lg bg-white mb-6">
                                                <summary
                                                    class="flex items-center justify-between gap-4 px-4 py-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden bg-purple-50">
                                                    <div class="min-w-0 flex items-center gap-3 flex-wrap">
                                                        <h4 class="text-base font-semibold text-gray-800">
                                                            {{ $mainCriteria['name'] }}
                                                        </h4>
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $mainHasChecked ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                                            {{ $mainHasChecked ? 'มีการเลือกแล้ว' : 'ยังไม่เลือก' }}
                                                        </span>
                                                    </div>
                                                    <svg class="w-5 h-5 text-purple-600 chevron-up" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                    <svg class="w-5 h-5 text-purple-600 chevron-down" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </summary>

                                                <div class="px-4 py-4">
                                                    @if (!empty($mainCriteria['tooltips']))
                                                        <div class="text-sm text-gray-500 mb-3">{!! $mainCriteria['tooltips'] !!}
                                                        </div>
                                                    @endif

                                                    {{-- Quality Sub Criteria --}}
                                                    <div class="space-y-3 ml-2">
                                                        @foreach (collect($mainCriteria['sub_criterias'])->sortBy('sequence') as $subCriteria)
                                                            @php
                                                                $hasScore =
                                                                    !empty($subCriteria['score']) &&
                                                                    $subCriteria['score'] !== '' &&
                                                                    $subCriteria['score'] !== null;
                                                                $shouldBeChecked =
                                                                    $hasScore ||
                                                                    ($subCriteria['user_selected'] ?? false);
                                                            @endphp

                                                            <div
                                                                class="p-4 bg-white border border-gray-200 rounded-lg">
                                                                <div
                                                                    class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6">
                                                                    {{-- Checkbox and Label --}}
                                                                    <div class="flex items-center flex-1 min-w-0">
                                                                        @if (!$readonly)
                                                                            <input type="checkbox"
                                                                                name="quality_criteria[{{ $subCriteria['id'] }}]"
                                                                                value="1"
                                                                                data-score="{{ $subCriteria['num_score'] ?? 0 }}"
                                                                                data-sub-criteria-id="{{ $subCriteria['id'] }}"
                                                                                onchange="handleQualityCheckboxChange(this)"
                                                                                {{ $shouldBeChecked ? 'checked' : '' }}
                                                                                class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded mr-3">
                                                                            <label
                                                                                class="text-base text-gray-800 break-words">
                                                                                {{ $subCriteria['name'] }}
                                                                                @if (!empty($subCriteria['description']))
                                                                                    <div
                                                                                        class="text-sm text-gray-500 mt-1">
                                                                                        {!! $subCriteria['description'] !!}
                                                                                    </div>
                                                                                @endif
                                                                            </label>
                                                                        @else
                                                                            <input type="checkbox"
                                                                                {{ $shouldBeChecked ? 'checked' : '' }}
                                                                                disabled
                                                                                class="h-5 w-5 text-purple-600 border-gray-300 rounded mr-3">
                                                                            <span
                                                                                class="text-base text-gray-800 break-words">
                                                                                {{ $subCriteria['name'] }}
                                                                                @if (!empty($subCriteria['description']))
                                                                                    <div
                                                                                        class="text-sm text-gray-500 mt-1">
                                                                                        {!! $subCriteria['description'] !!}
                                                                                    </div>
                                                                                @endif
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    {{-- Score (readonly, only when completed) --}}
                                                                    @if ($readonly && isset($report->status) && $report->status === 'Completed')
                                                                        {{-- <div class="w-full md:w-60">
                                                                            <div
                                                                                class="text-base text-gray-800 p-2 rounded border text-center">
                                                                                {{ $subCriteria['score'] ?: '0.00' }}
                                                                            </div>
                                                                        </div> --}}
                                                                    @endif
                                                                    {{-- Hidden Score Input for edit mode --}}
                                                                    @if (!$readonly)
                                                                        <input type="hidden"
                                                                            name="quality_list[{{ $subCriteria['id'] }}][quality_sub_criteria_id]"
                                                                            value="{{ $subCriteria['id'] }}">
                                                                        <input type="hidden"
                                                                            name="quality_list[{{ $subCriteria['id'] }}][evaluation_list_id]"
                                                                            value="{{ $evaluationList['id'] }}">
                                                                        <input type="hidden"
                                                                            id="quality-score-{{ $subCriteria['id'] }}"
                                                                            name="quality_list[{{ $subCriteria['id'] }}][score]"
                                                                            value="{{ $hasScore ? $subCriteria['score'] : ($shouldBeChecked ? $subCriteria['num_score'] : '') }}"
                                                                            data-evaluation-list-id="{{ $evaluationList['id'] }}"
                                                                            data-list-max="{{ $evaluationList['sum_score'] ?? 0 }}">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                        @if ($readonly)
                                                            {{-- บล็อกเนื้อหา --}}
                                                            <div
                                                                class="mt-5 p-6 bg-blue-50 rounded-xl border border-blue-500 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                                                <span
                                                                    class="text-lg font-semibold text-blue-700">คะแนนที่ได้</span>
                                                                <span
                                                                    class="text-lg font-semibold text-blue-900">{{ number_format($mainCriteria['main_calculated_score'] ?? 0, 2) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Evidence Section for Quality Main Criteria --}}
                                                    <div class="mt-5 border-t border-gray-200 pt-4">
                                                        @php
                                                            $links =
                                                                isset($qualityEvidenceMap[$mainCriteria['id']]) &&
                                                                is_array($qualityEvidenceMap[$mainCriteria['id']])
                                                                    ? $qualityEvidenceMap[$mainCriteria['id']]
                                                                    : (isset($qualityEvidenceMap[$mainCriteria['id']])
                                                                        ? [$qualityEvidenceMap[$mainCriteria['id']]]
                                                                        : ['']);

                                                            if (
                                                                empty($links) ||
                                                                (count($links) === 1 && empty($links[0]))
                                                            ) {
                                                                $links = [''];
                                                            }
                                                        @endphp

                                                        @if (!$readonly)
                                                            <h3
                                                                class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                                                <svg class="w-5 h-5 mr-2" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                                                    </path>
                                                                </svg>
                                                                แนบลิงก์หลักฐาน
                                                            </h3>
                                                            <div
                                                                id="evidence-links-quality-{{ $mainCriteria['id'] }}">
                                                                @foreach ($links as $idx => $link)
                                                                    <div
                                                                        class="flex items-center mb-2 evidence-link-row">
                                                                        <input type="url"
                                                                            name="evidence_list[{{ $mainCriteria['id'] }}][links][]"
                                                                            value="{{ $link }}"
                                                                            class="form-input text-base w-full h-12 px-4 rounded-lg border border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors"
                                                                            placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้">
                                                                        @if ($idx > 0 || count($links) > 1)
                                                                            <button type="button"
                                                                                class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded remove-evidence-link"
                                                                                title="ลบลิงก์">
                                                                                &times;
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <button type="button"
                                                                class="mt-2 px-4 py-2 bg-blue-100 text-blue-700 rounded add-evidence-link"
                                                                data-quality-main="{{ $mainCriteria['id'] }}">
                                                                + เพิ่มลิงก์หลักฐาน
                                                            </button>
                                                            <input type="hidden"
                                                                name="evidence_list[{{ $mainCriteria['id'] }}][evaluation_list_id]"
                                                                value="{{ $evaluationList['id'] }}">
                                                            <input type="hidden"
                                                                name="evidence_list[{{ $mainCriteria['id'] }}][quality_main_criteria_id]"
                                                                value="{{ $mainCriteria['id'] }}">
                                                        @else
                                                            <h3
                                                                class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                                                <svg class="w-5 h-5 mr-2" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                                                    </path>
                                                                </svg>
                                                                หลักฐาน
                                                            </h3>
                                                            @if (!empty($qualityEvidenceMap[$mainCriteria['id']]))
                                                                @foreach ((array) $qualityEvidenceMap[$mainCriteria['id']] as $link)
                                                                    <div
                                                                        class="p-3 bg-blue-50 border border-blue-200 rounded-lg mb-2">
                                                                        <a href="{{ $link }}" target="_blank"
                                                                            class="text-blue-600 hover:underline break-all">
                                                                            {{ $link }}
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <div
                                                                    class="text-gray-500 mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                                    ไม่มีหลักฐานแนบ
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </details>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Notes Section --}}
    @php
        $hasAnnotations = false;
        $annotations = [];
        foreach ($categoryItems as $category) {
            foreach ($category['evaluation_lists'] as $evalList) {
                if (!empty($evalList['annotation'])) {
                    $hasAnnotations = true;
                    $annotations[] = $evalList['annotation'];
                }
            }
        }
    @endphp
    @if ($hasAnnotations)
        {{-- บล็อกเนื้อหา --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">หมายเหตุ</h3>
                    <div class="text-sm text-yellow-700">
                        <ol class="list-decimal list-inside space-y-1">
                            @foreach (array_unique($annotations) as $annotation)
                                <li>{{ $annotation }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
<style>
    @media (max-width: 768px) {
        .md\:flex-row {
            flex-direction: column !important;
        }

        .md\:items-center {
            align-items: flex-start !important;
        }

        .md\:w-40 {
            width: 100% !important;
        }
    }

    details .chevron-up {
        display: inline-block !important;
    }

    details .chevron-down {
        display: none !important;
    }

    details[open] .chevron-up {
        display: none !important;
    }

    details[open] .chevron-down {
        display: inline-block !important;
    }
</style>

{{-- JavaScript for Quality Checkbox Handling --}}
<script>
    function handleQualityCheckboxChange(checkbox) {
        const subCriteriaId = checkbox.dataset.subCriteriaId;
        const score = parseFloat(checkbox.dataset.score) || 0;
        const scoreInput = document.getElementById(`quality-score-${subCriteriaId}`);

        if (scoreInput) {
            if (checkbox.checked) {
                scoreInput.value = score;
            } else {
                scoreInput.value = '';
            }
        }

        recalculateSummaryScores();
    }

    // Initialize checkbox states on page load
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input[name*="quality_criteria"]');
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                handleQualityCheckboxChange(checkbox);
            }
        });
    });

    function recalculateSummaryScores() {
        const readonlyInput = document.getElementById('quality-readonly');
        const isReadonly = readonlyInput && readonlyInput.value === '1';
        if (isReadonly) {
            return;
        }

        // Quantity: sum all score_D inputs (fallback to base if none)
        let quantitySum = 0;
        const quantityInputs = document.querySelectorAll('input[name^=\"quantity_list\"][name$=\"[score_D]\"]');
        if (quantityInputs.length > 0) {
            quantityInputs.forEach(input => {
                let val = parseFloat(input.value);
                if (!isNaN(val)) quantitySum += val;
            });
        } else {
            const baseInput = document.getElementById('quantity-base-score');
            quantitySum = baseInput ? parseFloat(baseInput.value) || 0 : 0;
        }

        // Quality: sum selected sub-criteria scores, capped per evaluation list
        const listTotals = {};
        document.querySelectorAll('input[name^=\"quality_list\"][name$=\"[score]\"]').forEach(input => {
            const val = parseFloat(input.value);
            if (isNaN(val)) return;

            const listId = input.dataset.evaluationListId || 'unknown';
            const listMax = parseFloat(input.dataset.listMax);

            if (!listTotals[listId]) {
                listTotals[listId] = {
                    sum: 0,
                    max: isNaN(listMax) ? 0 : listMax
                };
            }
            listTotals[listId].sum += val;
        });

        let qualitySum = 0;
        Object.values(listTotals).forEach(({
            sum,
            max
        }) => {
            let cappedSum = sum;
            if (max > 0 && cappedSum > max) cappedSum = max;
            qualitySum += cappedSum;
        });

        const qualityMaxInput = document.getElementById('quality-max-score');
        const qualityMax = qualityMaxInput ? parseFloat(qualityMaxInput.value) : 0;
        if (!isNaN(qualityMax) && qualityMax > 0 && qualitySum > qualityMax) {
            qualitySum = qualityMax;
        }

        // Update the summary fields
        const quantityEl = document.getElementById('quantity-summary');
        const qualityEl = document.getElementById('quality-summary');
        const totalEl = document.getElementById('total-summary');
        if (quantityEl) quantityEl.textContent = quantitySum.toFixed(2);
        if (qualityEl) qualityEl.textContent = qualitySum.toFixed(2);
        if (totalEl) totalEl.textContent = (quantitySum + qualitySum).toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll(
            'input[name^=\"quality_list\"][name$=\"[score]\"]'
        ).forEach(input => {
            input.addEventListener('input', recalculateSummaryScores);
        });
        recalculateSummaryScores();
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Function to update remove button visibility
        function updateRemoveButtonVisibility(container) {
            const rows = container.querySelectorAll('.evidence-link-row');
            const hasContent = Array.from(rows).some(row => {
                const input = row.querySelector('input[type="url"]');
                return input && input.value.trim() !== '';
            });

            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-evidence-link');
                const input = row.querySelector('input[type="url"]');
                if (rows.length > 1 || hasContent) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }

        // Add new evidence link input
        document.querySelectorAll('.add-evidence-link').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const mainCriteriaId = btn.getAttribute('data-quality-main');
                const container = document.getElementById('evidence-links-quality-' +
                    mainCriteriaId);
                const div = document.createElement('div');
                div.className = 'flex items-center mb-2 evidence-link-row';
                div.innerHTML = `
                <input type="url"
                    name="evidence_list[${mainCriteriaId}][links][]"
                    class="form-input text-base w-full h-12 px-4 rounded-lg border border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors"
                    placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้">
                <button type="button" 
                    class="ml-2 px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors remove-evidence-link" 
                    title="ลบลิงก์">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
                container.appendChild(div);
                updateRemoveButtonVisibility(container);

                // Focus on the new input
                const newInput = div.querySelector('input[type="url"]');
                newInput.focus();
            });
        });

        // Remove evidence link input
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-evidence-link')) {
                const container = e.target.closest('[id^="evidence-links-quality-"]');
                const row = e.target.closest('.evidence-link-row');
                const rows = container.querySelectorAll('.evidence-link-row');

                // If it's the last row and it's empty, just clear it
                if (rows.length === 1) {
                    const input = row.querySelector('input[type="url"]');
                    input.value = '';
                } else {
                    // Remove the row
                    row.remove();
                }

                updateRemoveButtonVisibility(container);
            }
        });

        // Update remove button visibility when input values change
        document.addEventListener('input', function(e) {
            if (e.target.type === 'url' && e.target.name && e.target.name.includes('evidence_list')) {
                const container = e.target.closest('[id^="evidence-links-quality-"]');
                if (container) {
                    updateRemoveButtonVisibility(container);
                }
            }
        });

        // Initialize remove button visibility on page load
        document.querySelectorAll('[id^="evidence-links-quality-"]').forEach(updateRemoveButtonVisibility);
    });
</script>
