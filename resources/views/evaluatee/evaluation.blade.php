@extends('layouts.app')

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
{{-- บล็อกเนื้อหา --}}
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    {{--  --}}
    <div class="page-header">
        <h1>แบบประเมินผลงาน</h1>
        {{-- <p class="version">เวอร์ชัน: {{ $versionName }}</p> --}}
    </div>

    <x-evaluate-report-card 
        :reportName="$reportName"
        :reportDescription="$reportDescription"
        :assessmentType="$assessmentType"
        :reportComment="$reportComment"
    />

    <x-evaluate-profile-card 
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    {{-- ฟอร์ม --}}
    <form id="evaluationForm" method="POST" action="{{ route('evaluation_score.store', $report->id) }}">
        @csrf

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Wrap all inputs in fieldset --}}
        @if($readonly)
            <fieldset disabled>
        @endif

        @if($readonly)
            <x-unified-evaluator
                :categoryItems="$categoryItems"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
                :qualityEvidenceMap="$qualityEvidenceMap"
                :workloadMap="$workloadMap"
            />
        @else
            <x-unified-evaluation
                :categoryItems="$categoryItems"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
                :qualityEvidenceMap="$qualityEvidenceMap"
                :report="$report"
                :workloadMap="$workloadMap"
            />
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
            $maxQualityScore = 0;
            foreach ($categoryItems as $category) {
                foreach ($category['evaluation_lists'] as $evalList) {
                    if (!empty($evalList['quality_items'])) {
                        $maxQualityScore += floatval($evalList['sum_score'] ?? 0);
                    }
                }
            }
            if ($maxQualityScore > 0 && $totalQualityScore > $maxQualityScore) {
                $totalQualityScore = $maxQualityScore;
            }
            $totalScore = $totalQuantityScore + $totalQualityScore;
        @endphp

        {{-- Summary Score and Comments --}}
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
    
            <div class="bg-purple-50 border border-blue-200 rounded-lg p-6 mt-8">
                <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    ความคิดเห็นจากผู้ประเมิน
                </h3>
                
                @if(isset($report->comment) && !empty($report->comment))
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($report->comment)) !!}
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
                        <p class="text-gray-500 italic">ไม่มีความคิดเห็นเพิ่มเติม</p>
                    </div>
                @endif
            </div>

        <input type="hidden" name="status" id="formStatus" value="Draft">

        @if($readonly)
            </fieldset>
        @endif

        {{-- บล็อกเนื้อหา --}}
        <div class="flex justify-center gap-4 mt-8">
            <x-button 
                    type= defualt 
                    text="ย้อนกลับ" 
                    icon="fas fa-arrow-left"
                    href="/evaluatee-dashboard" />

            @unless($readonly)
                <x-button 
                    type="secondary"
                    buttonType="submit" 
                    text="บันทึกร่าง" 
                    onclick="setFormStatus('Draft')" 
                    icon="fas fa-save" />
                <x-button 
                    type="primary"
                    buttonType="button" 
                    text="ส่งแบบประเมิน"
                    id="openModalBtn"
                    icon="fas fa-paper-plane" />
            @endunless
        </div>
    </form>
</div>

<!-- Loading Overlay -->
{{-- บล็อกเนื้อหา --}}
<div id="loading_overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
    {{-- บล็อกเนื้อหา --}}
    <div class="bg-white p-6 rounded-lg shadow-xl text-center">
        {{-- บล็อกเนื้อหา --}}
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-700 text-lg">กำลังส่งข้อมูล กรุณารอสักครู่...</p>
    </div>
</div>

