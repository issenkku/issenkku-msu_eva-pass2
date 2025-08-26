@props([
    'qualityItems' => [],
    'title' => 'ด้านคุณภาพ',
    'readonly' => false,
    'evidenceMap' => [] 
])

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="bg-purple-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
    </div>

    <div class="p-4 space-y-6">
        @php 
            // Group items by evaluation list
            $groupedItems = collect($qualityItems)->groupBy('evaluation_list_id');
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
                    @php
                        // Check if checkbox should be selected based on score
                        $hasScore = !empty($item['score']) && $item['score'] !== '' && $item['score'] !== null;
                        $shouldBeChecked = $hasScore || ($item['user_selected'] ?? false);
                    @endphp

                    <div class="ml-8 mt-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <div class="flex items-start flex-1">
                                @if(!$readonly)
                                    <input type="checkbox" 
                                        name="quality_criteria[{{ $item['sub_criteria_id'] }}]" 
                                        value="1"
                                        data-score="{{ $item['num_score'] ?? 0 }}"
                                        data-sub-criteria-id="{{ $item['sub_criteria_id'] }}"
                                        onchange="handleQualityCheckboxChange(this)"
                                        {{ $shouldBeChecked ? 'checked' : '' }}
                                        class="mt-1 mr-3 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                    <label class="text-base text-gray-800">
                                        {{ $item['title'] ?? "รายการย่อย" }}
                                    </label>
                                @else
                                    <input type="checkbox" 
                                        {{ $shouldBeChecked ? 'checked' : '' }}
                                        disabled
                                        class="mt-1 mr-3 h-4 w-4 text-purple-600 border-gray-300 rounded">
                                    <span class="text-base text-gray-800">
                                        {{ $item['title'] ?? "รายการย่อย" }}
                                    </span>
                                @endif
                            </div>

                            {{-- Score Input Field - Hidden from user --}}
                            <div class="w-full lg:w-1/4" style="display: none;">
                                @if(!$readonly)
                                    <div class="flex flex-col">
                                        <label class="text-xs text-gray-600 mb-1">คะแนน</label>
                                        <input type="hidden" 
                                            name="quality_list[{{ $item['sub_criteria_id'] }}][quality_sub_criteria_id]" 
                                            value="{{ $item['sub_criteria_id'] }}">
                                        <input type="hidden" 
                                            name="quality_list[{{ $item['sub_criteria_id'] }}][evaluation_list_id]" 
                                            value="{{ $evalListId }}">
                                        <input type="number" 
                                            id="quality-score-{{ $item['sub_criteria_id'] }}"
                                            name="quality_list[{{ $item['sub_criteria_id'] }}][score]" 
                                            value="{{ $hasScore ? $item['score'] : ($shouldBeChecked ? $item['num_score'] : '') }}"
                                            min="0" 
                                            max="100" 
                                            step="0.01"
                                            readonly
                                            class="form-input text-base w-full h-10 px-3 rounded border-gray-300 {{ $shouldBeChecked ? 'bg-white' : 'bg-gray-50 cursor-not-allowed' }} text-center" 
                                            placeholder="0.00">
                                    </div>
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
            $hasAnnotations = collect($qualityItems)
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

{{-- JavaScript for checkbox handling --}}
<script>
function handleQualityCheckboxChange(checkbox) {
    const subCriteriaId = checkbox.dataset.subCriteriaId;
    const score = parseFloat(checkbox.dataset.score) || 0;
    const scoreInput = document.getElementById(`quality-score-${subCriteriaId}`);
    
    if (scoreInput) {
        if (checkbox.checked) {
            // Add score when checked
            scoreInput.value = score;
            scoreInput.classList.remove('bg-gray-50', 'cursor-not-allowed');
            scoreInput.classList.add('bg-white');
        } else {
            // Remove score when unchecked
            scoreInput.value = '';
            scoreInput.classList.remove('bg-white');
            scoreInput.classList.add('bg-gray-50', 'cursor-not-allowed');
        }
        
        // Update total score (hidden from user but still calculated)
        updateQualityTotalScore();
    }
}

function updateQualityTotalScore() {
    const scoreInputs = document.querySelectorAll('input[name*="quality_list"][name*="[score]"]');
    let totalScore = 0;
    
    scoreInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        totalScore += value;
    });
    
    // Total score is calculated but not displayed to user
    // You can still access this value in your backend processing
}

function resetQualityForm() {
    // Reset all quality checkboxes
    const checkboxes = document.querySelectorAll('input[name*="quality_criteria"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        handleQualityCheckboxChange(checkbox);
    });
    
    // Reset all evidence inputs
    const evidenceInputs = document.querySelectorAll('input[name*="evidence_list"]');
    evidenceInputs.forEach(input => {
        if (input.type !== 'hidden') {
            input.value = '';
        }
    });
}

// Initialize checkbox states based on existing scores on page load
function initializeCheckboxStates() {
    const checkboxes = document.querySelectorAll('input[name*="quality_criteria"]');
    checkboxes.forEach(checkbox => {
        const subCriteriaId = checkbox.dataset.subCriteriaId;
        const scoreInput = document.getElementById(`quality-score-${subCriteriaId}`);
        
        if (scoreInput && checkbox.checked) {
            // If checkbox is checked and has a score, ensure proper styling
            scoreInput.classList.remove('bg-gray-50', 'cursor-not-allowed');
            scoreInput.classList.add('bg-white');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCheckboxStates();
    updateQualityTotalScore();
});
</script>