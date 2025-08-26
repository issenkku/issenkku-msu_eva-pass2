@props([
    'categoryItems' => [],
    'readonly' => false,
    'evidenceMap' => [] 
])

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
                    {{-- Evaluation List Container --}}
                    <div class="mb-8 bg-gray-50 rounded-lg border border-gray-300">
                        {{-- Evaluation List Header --}}
                        <div class="bg-gradient-to-r from-purple-100 to-blue-100 px-6 py-4 rounded-t-lg border-b border-gray-200">
                            <div class="flex items-center space-x-3">
                                <h2 class="text-xl font-bold text-gray-800">
                                    {{ $evaluationList['name'] }}
                                </h2>
                                @if(isset($evaluationList['sum_score']))
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        คะแนน {{ $evaluationList['sum_score'] }}
                                    </span>
                                @endif
                            </div>
                            
                            @if(!empty($evaluationList['annotation']))
                                <div class="flex items-center space-x-2 mt-1">
                                    <p class="text-sm text-gray-600">{{ $evaluationList['annotation'] }}</p>
                                </div>
                            @endif
                        </div>

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

                                        {{-- Quantity Sub Criteria --}}
                                        <div class="space-y-3 ml-6 mb-6">
                                            @foreach(collect($mainCriteria['sub_criterias'])->sortBy('sequence') as $subCriteria)
                                                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                                    <div class="flex flex-col lg:flex-row lg:items-center gap-4 mb-3">
                                                        <div class="flex items-start flex-1">
                                                            <div>
                                                                <label class="text-base text-gray-800">
                                                                    {{ $subCriteria['name'] }}
                                                                </label>
                                                                @if(!empty($subCriteria['description']))
                                                                    <div class="text-sm text-gray-500 mt-1">{{ $subCriteria['description'] }}</div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="w-full lg:w-2/3">
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
                                                                {{-- Score A --}}
                                                                <div class="flex flex-col h-full">
                                                                    <label class="text-sm font-semibold text-gray-700 text-center min-h-[40px] flex items-center justify-center mb-2">
                                                                        ค่าน้ำหนักคะแนน (A)
                                                                    </label>
                                                                    <input type="text" 
                                                                        name="quantity_list[{{ $subCriteria['id'] }}][score_A]" 
                                                                        value="{{ $subCriteria['score_a'] ?? '' }}"
                                                                        readonly
                                                                        class="bg-gray-100 text-center form-input text-base w-full h-11 px-3 rounded-lg border border-gray-300 shadow-sm">
                                                                </div>

                                                                {{-- Score B --}}
                                                                <div class="flex flex-col h-full">
                                                                    <label class="text-sm font-semibold text-gray-700 text-center min-h-[40px] flex items-center justify-center mb-2">
                                                                        หน่วยภาระงานมาตรฐาน (B)
                                                                    </label>
                                                                    <input type="text" 
                                                                        name="quantity_list[{{ $subCriteria['id'] }}][score_B]" 
                                                                        value="{{ $subCriteria['score_b'] ?? '' }}"
                                                                        readonly
                                                                        class="bg-gray-100 text-center form-input text-base w-full h-11 px-3 rounded-lg border border-gray-300 shadow-sm">
                                                                </div>

                                                                {{-- Score C --}}
                                                                <div class="flex flex-col h-full">
                                                                    <label class="text-sm font-semibold text-gray-700 text-center min-h-[40px] flex items-center justify-center mb-2">
                                                                        หน่วยภาระงานที่ทำได้ (C)
                                                                    </label>
                                                                    @if(!$readonly)
                                                                        <input type="number" step="1"
                                                                            name="quantity_list[{{ $subCriteria['id'] }}][score_C]" 
                                                                            value="{{ $subCriteria['tor_compliant'] ?? '' }}"
                                                                            data-sub-criteria-id="{{ $subCriteria['id'] }}"
                                                                            oninput="calculateScoreD(this)"
                                                                            class="bg-white text-center form-input text-base w-full h-11 px-3 rounded-lg border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                                                                            placeholder="ใส่ค่า C">
                                                                    @else
                                                                        <input type="text" 
                                                                            name="quantity_list[{{ $subCriteria['id'] }}][tor_compliant]" 
                                                                            value="{{ $subCriteria['tor_compliant'] ?? '' }}"
                                                                            readonly
                                                                            class="bg-gray-100 text-center form-input text-base w-full h-11 px-3 rounded-lg border border-gray-300 shadow-sm"
                                                                            placeholder="0">
                                                                    @endif
                                                                    
                                                                </div>

                                                                {{-- Score D --}}
                                                                <div class="flex flex-col h-full">
                                                                    <label class="text-sm font-semibold text-gray-700 text-center min-h-[40px] flex items-center justify-center mb-2">
                                                                        คำนวณหาค่าน้ำหนักคะแนน
                                                                    </label>
                                                                    <input type="text" 
                                                                        name="quantity_list[{{ $subCriteria['id'] }}][score_D]" 
                                                                        id="score-D-{{ $subCriteria['id'] }}"
                                                                        value="{{ $subCriteria['score_d'] ?? '' }}"
                                                                        readonly
                                                                        class="bg-gray-100 text-center form-input text-base w-full h-11 px-3 rounded-lg border border-gray-300 shadow-sm"
                                                                        placeholder="0">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(!empty($subCriteria['score_description']))
                                                        <h4 class="text-base font-semibold text-gray-800 flex items-center border-t border-gray-200 pt-3">
                                                            คำอธิบายหลักฐาน
                                                        </h4>
                                                        <div class="text-sm text-gray-500 mt-1">{{ $subCriteria['score_description'] }}</div>
                                                    @else
                                                        <div class="text-sm text-gray-500 mt-1">ไม่มีคำอธิบายหลักฐาน</div>
                                                    @endif
                                                </div>

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
                                <div class="mb-8">
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                                        <h3 class="text-lg font-semibold text-purple-800 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            </svg>
                                            ด้านคุณภาพ
                                        </h3>
                                    </div>

                                    @foreach($evaluationList['quality_items'] as $mainCriteria)
                                        {{-- Main Criteria Header --}}
                                        <div class="mb-4 border-l-4 border-purple-400 pl-4 py-2 bg-purple-50">
                                            <h4 class="text-base font-semibold text-gray-800">
                                                {{ $mainCriteria['name'] }}
                                            </h4>
                                            @if(!empty($mainCriteria['tooltips']))
                                                <div class="text-sm text-gray-500 mt-1">{!! $mainCriteria['tooltips'] !!}</div>
                                            @endif
                                        </div>

                                        {{-- Quality Sub Criteria --}}
                                        <div class="space-y-3 ml-6 mb-6">
                                            @foreach(collect($mainCriteria['sub_criterias'])->sortBy('sequence') as $subCriteria)
                                                @php
                                                    $hasScore = !empty($subCriteria['score']) && $subCriteria['score'] !== '' && $subCriteria['score'] !== null;
                                                    $shouldBeChecked = $hasScore || ($subCriteria['user_selected'] ?? false);
                                                @endphp

                                                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                                    <div class="flex flex-col gap-2 p-3 border-b border-gray-100 lg:flex-row lg:items-center lg:justify-between">
                                                        <!-- Name + Badge -->
                                                        <div class="flex flex-col gap-1 flex-1 lg:flex-row lg:items-center lg:gap-3">
                                                            <span class="text-base text-gray-800 font-medium">
                                                                {{ $subCriteria['name'] }}
                                                            </span>
                                                            <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full w-max">
                                                                คะแนน {{ $subCriteria['num_score'] }}
                                                            </span>
                                                        </div>

                                                        <!-- Input -->
                                                        @if(!$readonly)
                                                            <div class="w-full lg:w-1/4">
                                                                <input 
                                                                    type="number" step="0.01" min="0" 
                                                                    max="{{ $subCriteria['num_score'] }}"
                                                                    name="quality_list[{{ $subCriteria['id'] }}][score]" 
                                                                    value="{{ $subCriteria['score'] ?? '' }}"
                                                                    class="bg-white text-center form-input text-base w-full h-11 px-3 rounded-lg border border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                                                    placeholder="ใส่คะแนน">
                                                            </div>
                                                        @else
                                                            <div class="w-full lg:w-1/4">
                                                                <input 
                                                                    type="number" step="0.01" min="0" 
                                                                    max="{{ $subCriteria['num_score'] }}"
                                                                    name="quality_list[{{ $subCriteria['id'] }}][score]" 
                                                                    value="{{ $subCriteria['score'] ?? '' }}"
                                                                    readonly
                                                                    class="bg-white text-center form-input text-base w-full h-11 px-3 rounded-lg border border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                                                    placeholder="ไม่มีคะแนน">
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Hidden Score Input --}}
                                                    @if(!$readonly)
                                                        <input type="hidden" 
                                                            name="quality_list[{{ $subCriteria['id'] }}][quality_sub_criteria_id]" 
                                                            value="{{ $subCriteria['id'] }}">
                                                    @endif

                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Evidence Section for Evaluation List --}}
                            <div class="pt-6 border-t border-gray-200">
                                <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    หลักฐาน
                                </h3>
                                @if(!empty($evidenceMap[$evaluationList['id']]))
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <a href="{{ $evidenceMap[$evaluationList['id']] }}" target="_blank" class="text-blue-600 hover:underline break-all">
                                            {{ $evidenceMap[$evaluationList['id']] }}
                                        </a>
                                    </div>
                                @else
                                    <div class="text-gray-500 mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        ไม่มีหลักฐานแนบ
                                    </div>
                                @endif
                            </div>
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
                // Quality
                foreach($evalList['quality_items'] as $mainCriteria) {
                    foreach($mainCriteria['sub_criterias'] as $subCriteria) {
                        $totalQualityScore += floatval($subCriteria['score'] ?? 0);
                    }
                }
            }
        }
        $totalScore = $totalQuantityScore + $totalQualityScore;
    @endphp
    <div class="bg-blue-50 border border-blue-200 rounded-2xl shadow-sm p-6 mt-6">
        <h3 class="text-xl font-bold text-blue-900 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2h6v2m-7 4h8a2 2 0 002-2v-5a2 2 0 00-2-2h-1V7a4 4 0 10-8 0v5H9a2 2 0 00-2 2v5a2 2 0 002 2z"/>
            </svg>
            สรุปคะแนนรวม
        </h3>

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

        <div class="mt-5 p-4 bg-white rounded-xl shadow-inner flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <span class="text-lg font-semibold text-blue-700">คะแนนรวมทั้งหมด</span>
            <span id="total-summary" class="text-2xl font-bold text-blue-900">{{ number_format($totalScore, 2) }}</span>
        </div>
    </div>
