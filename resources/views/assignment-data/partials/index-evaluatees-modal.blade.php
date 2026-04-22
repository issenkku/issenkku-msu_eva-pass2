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
        <div class="relative mb-3">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-sm text-gray-400"></i>
            </div>
            <input
                id="evaluateesSearch"
                type="search"
                class="h-10 w-full rounded-md border border-gray-300 pl-9 pr-3 text-sm leading-10 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-50 disabled:text-gray-400"
                placeholder="ค้นหาชื่อหรืออีเมล"
                autocomplete="off"
                disabled
            >
        </div>
        <div id="evaluateesContent" class="max-h-96 overflow-y-auto"></div>
    </div>
</div>
