{{-- ส่วนเลือกช่วงเวลาเริ่มต้นและสิ้นสุดของรอบการประเมิน --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-1">
    <div class="flex items-center mb-6">
        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full mr-3 text-sm font-semibold">
            1
        </div>
        <h2 class="text-xl font-semibold text-gray-800">กำหนดกรอบการประเมิน</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>วันเริ่มต้นประเมิน:
            </label>
            <input
                type="text"
                name="start_time"
                id="start_time"
                autocomplete="off"
                value="{{ $startTimeValue }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-date"
                required
            >
        </div>
        <div>
            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>วันสิ้นสุดประเมิน:
            </label>
            <input
                type="text"
                name="end_time"
                id="end_time"
                autocomplete="off"
                value="{{ $endTimeValue }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-date"
                required
            >
        </div>
    </div>
</div>