</div>

{{-- JavaScript for Quality Checkbox Handling --}}
<script>
function calculateScoreD(input) {
    const subCriteriaId = input.dataset.subCriteriaId;
    const parent = input.closest('.grid'); 

    const scoreA = parseFloat(parent.querySelector(`input[name="quantity_list[${subCriteriaId}][score_A]"]`).value) || 0;
    const scoreB = parseFloat(parent.querySelector(`input[name="quantity_list[${subCriteriaId}][score_B]"]`).value) || 1;
    const scoreC = parseFloat(input.value) || 0;

    const scoreD = (scoreA * scoreC) / scoreB;

    document.getElementById(`score-D-${subCriteriaId}`).value = scoreD ? scoreD.toFixed(2) : '';
}

function recalculateSummaryScores() {
    // Quantity: sum all score_D inputs
    let quantitySum = 0;
    document.querySelectorAll('input[name^="quantity_list"][name$="[score_D]"]').forEach(input => {
        let val = parseFloat(input.value);
        if (!isNaN(val)) quantitySum += val;
    });

    // Quality: sum all quality score inputs
    let qualitySum = 0;
    document.querySelectorAll('input[name^="quality_list"][name$="[score]"]').forEach(input => {
        let val = parseFloat(input.value);
        if (!isNaN(val)) qualitySum += val;
    });

    // Update the summary fields
    document.getElementById('quantity-summary').textContent = quantitySum.toFixed(2);
    document.getElementById('quality-summary').textContent = qualitySum.toFixed(2);
    document.getElementById('total-summary').textContent = (quantitySum + qualitySum).toFixed(2);
}

// Listen for changes on all relevant inputs
document.addEventListener('DOMContentLoaded', function() {
    // Existing code...

    // Attach event listeners for real-time summary
    document.querySelectorAll(
        'input[name^="quantity_list"][name$="[score_C]"], input[name^="quantity_list"][name$="[score_D]"], input[name^="quality_list"][name$="[score]"]'
    ).forEach(input => {
        input.addEventListener('input', recalculateSummaryScores);
    });

    // Initial calculation
    recalculateSummaryScores();
});

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
</script>