<!-- Confirmation Modal -->
{{-- บล็อกเนื้อหา --}}
<div id="confirmationModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-start justify-center overflow-y-auto p-3 pt-24 sm:p-6 sm:pt-28 hidden transition-opacity duration-300" style="z-index: 1200;">
    {{--  --}}
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[calc(100vh-6rem)] sm:max-h-[calc(100vh-8rem)] p-3 sm:p-4 text-center transform transition-all duration-300 scale-95 opacity-0 flex flex-col overflow-hidden" id="modal-content">
        <!-- Icon -->
        {{-- บล็อกเนื้อหา --}}
        <div class="hidden">
            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.049c.534-2.203 2.51-3.79 4.772-3.79s4.238 1.587 4.772 3.79M8.228 9.049L6.5 10.5m1.728-1.451L9.5 6.5m6.228 2.549L17.5 10.5m-1.728-1.451L14.5 6.5M12 21a9 9 0 110-18 9 9 0 010 18z"></path>
            </svg>
        </div>
        
        <!-- Title -->
        <h3 class="text-xl font-bold text-gray-800">ยืนยันการส่งแบบประเมิน</h3>
        
        <!-- Description -->
        {{-- บล็อกเนื้อหา --}}
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
                                    {{-- @if(!empty($evaluationList['annotation']))
                                        <div class="text-xs text-gray-500 mt-1">{{ $evaluationList['annotation'] }}</div>
                                    @endif --}}
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

        <!-- Buttons -->
        {{-- บล็อกเนื้อหา --}}
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

<!-- Success Modal -->
{{-- บล็อกเนื้อหา --}}
<div id="success_modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
    {{--  --}}
    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="success-modal-content">
        {{-- บล็อกเนื้อหา --}}
        <div class="text-center">
            <div class="bg-green-100 rounded-full p-4 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">ส่งข้อมูลสำเร็จ</h3>
            <p class="text-gray-600 mb-6">ส่งข้อมูลการประเมินเรียบร้อยแล้ว</p>
            <p class="text-gray-500 text-sm mb-6">กำลังเปลี่ยนเส้นทางใน <span id="countdown">3</span> วินาที...</p>
            <div class="flex justify-center space-x-4">
                <button id="redirectNowBtn" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    ไปหน้าแดชบอร์ดทันที
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mobile-friendly spacing -->
<style>
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }
    
    .max-w-4xl {
        max-width: 100%;
        padding: 0 1rem;
    }
}
/* Header Styles */
.page-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 24px;
    border-bottom: 3px solid #f3f4f6;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}
</style>

<script>
function setFormStatus(status) {
    document.getElementById('formStatus').value = status;
}

