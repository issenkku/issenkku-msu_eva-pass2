@props([
    'evaluationItems' => [],
    'title' => 'ด้านปริมาณ',
    'readonly' => false,
    'evidenceMap' => [] 
])

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="bg-purple-100 px-6 py-4">
        <h2 class="text-xl font-semibold text-gray-800">{{ $title }}</h2>
    </div>

    <div class="p-4 space-y-6">
        @php 
            $evalCount = 0; 
            $evaluationListIds = collect($evaluationItems)
                ->where('is_evaluation_list', false)
                ->pluck('evaluation_list_id')
                ->unique()
                ->values();
        @endphp

        @foreach($evaluationItems as $index => $item)
            @php
                $isEvaluationList = $item['is_evaluation_list'] ?? false;
                $isMainCriteria = ($item['is_main'] ?? false) && !$isEvaluationList;
                $isSubCriteria = !($item['is_main'] ?? true);
            @endphp

            @if($isEvaluationList)
                @if($evalCount > 0)
                    <hr class="border-t border-gray-300 my-6"> {{-- Separator between evaluation sections --}}
                @endif

                <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                    <h3 class="text-lg font-bold text-purple-800">
                        {{ $item['title'] ?? "รายการที่ " . ($evalCount + 1) }}
                    </h3>
                    @if(!empty($item['subtitle']))
                        <p class="text-sm text-gray-600 mt-1">{{ $item['subtitle'] }}</p>
                    @endif
                </div>

                @php $evalCount++; @endphp
            @endif

            @if($isMainCriteria)
                <div class="ml-4 mt-6 border-t border-gray-200 pt-4">
                    <div class="bg-white border-l-4 border-purple-400 pl-4 py-2">
                        <h4 class="text-base font-semibold text-gray-800">
                            {{ $item['title'] ?? "หลักเกณฑ์หลัก" }}
                        </h4>
                        @if(!empty($item['subtitle']))
                            <p class="text-sm text-gray-500 mt-1">{{ $item['subtitle'] }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if($isSubCriteria)
                <div class="ml-8 mt-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <div class="flex items-start flex-1">
                            @if(!$readonly)
                                <label class="text-base text-gray-800">
                                    {{ $item['title'] ?? "รายการย่อย" }}
                                    {{-- Score removed from label --}}
                                </label>
                            @else
                                <span class="text-base text-gray-800">
                                    {{ $item['title'] ?? "รายการย่อย" }}
                                    {{-- Score removed from span --}}
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
                                    name="quantity_list[{{ $index }}][score_C]" 
                                    value="{{ $item['tor_compliant'] ?? '' }}"
                                    class="form-input text-base w-full h-10 ml-2 px-3 rounded border-gray-300 focus:ring-purple-500 focus:border-purple-500" 
                                    placeholder="ใส่ข้อมูล">
                                
                                <input type="hidden" 
                                    name="quantity_list[{{ $index }}][quantity_sub_criteria_id]" 
                                    value="{{ $item['sub_criteria_id'] ?? $item['quantity_sub_criteria_id'] ?? $item['id'] }}">
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Evidence inputs per evaluation list at the bottom --}}
        @if(!$readonly)
            <div class="ml-5 mr-5 mt-10 pt-6 border-t border-purple-200">
                <h3 class="text-lg font-semibold text-purple-800 mb-4">แนบลิงก์หลักฐาน</h3>
                @foreach($evaluationListIds as $evalListId)
                    <div class="mb-4">
                        <input type="url" 
                            name="evidence_list[{{ $evalListId }}][link]" 
                            value="{{ $evidenceMap[$evalListId] ?? '' }}"
                            class="form-input text-base w-full h-10 px-3 rounded border-gray-300 bg-gray-100 focus:ring-purple-500 focus:border-purple-500" 
                            placeholder="ใส่ลิงก์หลักฐาน">

                        <input type="hidden" 
                            name="evidence_list[{{ $evalListId }}][evaluation_list_id]" 
                            value="{{ $evalListId }}">
                    </div>
                @endforeach
            </div>
        @else
            {{-- Display evidence in readonly mode --}}
            <div class="ml-5 mr-5 mt-10 pt-6 border-t border-purple-200">
                <h3 class="text-lg font-semibold text-purple-800 mb-4">หลักฐาน</h3>
                @foreach($evaluationListIds as $evalListId)
                    @if(!empty($evidenceMap[$evalListId]))
                        <div class="mb-4">
                            <div class="text-base text-gray-800">
                                <a href="{{ $evidenceMap[$evalListId] }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ $evidenceMap[$evalListId] }}
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

    </div>
</div>