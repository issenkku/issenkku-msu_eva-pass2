@props([
    'categoryItems' => [],
    'readonly' => false,
    'evidenceMap' => [],
    'qualityEvidenceMap' => [],
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

{{-- เนื้อหาแบบประเมิน --}}
<div class="space-y-8">
    @foreach($categoryItems as $category)
        {{-- Category Container --}}
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
            {{-- Category Header --}}
            <div class="bg-purple-100 px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $category['main_categories'] }}
                </h1>
                @if(!empty($category['sub_categories']))
                    <p class="text-base text-gray-600 mt-1">{{ $category['sub_categories'] }}</p>
                @endif
            </div>

            <div class="p-6">
                @foreach($category['evaluation_lists'] as $evaluationList)
                    @php
                        $hasQualityItems = !empty($evaluationList['quality_items']);
                    @endphp
                    {{-- Evaluation List Container --}}
                    @if($hasQualityItems)
                    <details class="mb-8 bg-gray-50 rounded-lg border border-gray-300">
                    @else
                    <div class="mb-8 bg-gray-50 rounded-lg border border-gray-300">
                    @endif
                        {{-- Evaluation List Header --}}
                        @if($hasQualityItems)
                        <summary class="list-none [&::-webkit-details-marker]:hidden cursor-pointer">
                        @endif
                        <div class="{{ $hasQualityItems ? 'relative pr-14' : '' }} bg-gradient-to-r from-purple-100 to-blue-100 px-6 py-4 rounded-t-lg border-b border-gray-200">
                            <div class="flex items-center space-x-3">
                                <h2 class="text-xl font-bold text-gray-800">
                                    {{ $evaluationList['name'] }}
                                </h2>
                                @php
                                    $listSelectedQualitySum = 0;
                                    $qualityMainTotal = 0;
                                    $qualityMainChecked = 0;
                                    if (!empty($evaluationList['quality_items'])) {
                                        $qualityMainTotal = count($evaluationList['quality_items']);
                                        foreach ($evaluationList['quality_items'] as $mainCriteria) {
                                            $sorted = collect($mainCriteria['sub_criterias'])->sortBy('sequence')->values();
                                            $hasAnyChecked = $sorted->contains(function ($sub) {
                                                $hasScore = !empty($sub['score']) && $sub['score'] !== '' && $sub['score'] !== null;
                                                return $hasScore || ($sub['user_selected'] ?? false);
                                            });
                                            if ($hasAnyChecked) {
                                                $qualityMainChecked++;
                                            }

                                            foreach ($mainCriteria['sub_criterias'] as $subCriteria) {
                                                $hasScore = isset($subCriteria['score']) && $subCriteria['score'] !== '' && $subCriteria['score'] !== null;
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
                                @if(!empty($evaluationList['quality_items']))
                                    @if(isset($evaluationList['sum_score']))
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                                            คะแนน {{ $evaluationList['sum_score'] }}
                                        </span>
                                    @endif
                                    <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        คะแนนที่ได้ {{ number_format($listSelectedQualitySum, 2) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $qualityMainTotal > 0 && $qualityMainChecked === $qualityMainTotal ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                        ตรวจสอบแล้ว {{ $qualityMainChecked }}/{{ $qualityMainTotal }}
                                    </span>
                                @endif
                            </div>

                            @if(!empty($evaluationList['annotation']))
                                <div class="flex items-center space-x-2 mt-1">
                                    <p class="text-sm text-gray-600">{{ $evaluationList['annotation'] }}</p>
                                </div>
                            @endif
                            @if($hasQualityItems)
                                <div class="absolute right-6 top-4">
                                    <svg class="w-5 h-5 text-purple-600 chevron-up" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                    <svg class="w-5 h-5 text-purple-600 chevron-down" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        @if($hasQualityItems)
                        </summary>
                        @endif

                        <div class="p-6">
                            {{-- Quantity Section --}}
                            @if(count($evaluationList['quantity_items']) > 0)
                                <div class="mb-8">
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                        <h3 class="text-lg font-semibold text-green-800 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            ด้านปริมาณ
                                        </h3>
                                    </div>

                                    @foreach($evaluationList['quantity_items'] as $mainCriteria)
                                        {{-- Main Criteria Header --}}
                                        <div class="mb-4 border-l-4 border-green-400 pl-4 py-2 bg-green-50">
                                            <h4 class="text-base font-semibold text-gray-800">
                                                {{ $mainCriteria['name'] }}
                                            </h4>
                                            @if(!empty($mainCriteria['tooltips']))
                                                <div class="text-sm text-gray-500 mt-1">{!! $mainCriteria['tooltips'] !!}</div>
                                            @endif
                                        </div>

                                        @if(!empty($mainCriteria['formulas']) && count($mainCriteria['formulas']) > 0)
                                            <div class="ml-6 mb-6">
                                                <div class="p-4 bg-green-50 rounded-xl shadow-sm">
                                                    <h4 class="text-lg font-semibold text-green-700 mb-3 flex items-center">
                                                        {{-- <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 17v-2a4 4 0 014-4h6M9 13H5v6h4v-2a4 4 0 014-4z"/>
                                                        </svg> --}}
                                                        สูตรการคำนวณ
                                                    </h4>

                                                    <div class="space-y-3">
                                                        @foreach($mainCriteria['formulas'] as $formula)
                                                            @if(!empty($formula['condition']))
                                                                <div class="p-4 bg-white border border-green-200 rounded-lg text-center">
                                                                    <span class="text-sm md:text-lg font-medium text-gray-800 block">
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
                                            @if(!$readonly)
                                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                                    <div class="font-semibold mb-1">คำแนะนำการกรอกข้อมูล</div>
                                                    <div>กรอกข้อมูลตามผลงานจริงของรายการย่อยนี้ ระบบจะนำค่าไปคำนวณคะแนนด้านปริมาณอัตโนมัติ โปรดตรวจสอบความถูกต้องก่อนบันทึก</div>
                                                </div>
                                            @endif
                                            @foreach(collect($mainCriteria['sub_criterias'])->sortBy('sequence') as $subCriteria)
                                                <details class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all open:shadow-md">
                                                    <summary class="flex items-center justify-between gap-3 bg-gradient-to-r from-white via-purple-50/40 to-white px-4 py-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                                        <div class="min-w-0 flex items-center gap-3 flex-wrap">
                                                            <span class="text-base font-semibold text-slate-800">
                                                                {{ $subCriteria['name'] }}
                                                            </span>
                                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-purple-200 bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700">
                                                                <span>ดูรายละเอียด</span>
                                                                <svg class="w-4 h-4 chevron-down" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                </svg>
                                                                <svg class="w-4 h-4 chevron-up" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </summary>
                                                    <div class="border-t border-slate-200 bg-gradient-to-b from-slate-50 to-white px-4 py-4">
                                                        <div class="rounded-xl border border-slate-200 bg-white/90 p-4 shadow-sm">
                                                            <div class="space-y-4">
                                                                @if(!empty($subCriteria['description']))
                                                                    <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm leading-6 text-slate-600">{{ $subCriteria['description'] }}</div>
                                                                @endif

                                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                                                {{-- Score A --}}
                                                                <div class="flex h-full flex-col rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                                                                    <label class="mb-2 flex min-h-[32px] items-center justify-center text-center text-sm font-semibold leading-6 text-slate-700">
                                                                        ค่าน้ำหนักคะแนน (A)
                                                                    </label>
                                                                    <input type="text"
                                                                        name="quantity_list[{{ $subCriteria['id'] }}][score_A]"
                                                                        value="{{ $subCriteria['score_a'] ?? '' }}"
                                                                        readonly
                                                                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-center text-lg font-semibold text-slate-700 shadow-sm">
                                                                </div>

                                                                {{-- Score B --}}
                                                                <div class="flex h-full flex-col rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                                                                    <label class="mb-2 flex min-h-[32px] items-center justify-center text-center text-sm font-semibold leading-6 text-slate-700">
                                                                        หน่วยภาระงานมาตรฐาน (B)
                                                                    </label>
                                                                    <input type="text"
                                                                        name="quantity_list[{{ $subCriteria['id'] }}][score_B]"
                                                                        value="{{ $subCriteria['score_b'] ?? '' }}"
                                                                        readonly
                                                                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-center text-lg font-semibold text-slate-700 shadow-sm">
                                                                </div>

                                                                {{-- Score C --}}
                                                                <div class="flex h-full flex-col rounded-xl border border-blue-200 bg-blue-50/60 p-3">
                                                                    <label class="mb-2 flex min-h-[32px] items-center justify-center text-center text-sm font-semibold leading-6 text-slate-700">
                                                                        หน่วยภาระงานที่ทำได้ (C)
                                                                    </label>
                                                                    @if(!$readonly)
                                                                        <input type="number" step="1"
                                                                            name="quantity_list[{{ $subCriteria['id'] }}][score_C]"
                                                                            value="{{ $subCriteria['tor_compliant'] ?? '' }}"
                                                                            data-sub-criteria-id="{{ $subCriteria['id'] }}"
                                                                            oninput="calculateScoreD(this)"
                                                                            class="h-10 w-full rounded-lg border border-blue-300 bg-white px-3 text-center text-lg font-semibold text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-300"
                                                                            placeholder="ใส่ค่า C">
                                                                    @else
                                                                        <input type="text"
                                                                            name="quantity_list[{{ $subCriteria['id'] }}][tor_compliant]"
                                                                            value="{{ $subCriteria['tor_compliant'] ?? '' }}"
                                                                            readonly
                                                                            class="h-10 w-full rounded-lg border border-blue-300 bg-white px-3 text-center text-lg font-semibold text-slate-700 shadow-sm"
                                                                            placeholder="0">
                                                                    @endif

                                                                </div>

                                                                {{-- Score D --}}
                                                                <div class="flex h-full flex-col rounded-xl border border-emerald-200 bg-emerald-50/70 p-3">
                                                                    <label class="mb-2 flex min-h-[32px] items-center justify-center text-center text-sm font-semibold leading-6 text-slate-700">
                                                                        คะแนนที่คำนวณได้ (D)
                                                                    </label>
                                                                    <input type="text"
                                                                        name="quantity_list[{{ $subCriteria['id'] }}][score_D]"
                                                                        id="score-D-{{ $subCriteria['id'] }}"
                                                                        value="{{ $subCriteria['score_d'] ?? '' }}"
                                                                        readonly
                                                                        class="h-10 w-full rounded-lg border border-emerald-300 bg-white px-3 text-center text-lg font-semibold text-emerald-700 shadow-sm"
                                                                        placeholder="0">
                                                                </div>
                                                            </div>
                                                            @if(!$readonly || !empty($subCriteria['score_histories']))
                                                                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3">
                                                                    <label class="@if($readonly) hidden @else mb-2 block text-sm font-semibold leading-6 text-slate-700 @endif">
                                                                        หมายเหตุการแก้ไขค่า C
                                                                    </label>
                                                                    @if(!$readonly)
                                                                        <textarea
                                                                            name="quantity_list[{{ $subCriteria['id'] }}][description]"
                                                                            rows="3"
                                                                            class="w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-300"
                                                                            placeholder="ระบุหมายเหตุการแก้ไข">{{ $subCriteria['score_description'] ?? '' }}</textarea>
                                                                    @else
                                                                        <textarea
                                                                            rows="3"
                                                                            readonly
                                                                            class="hidden w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm">{{ $subCriteria['score_description'] ?? '' }}</textarea>
                                                                    @endif
                                                                    @if(!empty($subCriteria['score_modified_by_name']) || !empty($subCriteria['score_modified_by_role']))
                                                                        <div class="@if($readonly) hidden @else mt-2 text-xs text-slate-600 @endif">
                                                                            ล่าสุดแก้ไขโดย {{ $subCriteria['score_modified_by_name'] ?: '-' }}
                                                                            @if(!empty($subCriteria['score_modified_by_role']))
                                                                                ({{ $subCriteria['score_modified_by_role'] }})
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    @if(!empty($subCriteria['score_histories']))
                                                                        <div class="@if(!$readonly) mt-3 border-t border-amber-200 pt-3 @endif space-y-2">
                                                                            <div class="text-xs font-semibold text-slate-700">ประวัติการแก้ไข</div>
                                                                            @foreach($subCriteria['score_histories'] as $history)
                                                                                <div class="rounded-lg bg-white px-3 py-2 text-xs text-slate-700 shadow-sm">
                                                                                    <div>ค่าเดิม: {{ $history['previous_score_c'] ?? '-' }} | ค่าใหม่: {{ $history['new_score_c'] ?? '-' }}</div>
                                                                                    <div>ผู้แก้ไข: {{ $history['modified_by_name'] ?: '-' }}@if(!empty($history['modified_by_role'])) ({{ $history['modified_by_role'] }})@endif</div>
                                                                                    <div>หมายเหตุ: {{ $history['new_description'] ?? '-' }}</div>
                                                                                    @if(!empty($history['created_at']))
                                                                                        <div class="text-slate-500">เมื่อ {{ $history['created_at'] }}</div>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @php
                                                        $workloadData = $workloadMap[$subCriteria['id']] ?? null;
                                                        $workloadSubCriteria = $workloadData['subCriteria'] ?? null;
                                                        $workloadForms = $workloadData['workloadForms'] ?? collect();
                                                        $workloadEntriesByFormId = $workloadData['workloadEntriesByFormId'] ?? collect();
                                                        $evidenceLinksByEntryId = $workloadData['evidenceLinksByEntryId'] ?? collect();
                                                    @endphp
                                                    <div class="mt-4 border-t border-slate-200 pt-4">
                                                        <h4 class="mb-2 flex items-center text-sm font-semibold text-slate-800">
                                                            ข้อมูลภาระงาน
                                                        </h4>
                                                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                                            <div class="p-3 sm:p-4">
                                                                <x-workload-summary :sub-criteria="$workloadSubCriteria" :workload-forms="$workloadForms"
                                                                    :workload-entries-by-form-id="$workloadEntriesByFormId" :evidence-links-by-entry-id="$evidenceLinksByEntryId" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- legacy score_description block removed
                                                    @if(false && !empty($subCriteria['score_description']))
                                                        <h4 class="text-base font-semibold text-gray-800 flex items-center border-t border-gray-200 pt-3">
                                                            หมายเหตุเพิ่มเติม
                                                        </h4>
                                                        <div class="text-sm text-gray-500 mt-1">{{ $subCriteria['score_description'] }}</div>
                                                    @else
                                                        <div class="text-sm text-gray-500 mt-1">ไม่มีหมายเหตุเพิ่มเติม</div>
                                                    @endif --}}
                                                </div>
                                            </details>

                                                @if(!$readonly)
                                                    <input type="hidden"
                                                        name="quantity_list[{{ $subCriteria['id'] }}][quantity_sub_criteria_id]"
                                                        value="{{ $subCriteria['id'] }}">
                                                @endif
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Quality Section --}}
@if(count($evaluationList['quality_items']) > 0)


        {{-- ส่วนแสดงรายการด้านคุณภาพ --}}
        <div class="mt-6">
            @foreach($evaluationList['quality_items'] as $mainCriteria)
                <details class="group border border-gray-300 rounded-lg bg-white mb-6">
                    <summary class="flex items-center justify-between gap-4 px-4 py-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden bg-purple-50">
                        <div class="min-w-0 flex items-center gap-3 flex-wrap">
                            <h4 class="text-base font-semibold text-gray-800">
                                {{ $mainCriteria['name'] }}
                            </h4>
                        </div>
                        <svg class="w-5 h-5 text-purple-600 chevron-up" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg class="w-5 h-5 text-purple-600 chevron-down" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>

                    <div class="px-4 py-4">
                        @if(!empty($mainCriteria['tooltips']))
                            <div class="text-sm text-gray-500 mb-3">{!! $mainCriteria['tooltips'] !!}</div>
                        @endif

                        {{-- Quality Sub Criteria --}}
                        <div class="space-y-3 ml-2">
                            @php
                                $mainScoreSum = 0;
                            @endphp
                            @foreach(collect($mainCriteria['sub_criterias'])->sortBy('sequence') as $subCriteria)
                                @php
                                    $hasScore = !empty($subCriteria['score']) && $subCriteria['score'] !== '' && $subCriteria['score'] !== null;
                                    $shouldBeChecked = $hasScore || ($subCriteria['user_selected'] ?? false);
                                    $selectedScore = $hasScore ? $subCriteria['score'] : ($shouldBeChecked ? $subCriteria['num_score'] : '0.00');
                                    $displayScore = $subCriteria['num_score'] ?? 0;
                                      if ($shouldBeChecked) {
                                        $selectedDisplayScore = $displayScore;
                                    }
                                    $mainScoreSum += floatval($selectedScore);
                                @endphp

                                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6">
                                        {{-- Checkbox and Label --}}
                                        <div class="flex items-start flex-1 min-w-0">
                                            @if(!$readonly)
                                                <input type="checkbox"
                                                    name="quality_criteria[{{ $subCriteria['id'] }}]"
                                                    value="1"
                                                    data-score="{{ $subCriteria['num_score'] ?? 0 }}"
                                                    data-sub-criteria-id="{{ $subCriteria['id'] }}"
                                                    data-main-criteria-id="{{ $mainCriteria['id'] }}"
                                                    data-allow-multiple="{{ !empty($mainCriteria['allow_multiple']) ? '1' : '0' }}"
                                                    onchange="handleQualityCheckboxChange(this)"
                                                    {{ $shouldBeChecked ? 'checked' : '' }}
                                                    class="h-5 w-5 accent-purple-600 text-purple-600 focus:ring-purple-500 border-gray-300 rounded mr-3">
                                                <div class="min-w-0">
                                                    <label class="text-base text-gray-800 break-words">
                                                        {{ $subCriteria['name'] }}
                                                    </label>
                                                    @if(!empty($subCriteria['description']))
                                                        <div class="text-sm text-gray-500 mt-1 break-words">
                                                            {!! $subCriteria['description'] !!}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <input type="checkbox"
                                                    {{ $shouldBeChecked ? 'checked' : '' }}
                                                    disabled
                                                    class="h-5 w-5 accent-purple-600 text-purple-600 border-gray-300 rounded mr-3 disabled:opacity-100">
                                                <div class="min-w-0">
                                                    <span class="text-base text-gray-800 break-words">
                                                        {{ $subCriteria['name'] }}
                                                    </span>
                                                    @if(!empty($subCriteria['description']))
                                                        <div class="text-sm text-gray-500 mt-1 break-words">
                                                            {!! $subCriteria['description'] !!}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        {{-- Selected Score --}}
                                        {{-- <div class="w-full md:w-60">
                                            <div class="text-base text-gray-800 text-center">
                                                คะแนนที่ได้ {{ number_format((float)$displayScore, 2) }}
                                            </div>
                                        </div> --}}
                                        {{-- Hidden Score Input for edit mode --}}
                                        @if(!$readonly)
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
                            @if($readonly)
                                {{-- <div class="mt-5 p-6 bg-blue-50 rounded-xl border border-blue-500 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                                <span class="text-lg font-semibold text-blue-700">คะแนนรวมทั้งหมด</span>

                                    <span class="text-lg font-semibold text-blue-900">   {{ number_format((float)($selectedDisplayScore ?? 0), 2) }}</span>
                                </div> --}}
                            @endif
                        </div>

                        {{-- Evidence Section for Quality Main Criteria --}}
                        <div class="mt-5 border-t border-gray-200 pt-4">
                            @php
                                $links = isset($qualityEvidenceMap[$mainCriteria['id']]) && is_array($qualityEvidenceMap[$mainCriteria['id']])
                                    ? $qualityEvidenceMap[$mainCriteria['id']]
                                    : (isset($qualityEvidenceMap[$mainCriteria['id']]) ? [$qualityEvidenceMap[$mainCriteria['id']]] : ['']);

                                if (empty($links) || (count($links) === 1 && empty($links[0]))) {
                                    $links = [''];
                                }
                            @endphp

                            @if(!$readonly)
                                <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    ลิงก์หลักฐาน
                                </h3>
                                <div id="evidence-links-quality-{{ $mainCriteria['id'] }}">
                                    @foreach($links as $idx => $link)
                                        <div class="flex items-center mb-2 evidence-link-row">
                                            <input type="url"
                                                name="evidence_list[{{ $mainCriteria['id'] }}][links][]"
                                                value="{{ $link }}"
                                                class="form-input text-base w-full h-12 px-4 rounded-lg border border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors"
                                                placeholder="วางลิงก์หลักฐาน">
                                            @if($idx > 0 || count($links) > 1)
                                                <button type="button" class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded remove-evidence-link" title="ลบลิงก์">
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
                                <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    ลิงก์หลักฐาน
                                </h3>
                                @if(!empty($qualityEvidenceMap[$mainCriteria['id']]))
                                    @foreach((array)$qualityEvidenceMap[$mainCriteria['id']] as $link)
                                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg mb-2">
                                            <a href="{{ $link }}" target="_blank" class="text-blue-600 hover:underline break-all">
                                                {{ $link }}
                                            </a>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-gray-500 mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        ไม่มีลิงก์หลักฐาน
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

@endif
                        </div>
                    @if($hasQualityItems)
                    </details>
                    @else
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Notes Section --}}
    @php
        $hasAnnotations = false;
        $annotations = [];
        foreach($categoryItems as $category) {
            foreach($category['evaluation_lists'] as $evalList) {
                if(!empty($evalList['annotation'])) {
                    $hasAnnotations = true;
                    $annotations[] = $evalList['annotation'];
                }
            }
        }
    @endphp
    @if($hasAnnotations)
        {{-- ส่วนหมายเหตุ --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">หมายเหตุ</h3>
                    <div class="text-sm text-yellow-700">
                        <ol class="list-decimal list-inside space-y-1">
                            @foreach(array_unique($annotations) as $annotation)
                                <li>{{ $annotation }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- summary score -->
    @php
        $totalQuantityScore = 0;
        $totalQualityScore = 0;

        foreach($categoryItems as $category) {
            foreach($category['evaluation_lists'] as $evalList) {
                // Quantity
                foreach($evalList['quantity_items'] as $mainCriteria) {
                    foreach($mainCriteria['sub_criterias'] as $subCriteria) {
                        $totalQuantityScore += floatval($subCriteria['score_d'] ?? 0);
                    }
                }

                // Quality: sum selected sub-criteria per list, then cap by list max
                $evaluationListQualityTotal = 0;
                foreach($evalList['quality_items'] as $mainCriteria) {
                    foreach($mainCriteria['sub_criterias'] as $subCriteria) {
                        $hasScore = isset($subCriteria['score']) && $subCriteria['score'] !== '' && $subCriteria['score'] !== null;
                        $isSelected = $hasScore || ($subCriteria['user_selected'] ?? false);
                        if ($isSelected) {
                            $evaluationListQualityTotal += $hasScore
                                ? floatval($subCriteria['score'])
                                : floatval($subCriteria['num_score'] ?? 0);
                        }
                    }
                }
                $listMaxScore = floatval($evalList['sum_score'] ?? 0);
                if ($listMaxScore > 0 && $evaluationListQualityTotal > $listMaxScore) {
                    $evaluationListQualityTotal = $listMaxScore;
                }
                $totalQualityScore += $evaluationListQualityTotal;
            }
        }
        if ($qualityMaxScore > 0 && $totalQualityScore > $qualityMaxScore) {
            $totalQualityScore = $qualityMaxScore;
        }
        $totalScore = $totalQuantityScore + $totalQualityScore;
    @endphp
    {{-- ส่วนสรุปคะแนนรวม --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl shadow-sm p-6 mt-6">
        <h3 class="text-xl font-bold text-blue-900 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2h6v2m-7 4h8a2 2 0 002-2v-5a2 2 0 00-2-2h-1V7a4 4 0 10-8 0v5H9a2 2 0 00-2 2v5a2 2 0 002 2z"/>
            </svg>
            สรุปคะแนนรวม
        </h3>

        {{-- รายละเอียดคะแนนแต่ละด้าน --}}
        <div class="space-y-3 text-blue-800">
            <div class="flex justify-between items-center">
                <span class="text-base">คะแนนด้านปริมาณ (Quantity)</span>
                <span id="quantity-summary" class="font-semibold text-blue-900">{{ number_format($totalQuantityScore, 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-base">คะแนนด้านคุณภาพ (Quality)</span>
                <span id="quality-summary" class="font-semibold text-blue-900">{{ number_format($totalQualityScore, 2) }}</span>
            </div>
        </div>

        {{-- คะแนนรวมทั้งหมด --}}
        <div class="mt-5 p-4 bg-white rounded-xl shadow-inner flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <span class="text-lg font-semibold text-blue-700">คะแนนรวมทั้งหมด</span>
            <span id="total-summary" class="text-2xl font-bold text-blue-900">{{ number_format($totalScore, 2) }}</span>
        </div>
    </div>
</div>

@include('components.unified-director-styles')
@include('components.unified-director-script')