document.addEventListener('DOMContentLoaded', function() {
    const evaluationForm = document.getElementById('evaluationForm');
    const openModalBtn = document.getElementById('openModalBtn');
    const confirmationModal = document.getElementById('confirmationModal');
    const modalContent = document.getElementById('modal-content');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    const loadingOverlay = document.getElementById('loading_overlay');

    if (!openModalBtn || !confirmationModal) {
        return;
    }

    function isValidUrl(value) {
        try {
            const url = new URL(value);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }

    function validateForm(isSubmit = false) {
        const errors = [];
        
        // Get all quantity inputs
        const quantityInputs = document.querySelectorAll('input[name^="quantity_list"][name$="[score_C]"]');
        
        quantityInputs.forEach(input => {
            const value = input.value.trim();
            if (value !== '') {
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < 0) {
                    errors.push('คะแนนด้านปริมาณต้องเป็นตัวเลขที่ไม่ติดลบ');
                }
            }
        });
        
        // Get all quality inputs
        const qualityInputs = document.querySelectorAll('input[name^="quality_list"][name$="[score]"]');
        
        qualityInputs.forEach(input => {
            const value = input.value.trim();
            if (value !== '') {
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < 0) {
                    errors.push('คะแนนด้านคุณภาพต้องเป็นตัวเลขที่ไม่ติดลบ');
                }
            }
        });
        
        // Validate evidence links (if any)
        const evidenceInputs = document.querySelectorAll('input[name^="evidence_list"][name$="[links][]"]');
        evidenceInputs.forEach(input => {
            const value = input.value.trim();
            if (value !== '' && !isValidUrl(value)) {
                errors.push('ลิงก์หลักฐานไม่ถูกต้อง กรุณาตรวจสอบ URL');
            }
        });
        
        if (isSubmit) {
            document.querySelectorAll('[id^="evidence-links-quality-"][data-require-evidence="1"]').forEach(container => {
                const mainCriteriaName = container.dataset.mainCriteriaName || 'เกณฑ์ที่เลือก';
                const qualityMainCard = container.closest('details');
                const hasSelectedScore = qualityMainCard
                    ? Array.from(qualityMainCard.querySelectorAll('input[name^="quality_list"][name$="[score]"]')).some(input => input.value.trim() !== '')
                    : false;

                if (!hasSelectedScore) {
                    return;
                }

                const hasEvidence = Array.from(container.querySelectorAll('input[type="url"]'))
                    .some(input => input.value.trim() !== '');

                if (!hasEvidence) {
                    errors.push(`กรุณาแนบหลักฐานสำหรับเกณฑ์ "${mainCriteriaName}"`);
                }
            });
        }

        return errors;
    }

    function showValidationErrors(errors) {
        // Remove existing error alerts
        const existingAlert = document.querySelector('.validation-error-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        if (errors.length === 0) return;
        
        // Create error alert
        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error-alert bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorDiv.setAttribute('role', 'alert');
        
        const errorList = document.createElement('ul');
        errorList.className = 'list-disc list-inside';
        
        // Remove duplicates
        const uniqueErrors = [...new Set(errors)];
        
        uniqueErrors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        
        errorDiv.appendChild(errorList);
        
        // Insert after form opening tag
        evaluationForm.insertBefore(errorDiv, evaluationForm.firstChild);
        
        // Scroll to error
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function setBadgeState(element, hasData, readyText = 'มีข้อมูลแล้ว', emptyText = 'ยังไม่มีข้อมูล') {
        if (!element) return;
        element.textContent = hasData ? readyText : emptyText;
        element.className = hasData
            ? 'inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700'
            : 'inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700';
    }

    function updateSubmissionSummary() {
        const quantitySummary = parseFloat(document.getElementById('quantity-summary')?.textContent || '0') || 0;
        const qualitySummary = parseFloat(document.getElementById('quality-summary')?.textContent || '0') || 0;
        const totalSummary = parseFloat(document.getElementById('total-summary')?.textContent || '0') || 0;

        const modalQuantity = document.getElementById('modal-quantity-summary');
        const modalQuality = document.getElementById('modal-quality-summary');
        const modalTotal = document.getElementById('modal-total-summary');

        if (modalQuantity) modalQuantity.textContent = quantitySummary.toFixed(2);
        if (modalQuality) modalQuality.textContent = qualitySummary.toFixed(2);
        if (modalTotal) modalTotal.textContent = totalSummary.toFixed(2);

        document.querySelectorAll('[data-summary-quantity-row]').forEach(row => {
            const subId = row.dataset.subId;
            const scoreA = parseFloat(row.dataset.scoreA || '0') || 0;
            const scoreB = parseFloat(row.dataset.scoreB || '0') || 0;
            const scoreCInput = document.querySelector(`input[name="quantity_list[${subId}][score_C]"]`);
            const descriptionInput = document.querySelector(`input[name="quantity_list[${subId}][description]"]`);
            const scoreC = parseFloat(scoreCInput?.value || '') || 0;
            const description = (descriptionInput?.value || '').trim();
            const hasData = (scoreCInput?.value || '').trim() !== '' || description !== '';
            const scoreD = hasData && scoreB !== 0 ? (scoreA * scoreC) / scoreB : 0;

            const statusEl = document.getElementById(`summary-quantity-status-${subId}`);
            const scoreEl = document.getElementById(`summary-quantity-score-${subId}`);

            if (statusEl) {
                statusEl.textContent = hasData ? 'มีข้อมูลแล้ว' : 'ยังไม่มีข้อมูล';
                statusEl.className = hasData ? 'text-xs text-emerald-700 mt-1' : 'text-xs text-gray-500 mt-1';
            }
            if (scoreEl) {
                scoreEl.textContent = scoreD.toFixed(2);
            }
        });

        document.querySelectorAll('[data-summary-quality-main]').forEach(row => {
            const mainId = row.dataset.mainId;
            const subIds = (row.dataset.subIds || '')
                .split(',')
                .map(id => id.trim())
                .filter(Boolean);

            let total = 0;
            let selectedCount = 0;
            subIds.forEach(subId => {
                const scoreInput = document.getElementById(`quality-score-${subId}`);
                const value = parseFloat(scoreInput?.value || '');
                if (!isNaN(value)) {
                    total += value;
                    selectedCount++;
                }
            });

            const hasData = selectedCount > 0;
            const statusEl = document.getElementById(`summary-quality-status-${mainId}`);
            const scoreEl = document.getElementById(`summary-quality-score-${mainId}`);

            if (statusEl) {
                statusEl.textContent = hasData ? `มีข้อมูลแล้ว ${selectedCount} รายการ` : 'ยังไม่มีข้อมูล';
                statusEl.className = hasData ? 'text-xs text-emerald-700 mt-1' : 'text-xs text-gray-500 mt-1';
            }
            if (scoreEl) {
                scoreEl.textContent = total.toFixed(2);
            }
        });

        document.querySelectorAll('[data-summary-list]').forEach(listEl => {
            const listId = listEl.dataset.listId;
            const listMax = parseFloat(listEl.dataset.listMax || '0') || 0;
            const qualitySubIds = (listEl.dataset.qualitySubIds || '')
                .split(',')
                .map(id => id.trim())
                .filter(Boolean);

            let quantityTotal = 0;
            let hasAnyData = false;

            listEl.querySelectorAll('[data-summary-quantity-row]').forEach(row => {
                const subId = row.dataset.subId;
                const scoreA = parseFloat(row.dataset.scoreA || '0') || 0;
                const scoreB = parseFloat(row.dataset.scoreB || '0') || 0;
                const scoreCInput = document.querySelector(`input[name="quantity_list[${subId}][score_C]"]`);
                const descriptionInput = document.querySelector(`input[name="quantity_list[${subId}][description]"]`);
                const scoreC = parseFloat(scoreCInput?.value || '') || 0;
                const description = (descriptionInput?.value || '').trim();
                const hasData = (scoreCInput?.value || '').trim() !== '' || description !== '';
                if (hasData) {
                    hasAnyData = true;
                    quantityTotal += scoreB !== 0 ? (scoreA * scoreC) / scoreB : 0;
                }
            });

            let qualityTotal = 0;
            qualitySubIds.forEach(subId => {
                const scoreInput = document.getElementById(`quality-score-${subId}`);
                const value = parseFloat(scoreInput?.value || '');
                if (!isNaN(value)) {
                    hasAnyData = true;
                    qualityTotal += value;
                }
            });

            if (listMax > 0 && qualityTotal > listMax) {
                qualityTotal = listMax;
            }

            const total = quantityTotal + qualityTotal;
            const statusEl = document.getElementById(`summary-list-status-${listId}`);
            const scoreEl = document.getElementById(`summary-list-score-${listId}`);

            setBadgeState(statusEl, hasAnyData);
            if (scoreEl) {
                scoreEl.textContent = total.toFixed(2);
            }
        });
    }
    
    // ===== Modal Animation Functions =====
    function openModal(modal, content) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    
    function closeModal(modal, content) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function showLoading() {
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoading() {
        loadingOverlay.classList.add('hidden');
    }

    // ===== Event Listeners =====
    
    // เปิด confirmation modal
    evaluationForm.addEventListener('submit', function(e) {
        const status = document.getElementById('formStatus').value;
        
        if (status === 'Draft') {
            // For draft, just do basic validation
            const errors = validateForm(false);
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                hideLoading();
            }
        }
    });
    
    openModalBtn.addEventListener('click', (e) => {
        e.preventDefault();

        const errors = validateForm(true);
        
        if (errors.length > 0) {
            showValidationErrors(errors);
            return;
        }

        if (typeof recalculateSummaryScores === 'function') {
            recalculateSummaryScores();
        }
        updateSubmissionSummary();
        
        openModal(confirmationModal, modalContent);
    });

    // ปิด confirmation modal
    cancelModalBtn.addEventListener('click', () => {
        closeModal(confirmationModal, modalContent);
    });

    // ปิด modal เมื่อคลิกพื้นหลัง
    confirmationModal.addEventListener('click', function(event) {
        if (event.target === confirmationModal) {
            closeModal(confirmationModal, modalContent);
        }
    });

    // ยืนยันการส่งแบบประเมิน
    confirmSubmitBtn.addEventListener('click', function() {
        // ปิด confirmation modal
        closeModal(confirmationModal, modalContent);
        
        // รอให้ modal ปิดแล้วแสดง loading และส่งฟอร์ม
        setTimeout(() => {
            setFormStatus('Pending');
            showLoading();
            
            // ส่งฟอร์มแบบปกติ
            evaluationForm.submit();
        }, 350);
    });

    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value) && value < 0) {
                this.setCustomValidity('คะแนนต้องไม่ติดลบ');
            } else {
                this.setCustomValidity('');
            }
        });
    });
});
</script>
@endsection
