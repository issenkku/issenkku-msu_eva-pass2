{{-- โมดัลสรุปคะแนนและยืนยันก่อนส่งแบบประเมินของ evaluator --}}
<div id="confirmationModal" class="fixed inset-0 z-[1200] hidden bg-gray-900 bg-opacity-75 p-3 pt-24 transition-opacity duration-300 flex items-start justify-center overflow-y-auto sm:p-6 sm:pt-28">
    <div id="modal-content" class="flex w-full max-w-3xl max-h-[calc(100vh-6rem)] sm:max-h-[calc(100vh-8rem)] scale-95 flex-col overflow-hidden rounded-2xl bg-white p-3 text-center opacity-0 shadow-xl transform transition-all duration-300 sm:p-4">
        <div class="hidden">
            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.049c.534-2.203 2.51-3.79 4.772-3.79s4.238 1.587 4.772 3.79M8.228 9.049L6.5 10.5m1.728-1.451L9.5 6.5m6.228 2.549L17.5 10.5m-1.728-1.451L14.5 6.5M12 21a9 9 0 110-18 9 9 0 010 18z"></path>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-gray-800">ยืนยันการส่งแบบประเมิน</h3>

        <div class="mb-2 mt-1">
            <p class="px-2 text-xs text-gray-500 sm:px-4 sm:text-sm">
                ตรวจสอบความครบถ้วนของข้อมูลก่อนส่งจริง
            </p>
        </div>

        <div class="mb-3 min-h-0 flex-1 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-left sm:mb-4 sm:px-4 sm:py-4">
            @php
                $visibleScoreCardCount = 1
                    + (int) ($scoreSummary['has_quantity'] ?? false)
                    + (int) ($scoreSummary['has_quality'] ?? false)
                    + (int) ($scoreSummary['has_support'] ?? false);

                $scoreCardGridClass = match ($visibleScoreCardCount) {
                    1 => 'sm:grid-cols-1',
                    2 => 'sm:grid-cols-2',
                    3 => 'sm:grid-cols-3',
                    default => 'sm:grid-cols-4',
                };
            @endphp
            <div class="mb-3 grid grid-cols-1 gap-2 sm:gap-3 {{ $scoreCardGridClass }}">
                @if ($scoreSummary['has_quantity'] ?? false)
                    <div class="rounded-lg border border-blue-100 bg-white p-3">
                        <div class="text-sm text-gray-500">คะแนนด้านปริมาณ</div>
                        <div id="modal-quantity-summary" class="mt-1 text-lg font-bold text-blue-900 sm:text-xl">0.00</div>
                    </div>
                @endif
                @if ($scoreSummary['has_quality'] ?? false)
                    <div class="rounded-lg border border-purple-100 bg-white p-3">
                        <div class="text-sm text-gray-500">คะแนนด้านคุณภาพ</div>
                        <div id="modal-quality-summary" class="mt-1 text-lg font-bold text-purple-900 sm:text-xl">0.00</div>
                    </div>
                @endif
                @if ($scoreSummary['has_support'] ?? false)
                    <div class="rounded-lg border border-amber-100 bg-white p-3">
                        <div class="text-sm text-gray-500">คะแนนสายสนับสนุน</div>
                        <div id="modal-support-summary" class="mt-1 text-lg font-bold text-amber-900 sm:text-xl">0.00</div>
                    </div>
                @endif
                <div class="rounded-lg border border-emerald-100 bg-white p-3">
                    <div class="text-sm text-gray-500">คะแนนรวม</div>
                    <div id="modal-total-summary" class="mt-1 text-lg font-bold text-emerald-700 sm:text-xl">0.00</div>
                </div>
            </div>

            <div class="space-y-4">
                @foreach ($categoryItems as $category)
                    @foreach ($category['evaluation_lists'] as $evaluationList)
                        @php
                            $qualitySubIds = collect($evaluationList['quality_items'])
                                ->flatMap(fn($main) => collect($main['sub_criterias'])->pluck('id'))
                                ->filter()
                                ->implode(',');
                        @endphp
                        <div
                            class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                            data-summary-list
                            data-list-id="{{ $evaluationList['id'] }}"
                            data-list-max="{{ $evaluationList['sum_score'] ?? 0 }}"
                            data-quality-sub-ids="{{ $qualitySubIds }}">
                            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
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

                            @if (count($evaluationList['quantity_items']) > 0)
                                <div class="mb-3">
                                    <div class="mb-2 text-xs font-bold uppercase tracking-wide text-green-700">Quantity</div>
                                    <div class="space-y-2">
                                        @foreach ($evaluationList['quantity_items'] as $mainCriteria)
                                            @foreach ($mainCriteria['sub_criterias'] as $subCriteria)
                                                <div
                                                    class="flex flex-col gap-2 rounded-lg border border-green-100 bg-green-50/60 px-3 py-2 sm:flex-row sm:items-center sm:justify-between"
                                                    data-summary-quantity-row
                                                    data-list-id="{{ $evaluationList['id'] }}"
                                                    data-sub-id="{{ $subCriteria['id'] }}"
                                                    data-score-a="{{ $subCriteria['score_a'] ?? 0 }}"
                                                    data-score-b="{{ $subCriteria['score_b'] ?? 0 }}">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-medium text-gray-800">{{ $subCriteria['name'] }}</div>
                                                        <div id="summary-quantity-status-{{ $subCriteria['id'] }}" class="mt-1 text-xs text-gray-500">ยังไม่มีข้อมูล</div>
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

                            @if (count($evaluationList['quality_items']) > 0)
                                <div>
                                    <div class="mb-2 text-xs font-bold uppercase tracking-wide text-purple-700">Quality</div>
                                    <div class="space-y-2">
                                        @foreach ($evaluationList['quality_items'] as $mainCriteria)
                                            @php
                                                $subIds = collect($mainCriteria['sub_criterias'])->pluck('id')->implode(',');
                                            @endphp
                                            <div
                                                class="flex flex-col gap-2 rounded-lg border border-purple-100 bg-purple-50/60 px-3 py-2 sm:flex-row sm:items-center sm:justify-between"
                                                data-summary-quality-main
                                                data-list-id="{{ $evaluationList['id'] }}"
                                                data-main-id="{{ $mainCriteria['id'] }}"
                                                data-sub-ids="{{ $subIds }}">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-800">{{ $mainCriteria['name'] }}</div>
                                                    <div id="summary-quality-status-{{ $mainCriteria['id'] }}" class="mt-1 text-xs text-gray-500">ยังไม่มีข้อมูล</div>
                                                </div>
                                                <div class="text-sm font-semibold text-purple-800">
                                                    <span id="summary-quality-score-{{ $mainCriteria['id'] }}">0.00</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (count($evaluationList['support_items']) > 0)
                                <div class="mt-3">
                                    <div class="mb-2 text-xs font-bold uppercase tracking-wide text-amber-700">Support</div>
                                    <div class="space-y-2">
                                        @foreach ($evaluationList['support_items'] as $supportItem)
                                            <div class="flex flex-col gap-2 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2 sm:flex-row sm:items-center sm:justify-between"
                                                data-summary-support-main
                                                data-list-id="{{ $evaluationList['id'] }}"
                                                data-support-id="{{ $supportItem['id'] }}"
                                                data-support-weight="{{ $supportItem['weight'] ?? 0 }}">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-800">{{ \App\Support\SafeHtml::plainText($supportItem['activity_name'] ?? '') }}</div>
                                                    <div id="summary-support-status-{{ $supportItem['id'] }}" class="mt-1 text-xs text-gray-500">ยังไม่มีข้อมูล</div>
                                                </div>
                                                <div class="text-sm font-semibold text-amber-800">
                                                    <span id="summary-support-score-{{ $supportItem['id'] }}">0.00</span>
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

        <div class="shrink-0 bg-white pt-1">
            <div class="flex flex-col space-y-2">
                <button id="confirmSubmitBtn" class="w-full rounded-lg bg-purple-600 px-4 py-2.5 font-semibold text-white transition-colors duration-200 hover:bg-purple-700">
                    ยืนยัน
                </button>
                <button id="cancelModalBtn" class="w-full rounded-lg bg-gray-100 px-4 py-2.5 font-semibold text-gray-700 transition-colors duration-200 hover:bg-gray-200">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>
