{{-- ไฟล์มุมมอง: resources/views/criteria_config/partials/evaluators-period-section.blade.php --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">กำหนดกรอบการประเมิน</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700">วันเริ่มต้นประเมิน
                :</label>
            <input type="date" name="start_date" id="start_date" class="mt-1 form-input-custom">
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700">วันสิ้นสุดประเมิน
                :</label>
            <input type="date" name="end_date" id="end_date" class="mt-1 form-input-custom">
        </div>
    </div>
</div>
