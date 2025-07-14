@extends('layouts.app')

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-evaluate-profile-card 
        :evaluatorName="$evaluatorName"
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

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

        <div class="space-y-8">
            <x-quantity-table
                :evaluationItems="$evaluationItems"
                title="ด้านปริมาณ"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
            />

            <x-quality-table
                :qualityItems="$qualityItems" 
                title="ด้านคุณภาพ"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
            />
        </div>

        <input type="hidden" name="status" id="formStatus" value="submitted">

        @if($readonly)
            </fieldset>
        @endif

        <div class="flex justify-center gap-4 mt-8">
            <a href="/evaluatee-dashboard" class="px-6 py-2 bg-white rounded-md text-center hover:bg-gray-200 w-40">กลับ</a>

            @unless($readonly)
                <button type="submit" onclick="setFormStatus('Draft')" class="bg-pink-400 text-white px-6 py-2 rounded-md hover:bg-pink-500 w-40">
                    บันทึกร่าง
                </button>
                <button type="button" id="openModalBtn" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 w-40">
                    ส่งแบบประเมิน
                </button>
            @endunless
        </div>

        <!-- <div class="sticky bottom-0 py-4 px-4 z-10">
            <div class="flex justify-center gap-4">
                <a href="{{ route('dashboard') }}" class="px-6 py-2 shadow bg-white rounded-md text-center hover:bg-gray-200 w-40">กลับ</a>

                @unless($readonly)
                    <button type="submit" onclick="setFormStatus('Draft')" class="bg-pink-400 shadow text-white px-6 py-2 rounded-md hover:bg-pink-500 w-40">
                        บันทึกร่าง
                    </button>
                    <button type="submit" onclick="setFormStatus('Pending')" class="bg-purple-600 shadow text-white px-6 py-2 rounded-md hover:bg-purple-700 w-40" id="submitBtn">
                        ส่งแบบประเมิน
                    </button>
                @endunless
            </div>
        </div> -->
    </form>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 hidden z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
        <!-- Icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-5">
            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.049c.534-2.203 2.51-3.79 4.772-3.79s4.238 1.587 4.772 3.79M8.228 9.049L6.5 10.5m1.728-1.451L9.5 6.5m6.228 2.549L17.5 10.5m-1.728-1.451L14.5 6.5M12 21a9 9 0 110-18 9 9 0 010 18z"></path>
            </svg>
        </div>
        
        <!-- Title -->
        <h3 class="text-xl font-bold text-gray-800">ยืนยันการส่งแบบประเมิน</h3>
        
        <!-- Description -->
        <div class="mt-2 mb-6">
            <p class="text-sm text-gray-500 px-4">
                เมื่อส่งแล้วจะไม่สามารถกลับมาแก้ไขได้อีก<br>คุณต้องการดำเนินการต่อหรือไม่?
            </p>
        </div>

        <!-- Buttons -->
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
</style>

<script>
function setFormStatus(status) {
    document.getElementById('formStatus').value = status;
}

// ===== โค้ดสำหรับ Modal (ส่วนที่เพิ่มเข้ามา) =====
document.addEventListener('DOMContentLoaded', function() {
    const evaluationForm = document.getElementById('evaluationForm');
    const openModalBtn = document.getElementById('openModalBtn');
    const confirmationModal = document.getElementById('confirmationModal');
    const modalContent = document.getElementById('modal-content'); // << เพิ่ม
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');

    if (!openModalBtn || !confirmationModal) {
        return;
    }
    
    // ===== ฟังก์ชันสำหรับเปิด-ปิด Modal พร้อม Animation =====
    function openModal() {
        confirmationModal.classList.remove('hidden');
        // ใช้ setTimeout เล็กน้อยเพื่อให้ CSS transition ทำงานได้
        setTimeout(() => {
            confirmationModal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    
    function closeModal() {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        confirmationModal.classList.add('opacity-0');
        // รอให้ animation จบแล้วค่อยซ่อน
        setTimeout(() => {
            confirmationModal.classList.add('hidden');
        }, 300); // 300ms คือ duration ของ transition
    }
    // =======================================================

    // เมื่อกดปุ่ม "ส่งแบบประเมิน" ให้เปิด Modal
    openModalBtn.addEventListener('click', openModal);

    // เมื่อกดปุ่ม "ยกเลิก" ใน Modal ให้ปิด Modal
    cancelModalBtn.addEventListener('click', closeModal);

    // เมื่อกดปุ่ม "ยืนยัน" ใน Modal
    confirmSubmitBtn.addEventListener('click', function() {
        setFormStatus('Pending');
        closeModal(); // ปิด Modal ก่อน
        
        // ส่งฟอร์มหลังจากปิด Modal ไปแล้วเล็กน้อย
        setTimeout(() => {
            if (evaluationForm) {
                evaluationForm.submit();
            }
        }, 350);
    });

    // ปิด Modal เมื่อคลิกที่พื้นหลังสีเทา
    confirmationModal.addEventListener('click', function(event) {
        if (event.target === confirmationModal) {
            closeModal();
        }
    });
});
</script>
@endsection