{{-- ส่วนเลือกเกณฑ์หรือแบบประเมินที่ใช้ในรอบนี้ --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-2">
    <div class="flex items-center mb-6">
        <div class="flex items-center justify-center w-8 h-8 bg-green-600 text-white rounded-full mr-3 text-sm font-semibold">
            2
        </div>
        <h2 class="text-xl font-semibold text-gray-800">เลือกเกณฑ์การประเมิน</h2>
    </div>
    <div>
        <label for="report_data_id" class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-clipboard-list mr-2 text-green-500"></i>เกณฑ์การประเมิน:
        </label>
        <select
            id="report_data_id"
            name="report_data_id"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
        >
            <option value="">-- กรุณาเลือกเกณฑ์การประเมิน --</option>
            @foreach ($report_data as $item)
                <option
                    value="{{ $item->id }}"
                    data-assessment-type="{{ $item->assessment_type }}"
                    {{ old('report_data_id', $currentReportDataId) == $item->id ? 'selected' : '' }}
                >
                    {{ $item->report_title }}
                </option>
            @endforeach
        </select>
    </div>
</div>
