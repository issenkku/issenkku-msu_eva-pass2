{{-- modal แสดงรายชื่อผู้รับการประเมินของรอบที่เลือกจากหน้า list --}}
<div id="evaluateesModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-users mr-2 text-blue-600"></i>รายชื่อผู้รับการประเมิน
            </h3>
            <button type="button" data-modal-close class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="evaluateesContent" class="mt-4 max-h-96 overflow-y-auto"></div>
    </div>
</div>
