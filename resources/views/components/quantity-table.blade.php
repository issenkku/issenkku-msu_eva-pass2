@props([
    'evaluationItems' => [],
    'title' => 'ด้านปริมาณ',
    'readonly' => false,
    'evidenceMap' => [] 
])

{{-- บล็อกเนื้อหา --}}
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    {{-- บล็อกเนื้อหา --}}
    <div class="bg-purple-100 px-6 py-4">
        <h2 class="text-xl font-semibold text-gray-800">{{ $title }}</h2>
    </div>

    {{-- บล็อกเนื้อหา --}}
    <div class="p-4 space-y-6">
        @php 
            // Group items by evaluation list
            $groupedItems = collect($evaluationItems)->groupBy('evaluation_list_id');
            $evaluationListCount = 0;
        @endphp

        @foreach($groupedItems as $evalListId => $items)
            @php
                // Find the evaluation list header
                $evaluationListItem = $items->where('is_evaluation_list', true)->first();
                $mainCriteriaItems = $items->where('is_main', true)->where('is_evaluation_list', false);
                $subCriteriaItems = $items->where('is_main', false)->where('is_evaluation_list', false);
                
                $evaluationListCount++;
            @endphp

            {{-- Add separator between evaluation lists --}}
            @if($evaluationListCount > 1)
                <hr class="border-t border-gray-300 my-6">
            @endif

            {{-- Evaluation List Header --}}
            @if($evaluationListItem)
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                    <h3 class="text-lg font-bold text-purple-800">
                        {{ $evaluationListItem['title'] ?? "รายการที่ " . $evaluationListCount }}
                    </h3>
                    @if(!empty($evaluationListItem['subtitle']))
                        <p class="text-sm text-gray-600 mt-1">{{ $evaluationListItem['subtitle'] }}</p>
                    @endif
                </div>
            @endif

            {{-- Group by main criteria within this evaluation list --}}
            @php
                $mainCriteriaGroups = $subCriteriaItems->groupBy('main_criteria_id');
            @endphp

            @foreach($mainCriteriaGroups as $mainCriteriaId => $subItems)
                @php
                    $mainCriteriaItem = $mainCriteriaItems->where('main_criteria_id', $mainCriteriaId)->first();
                @endphp

                {{-- Main Criteria Header --}}
                @if($mainCriteriaItem)
                    <div class="ml-4 mt-6 border-t border-gray-200 pt-4">
                        <div class="bg-white border-l-4 border-purple-400 pl-4 py-2">
                            <h4 class="text-base font-semibold text-gray-800">
                                {{ $mainCriteriaItem['title'] ?? "หลักเกณฑ์หลัก" }}
                            </h4>
                            @if(!empty($mainCriteriaItem['subtitle']))
                                <p class="text-sm text-gray-500 mt-1">{{ $mainCriteriaItem['subtitle'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Sub Criteria Items --}}
                @foreach($subItems->sortBy('sequence') as $index => $item)
                    <div class="ml-8 mt-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <div class="flex items-start flex-1">
                                @if(!$readonly)
                                    <label class="text-base text-gray-800">
                                        {{ $item['title'] ?? "รายการย่อย" }}
                                    </label>
                                @else
                                    <span class="text-base text-gray-800">
                                        {{ $item['title'] ?? "รายการย่อย" }}
                                    </span>
                                @endif
                            </div>

                            <div class="w-full lg:w-1/3">
                                @if($readonly)
                                    <div class="text-base text-gray-800">
                                        {{ $item['tor_compliant'] ?? '-' }}
                                    </div>
                                @else
                                    <input type="text" 
                                        name="quantity_list[{{ $item['sub_criteria_id'] }}][score_C]" 
                                        value="{{ $item['tor_compliant'] ?? '' }}"
                                        class="form-input text-base w-full h-10 ml-2 px-3 rounded border border-gray-400 focus:ring-purple-500 focus:border-purple-500" 
                                        placeholder="ใส่ข้อมูล">
                                    
                                    <input type="hidden" 
                                        name="quantity_list[{{ $item['sub_criteria_id'] }}][quantity_sub_criteria_id]" 
                                        value="{{ $item['sub_criteria_id'] }}">
                                        
                                    <input type="hidden" 
                                        name="quantity_list[{{ $item['sub_criteria_id'] }}][evaluation_list_id]" 
                                        value="{{ $evalListId }}">
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach

            {{-- Evidence Section for this Evaluation List --}}
            <div class="ml-8 mr-5 mt-6 pt-4 border-t border-purple-200">
                @if(!$readonly)
                    <h3 class="text-base font-semibold text-purple-800 mb-2">แนบลิงก์หลักฐาน</h3>
                    <input type="url" 
                        name="evidence_list[{{ $evalListId }}][link]" 
                        value="{{ $evidenceMap[$evalListId] ?? '' }}"
                        class="form-input text-base w-full h-10 px-3 rounded border-gray-300 bg-gray-100 focus:ring-purple-500 focus:border-purple-500" 
                        placeholder="ใส่ลิงก์หลักฐาน">

                    <input type="hidden" 
                        name="evidence_list[{{ $evalListId }}][evaluation_list_id]" 
                        value="{{ $evalListId }}">
                @else
                    @if(!empty($evidenceMap[$evalListId]))
                        <h3 class="text-base font-semibold text-purple-800 mb-2">หลักฐาน</h3>
                        <a href="{{ $evidenceMap[$evalListId] }}" target="_blank" class="text-blue-600 hover:underline">
                            {{ $evidenceMap[$evalListId] }}
                        </a>
                    @endif
                @endif
            </div>
        @endforeach

        {{-- Annotations/Notes Section --}}
        @php
            $hasAnnotations = collect($evaluationItems)
                ->where('is_evaluation_list', true)
                ->whereNotNull('subtitle')
                ->isNotEmpty();
        @endphp
        @if($hasAnnotations)
            <div class="p-8 space-y-6">
                <div class="bg-yellow-50 border border-yellow-200 px-6 py-4 rounded-xl">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-base font-medium text-yellow-800">หมายเหตุ</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ol class="list-decimal list-inside space-y-1">
                                    @foreach($evaluationItems as $item)
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
