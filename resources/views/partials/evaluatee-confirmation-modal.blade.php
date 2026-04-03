<!-- Confirmation Modal -->
{{-- Submission summary modal for the evaluatee form --}}
<div id="confirmationModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-start justify-center overflow-y-auto p-3 pt-24 sm:p-6 sm:pt-28 hidden transition-opacity duration-300" style="z-index: 1200;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[calc(100vh-6rem)] sm:max-h-[calc(100vh-8rem)] p-3 sm:p-4 text-center transform transition-all duration-300 scale-95 opacity-0 flex flex-col overflow-hidden" id="modal-content">
        <div class="hidden">
            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.049c.534-2.203 2.51-3.79 4.772-3.79s4.238 1.587 4.772 3.79M8.228 9.049L6.5 10.5m1.728-1.451L9.5 6.5m6.228 2.549L17.5 10.5m-1.728-1.451L14.5 6.5M12 21a9 9 0 110-18 9 9 0 010 18z"></path>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-gray-800">ยืนยันการส่งแบบประเมิน</h3>

        <div class="mt-1 mb-2">
            <p class="text-xs sm:text-sm text-gray-500 px-2 sm:px-4">
                ตรวจสอบความครบถ้วนของข้อมูลก่อนส่งจริง
            </p>
        </div>

        <div class="text-left border border-gray-200 rounded-xl bg-gray-50 flex-1 min-h-0 overflow-y-auto px-3 sm:px-4 py-3 sm:py-4 mb-3 sm:mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 mb-3">
                <div class="bg-white rounded-lg border border-blue-100 p-3">
                    <div class="text-sm text-gray-500">คะแนนด้านปริมาณ</div>
                    <div id="modal-quantity-summary" class="text-lg sm:text-xl font-bold text-blue-900 mt-1">0.00</div>
                </div>
                <div class="bg-white rounded-lg border border-purple-100 p-3">
                    <div class="text-sm text-gray-500">คะแนนด้านคุณภาพ</div>
                    <div id="modal-quality-summary" class="text-lg sm:text-xl font-bold text-purple-900 mt-1">0.00</div>
                </div>
                <div class="bg-white rounded-lg border border-emerald-100 p-3">
                    <div class="text-sm text-gray-500">คะแนนรวม</div>
                    <div id="modal-total-summary" class="text-lg sm:text-xl font-bold text-emerald-700 mt-1">0.00</div>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($categoryItems as $category)
                    @foreach($category['evaluation_lists'] as $evaluationList)
                        @php
                            $qualitySubIds = collect($evaluationList['quality_items'])
                                ->flatMap(fn($main) => collect($main['sub_criterias'])->pluck('id'))
                                ->filter()
                                ->implode(',');
                        @endphp
                        <div
                            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm"
                            data-summary-list
                            data-list-id="{{ $evaluationList['id'] }}"
                            data-list-max="{{ $evaluationList['sum_score'] ?? 0 }}"
                            data-quality-sub-ids="{{ $qualitySubIds }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $evaluationList['name'] }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="summary-list-status-{{ $evaluationList['id'] }}" class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                        ยังไม่มีข้อมูล
                                    </span>
                                    <span class="text-sm font-semibold text-gray-700">
                                        รวม <span id="summary-list-score-{{ $evaluationList['id'] }}">0.00</span>
                                    </span>
                                </div>
                            </div>

                            @if(count($evaluationList['quantity_items']) > 0)
                                <div class="mb-3">
                                    <div class="text-xs font-bold tracking-wide text-green-700 uppercase mb-2">Quantity</div>
                                    <div class="space-y-2">
                                        @foreach($evaluationList['quantity_items'] as $mainCriteria)
                                            @foreach($mainCriteria['sub_criterias'] as $subCriteria)
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg border border-green-100 bg-green-50/60 px-3 py-2"
                                                    data-summary-quantity-row
                                                    data-list-id="{{ $evaluationList['id'] }}"
                                                    data-sub-id="{{ $subCriteria['id'] }}"
                                                    data-score-a="{{ $subCriteria['score_a'] ?? 0 }}"
                                                    data-score-b="{{ $subCriteria['score_b'] ?? 0 }}">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-medium text-gray-800">{{ $subCriteria['name'] }}</div>
                                                        <div id="summary-quantity-status-{{ $subCriteria['id'] }}" class="text-xs text-gray-500 mt-1">ยังไม่มีข้อมูล</div>
                                                    </div>
                                                    <div class="text-sm font-semibold text-green-800">
                                                        <span id="summary-quantity-score-{{ $subCriteria['id'] }}">0.00</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($evaluationList['quality_items']) > 0)
                                <div>
                                    <div class="text-xs font-bold tracking-wide text-purple-700 uppercase mb-2">Quality</div>
                                    <div class="space-y-2">
                                        @foreach($evaluationList['quality_items'] as $mainCriteria)
                                            @php
                                                $subIds = collect($mainCriteria['sub_criterias'])->pluck('id')->implode(',');
                                            @endphp
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg border border-purple-100 bg-purple-50/60 px-3 py-2"
                                                data-summary-quality-main
                                                data-list-id="{{ $evaluationList['id'] }}"
                                                data-main-id="{{ $mainCriteria['id'] }}"
                                                data-sub-ids="{{ $subIds }}">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-800">{{ $mainCriteria['name'] }}</div>
                                                    <div id="summary-quality-status-{{ $mainCriteria['id'] }}" class="text-xs text-gray-500 mt-1">ยังไม่มีข้อมูล</div>
                                                </div>
                                                <div class="text-sm font-semibold text-purple-800">
                                                    <span id="summary-quality-score-{{ $mainCriteria['id'] }}">0.00</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="flex flex-col space-y-2 pt-1 bg-white shrink-0">
            <button id="confirmSubmitBtn" class="w-full px-4 py-2.5 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors duration-200">
                ยืนยัน
            </button>
            <button id="cancelModalBtn" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-200">
                ยกเลิก
            </button>
        </div>
    </div>
</div>
