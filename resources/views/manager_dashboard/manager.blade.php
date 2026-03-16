@extends('layouts.app')

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
{{-- บล็อกเนื้อหา --}}
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    {{--  --}}
    <div class="page-header">
        <h1>แบบประเมินผลงาน</h1>
        <p class="version">เวอร์ชัน: {{ $versionName }}</p>
    </div>

    <x-evaluate-report-card 
        :reportName="$reportName"
        :reportDescription="$reportDescription"
        :assessmentType="$assessmentType"
        :reportComment="$reportComment"
    />

    <x-evaluator-profile-card 
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    <x-director-profile-card
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    {{-- ฟอร์ม --}}
    <form id="evaluationForm" method="POST" action="{{ route('manager_score.store', $report->id) }}">
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

        <x-unified-director
            :categoryItems="$categoryItems"
            :readonly="$readonly"
            :evidenceMap="$evidenceMap"
            :qualityEvidenceMap="$qualityEvidenceMap"
            :workloadMap="$workloadMap"
        />

        {{-- บล็อกเนื้อหา --}}
        <div class="bg-purple-50 border border-blue-200 rounded-lg p-6 mt-8">
            <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                    </path>
                </svg>
                ความคิดเห็นจากผู้ประเมิน
            </h3>
            @if(!$readonly)
                <textarea
                    name="comment"
                    class="bg-white form-input text-base w-full mt-2 px-3 rounded-lg p-4 border border-gray-400 focus:ring-green-500 focus:border-green-500"
                    placeholder="ระบุความความเห็นเพิ่มเติม">{{ old('comment', $assignment->report->comment ?? '') }}</textarea>
            @else
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
            @endif
        </div> 

        <input type="hidden" name="status" id="formStatus" value="submitted">

        @if($readonly)
            </fieldset>
        @endif

        {{-- บล็อกเนื้อหา --}}
        <div class="flex justify-center gap-4 mt-8">
            @if(auth()->user()->hasRole('admin'))
            <x-button 
                    type="default" 
                    text="ย้อนกลับ" 
                    icon="fas fa-arrow-left"
                    href="/dashboard" />
            @elseif(auth()->user()->hasRole('ผู้บริหาร'))
            <x-button 
                    type="default" 
                    text="ย้อนกลับ" 
                    icon="fas fa-arrow-left"
                    href="/manager-dashboard" />
            @endif

            @unless($readonly)
                <x-button 
                    type="secondary"
                    buttonType="submit" 
                    text="บันทึกร่าง" 
                    onclick="setFormStatus('Manager_draft')" 
                    icon="fas fa-save" />
                <x-button 
                    type="primary"
                    buttonType="button" 
                    text="รับรองการประเมิน"
                    id="openModalBtn"
                    icon="fa-solid fa-check-to-slot" />
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
<div id="confirmationModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 hidden z-50 transition-opacity duration-300">
    {{--  --}}
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
        <!-- Icon -->
        {{-- บล็อกเนื้อหา --}}
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-5">
            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.049c.534-2.203 2.51-3.79 4.772-3.79s4.238 1.587 4.772 3.79M8.228 9.049L6.5 10.5m1.728-1.451L9.5 6.5m6.228 2.549L17.5 10.5m-1.728-1.451L14.5 6.5M12 21a9 9 0 110-18 9 9 0 010 18z"></path>
            </svg>
        </div>
        
        <!-- Title -->
        <h3 class="text-xl font-bold text-gray-800">ยืนยันการส่งแบบประเมิน</h3>
        
        <!-- Description -->
        {{-- บล็อกเนื้อหา --}}
        <div class="mt-2 mb-6">
            <p class="text-sm text-gray-500 px-4">
                เมื่อส่งแล้วจะไม่สามารถกลับมาแก้ไขได้อีก<br>คุณต้องการดำเนินการต่อหรือไม่?
            </p>
        </div>

        <!-- Buttons -->
        {{-- บล็อกเนื้อหา --}}
        <div class="flex flex-col space-y-3">
            <button id="confirmSubmitBtn" class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors duration-200">
                ยืนยัน
            </button>
            <button id="cancelModalBtn" class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-200">
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
    openModalBtn.addEventListener('click', () => {
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
            setFormStatus('Completed');
            showLoading();
            
            // ส่งฟอร์มแบบปกติ
            evaluationForm.submit();
        }, 350);
    });
});
</script>
@endsection
