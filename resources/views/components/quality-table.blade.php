@props([
    'qualityItems' => [],
    'title' => 'ด้านคุณภาพ',
    'readonly' => false,
    'evidenceMap' => [],
])

{{-- ตารางแสดงรายการประเมินด้านคุณภาพ --}}
<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
    <div class="bg-purple-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
    </div>

    <div class="space-y-6 p-4">
        @php
            $groupedItems = collect($qualityItems)->groupBy('evaluation_list_id');
            $evaluationListCount = 0;
        @endphp

        @foreach($groupedItems as $evalListId => $items)
            @php
                $evaluationListItem = $items->where('is_evaluation_list', true)->first();
                $mainCriteriaItems = $items->where('is_main', true)->where('is_evaluation_list', false);
                $subCriteriaItems = $items->where('is_main', false)->where('is_evaluation_list', false);

                $evaluationListCount++;
            @endphp

            @if($evaluationListCount > 1)
                <hr class="my-6 border-t border-gray-300">
            @endif

            @if($evaluationListItem)
                <div class="rounded-xl border border-purple-200 bg-purple-50 p-5">
                    <h3 class="text-lg font-bold text-purple-800">
                        {{ $evaluationListItem['title'] ?? 'รายการที่ ' . $evaluationListCount }}
                    </h3>
                    @if(!empty($evaluationListItem['subtitle']))
                        <p class="mt-1 text-sm text-gray-600">{{ $evaluationListItem['subtitle'] }}</p>
                    @endif
                </div>
            @endif

            @php
                $mainCriteriaGroups = $subCriteriaItems->groupBy('main_criteria_id');
            @endphp

            @foreach($mainCriteriaGroups as $mainCriteriaId => $subItems)
                @php
                    $mainCriteriaItem = $mainCriteriaItems->where('main_criteria_id', $mainCriteriaId)->first();
                @endphp

                @if($mainCriteriaItem)
                    <div class="ml-4 mt-6 border-t border-gray-200 pt-4">
                        <div class="border-l-4 border-purple-400 bg-white py-2 pl-4">
                            <h4 class="text-base font-semibold text-gray-800">
                                {{ $mainCriteriaItem['title'] ?? 'หลักเกณฑ์หลัก' }}
                            </h4>
                            @if(!empty($mainCriteriaItem['subtitle']))
                                <p class="mt-1 text-sm text-gray-500">{{ $mainCriteriaItem['subtitle'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach($subItems->sortBy('sequence') as $index => $item)
                    @php
                        $hasScore = !empty($item['score']) && $item['score'] !== '' && $item['score'] !== null;
                        $shouldBeChecked = $hasScore || ($item['user_selected'] ?? false);
                    @endphp

                    <div class="ml-8 mt-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                            <div class="flex flex-1 items-start">
                                @if(!$readonly)
                                    <input
                                        id="quality-criteria-{{ $item['sub_criteria_id'] }}"
                                        type="checkbox"
                                        name="quality_criteria[{{ $item['sub_criteria_id'] }}]"
                                        value="1"
                                        data-score="{{ $item['num_score'] ?? 0 }}"
                                        data-sub-criteria-id="{{ $item['sub_criteria_id'] }}"
                                        data-main-criteria-id="{{ $item['main_criteria_id'] ?? '' }}"
                                        data-allow-multiple="{{ !empty($item['allow_multiple']) ? '1' : '0' }}"
                                        data-quality-checkbox
                                        {{ $shouldBeChecked ? 'checked' : '' }}
                                        class="mt-1 mr-3 h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <label for="quality-criteria-{{ $item['sub_criteria_id'] }}" class="text-base text-gray-800">
                                        {{ $item['title'] ?? 'รายการย่อย' }}
                                    </label>
                                @else
                                    <input
                                        id="quality-criteria-readonly-{{ $item['sub_criteria_id'] }}"
                                        type="checkbox"
                                        {{ $shouldBeChecked ? 'checked' : '' }}
                                        disabled
                                        class="mt-1 mr-3 h-4 w-4 rounded border-gray-300 text-purple-600">
                                    <span class="text-base text-gray-800">
                                        {{ $item['title'] ?? 'รายการย่อย' }}
                                    </span>
                                @endif
                            </div>

                            <div class="w-full lg:w-1/4" style="display: none;">
                                @if(!$readonly)
                                    <div class="flex flex-col">
                                        <label for="quality-score-{{ $item['sub_criteria_id'] }}" class="mb-1 text-xs text-gray-600">คะแนน</label>
                                        <input
                                            type="hidden"
                                            name="quality_list[{{ $item['sub_criteria_id'] }}][quality_sub_criteria_id]"
                                            value="{{ $item['sub_criteria_id'] }}">
                                        <input
                                            type="hidden"
                                            name="quality_list[{{ $item['sub_criteria_id'] }}][evaluation_list_id]"
                                            value="{{ $evalListId }}">
                                        <input
                                            type="number"
                                            id="quality-score-{{ $item['sub_criteria_id'] }}"
                                            name="quality_list[{{ $item['sub_criteria_id'] }}][score]"
                                            value="{{ $hasScore ? $item['score'] : ($shouldBeChecked ? $item['num_score'] : '') }}"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            readonly
                                            class="form-input h-10 w-full rounded border-gray-300 px-3 text-center text-base {{ $shouldBeChecked ? 'bg-white' : 'cursor-not-allowed bg-gray-50' }}"
                                            placeholder="0.00">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach

            <div class="ml-8 mr-5 mt-6 border-t border-purple-200 pt-4">
                @if(!$readonly)
                    <h3 class="mb-2 text-base font-semibold text-purple-800">แนบลิงก์หลักฐาน</h3>
                    <input
                        type="url"
                        name="evidence_list[{{ $evalListId }}][link]"
                        value="{{ $evidenceMap[$evalListId] ?? '' }}"
                        class="form-input h-10 w-full rounded border-gray-300 bg-gray-100 px-3 text-base focus:border-purple-500 focus:ring-purple-500"
                        placeholder="ใส่ลิงก์หลักฐาน">

                    <input
                        type="hidden"
                        name="evidence_list[{{ $evalListId }}][evaluation_list_id]"
                        value="{{ $evalListId }}">
                @else
                    @if(!empty($evidenceMap[$evalListId]))
                        <h3 class="mb-2 text-base font-semibold text-purple-800">หลักฐาน</h3>
                        <a href="{{ $evidenceMap[$evalListId] }}" target="_blank" class="text-blue-600 hover:underline">
                            {{ $evidenceMap[$evalListId] }}
                        </a>
                    @endif
                @endif
            </div>
        @endforeach

        @php
            $hasAnnotations = collect($qualityItems)
                ->where('is_evaluation_list', true)
                ->whereNotNull('subtitle')
                ->isNotEmpty();
        @endphp

        @if($hasAnnotations)
            <div class="space-y-6 p-8">
                <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-6 py-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="mt-0.5 h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-base font-medium text-yellow-800">หมายเหตุ</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ol class="list-inside list-decimal space-y-1">
                                    @foreach($qualityItems as $item)
                                        @if(($item['is_evaluation_list'] ?? false) && !empty($item['subtitle']))
                                            <li>{{ $item['subtitle'] }}</li>
                                        @endif
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@include('components.quality-table-script')
