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
                    <textarea rows="8"
                        class="support_activity_name richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                        placeholder="กิจกรรม/โครงการ/งาน"></textarea>
                </label>
                <label class="text-sm font-medium text-gray-700" data-support-legacy-indicator>
                    ตัวชี้วัด/เกณฑ์การประเมิน <span class="text-red-500">*</span>
                    <textarea rows="8"
                        class="support_indicator richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                        placeholder="ตัวชี้วัด/เกณฑ์การประเมิน"></textarea>
                </label>
                <label class="text-sm font-medium text-gray-700">
                    ระดับค่าเป้าหมาย <span class="text-red-500">*</span>
                    <input type="number" min="0" step="0.01"
                        class="support_target_value mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                        placeholder="ระดับค่าเป้าหมาย">
                </label>
                <label class="text-sm font-medium text-gray-700">
                    น้ำหนัก <span class="text-red-500" data-support-weight-required>*</span>
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
            <label class="mt-3 flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox"
                    class="support_allow_activity_entries h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                <span>อนุญาตให้ผู้ถูกประเมินเพิ่มกิจกรรม/โครงการ</span>
            </label>
            <label class="mt-3 flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox"
                    class="support_allow_evaluatee_indicator h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                    disabled>
                <span>อนุญาตให้ผู้ถูกประเมินกรอกตัวชี้วัด</span>
            </label>
            <label class="mt-3 flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox"
                    class="support_allow_evaluatee_weight h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                    disabled>
                <span>อนุญาตให้ผู้ถูกประเมินกรอกน้ำหนัก</span>
            </label>
            <label class="mt-3 flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox"
                    class="support_group_by_indicator h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                    disabled>
                <span>แยกโครงการตามตัวชี้วัดย่อย</span>
            </label>

            <section class="support_indicator_items mt-4 hidden rounded-lg border border-amber-200 bg-amber-50 p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <h6 class="font-semibold text-amber-950">ตัวชี้วัดย่อย</h6>
                    <button type="button"
                        class="add_support_indicator_item_btn rounded-lg bg-white px-3 py-2 text-sm font-semibold text-amber-900 shadow-sm ring-1 ring-amber-200 transition hover:bg-amber-100">
                        + เพิ่มตัวชี้วัดย่อย
                    </button>
                </div>
                <div class="support_indicator_item_list space-y-3">
                    <article class="support_indicator_item_block rounded-lg border border-amber-200 bg-white p-3">
                        <input type="hidden" class="support_indicator_item_id" value="">
                        <input type="hidden" class="support_indicator_sequence" value="1">
                        <label class="block text-sm font-medium text-gray-700">
                            รหัสข้อ <span class="text-red-500">*</span>
                            <input type="text"
                                class="support_indicator_code mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
                                placeholder="เช่น 2.1">
                        </label>
                        <button type="button"
                            class="delete_support_indicator_item_btn mt-3 text-sm font-semibold text-red-600 transition hover:text-red-800">
                            ลบข้อย่อย
                        </button>
                    </article>
                </div>
            </section>
        </div>
    </div>
</div>
