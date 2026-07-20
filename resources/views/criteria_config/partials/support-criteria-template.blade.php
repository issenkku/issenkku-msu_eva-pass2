<div class="support_criterias_container hidden space-y-4 border-l-4 border-amber-400 pl-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h6 class="font-bold text-gray-900">เกณฑ์สำหรับสายสนับสนุน</h6>
        <button type="button"
            class="add_support_criteria_btn rounded-lg bg-amber-100 px-3 py-1.5 text-sm text-amber-800 transition hover:bg-amber-200">
            + เพิ่มเกณฑ์สายสนับสนุน
        </button>
    </div>

    <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
        คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ผู้ถูกประเมินกรอก ÷ 100
    </p>

    <div class="support_criteria_items space-y-3">
        <div class="support_criteria_block rounded-lg bg-white p-4 shadow-sm" draggable="true"
            data-draggable-level="support">
            <input type="hidden" class="support_criteria_id" value="">
            <div class="mb-3 flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-gray-700">รายการ <span
                        class="support_sequence">1.1.1</span></span>
                <div class="flex items-center gap-2">
                    <button type="button" class="drag_handle text-gray-500" title="ลากเพื่อจัดลำดับ">⋮⋮</button>
                    <button type="button" class="move_support_up_btn hidden text-blue-600" disabled>↑</button>
                    <button type="button" class="move_support_down_btn hidden text-blue-600">↓</button>
                    <button type="button" class="delete_support_criteria_btn text-red-600">ลบ</button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label class="text-sm font-medium text-gray-700">
                    กิจกรรม/โครงการ/งาน <span class="text-red-500">*</span>
                    <input type="text"
                        class="support_activity_name mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                        placeholder="กิจกรรม/โครงการ/งาน">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    ตัวชี้วัด/เกณฑ์การประเมิน <span class="text-red-500">*</span>
                    <input type="text"
                        class="support_indicator mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                        placeholder="ตัวชี้วัด/เกณฑ์การประเมิน">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    ระดับค่าเป้าหมาย <span class="text-red-500">*</span>
                    <input type="number" min="0" step="0.01"
                        class="support_target_value mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                        placeholder="ระดับค่าเป้าหมาย">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    น้ำหนัก <span class="text-red-500">*</span>
                    <input type="number" min="0.01" max="100" step="0.01"
                        class="support_weight mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                        placeholder="น้ำหนัก">
                </label>
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox"
                    class="support_require_evidence h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                <span>บังคับแนบหลักฐาน</span>
            </label>
        </div>
    </div>
</div>
