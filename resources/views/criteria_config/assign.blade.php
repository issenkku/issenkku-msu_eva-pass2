@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\criteria_config\assign.blade.php --}}

@section('content')
{{-- บล็อกเนื้อหา --}}
<div class="py-12">
    {{-- บล็อกเนื้อหา --}}
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        {{-- ฟอร์ม --}}
        <form action="#" method="POST">
            @csrf
            <!-- ======================================= -->
            <!-- Section 4: กำหนดกรอบการประเมิน            -->
            <!-- ======================================= -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">กำหนดกรอบการประเมิน</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">วันเริ่มต้นประเมิน :</label>
                        <input type="date" name="start_date" id="start_date" class="mt-1 form-input-custom">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">วันสิ้นสุดประเมิน :</label>
                        <input type="date" name="end_date" id="end_date" class="mt-1 form-input-custom">
                    </div>
                </div>
            </div>

            <!-- ======================================= -->
            <!-- Section 5: ผู้ประเมิน / ผู้รับการประเมิน   -->
            <!-- ======================================= -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">กำหนดผู้ประเมิน / ผู้รับการประเมิน</h2>

                <!-- ===== ส่วนที่เพิ่มเข้ามา: ฟิลเตอร์หน่วยงาน ===== -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <label for="department_filter" class="block text-sm font-medium text-gray-700">ฟิลเตอร์ตามหน่วยงานย่อย :</label>
                    <select id="department_filter" name="department_filter" class="mt-1 form-select-custom">
                        <option value="">เลือกหน่วยงาน</option>
                        <option value="dev">ฝ่ายพัฒนาซอฟต์แวร์</option>
                        <option value="marketing">ฝ่ายการตลาด</option>
                        <option value="hr">ฝ่ายบุคคล</option>
                        <option value="support">ฝ่ายสนับสนุนลูกค้า</option>
                    </select>
                    <p class="mt-2 text-xs text-gray-500">เลือกหน่วยงานเพื่อกรองรายชื่อผู้ประเมินและผู้รับการประเมินด้านล่าง</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="evaluatees" class="block text-sm font-medium text-gray-700">รายชื่อผู้รับการประเมิน :</label>
                        <select id="evaluatees" name="evaluatees[]" multiple class="mt-1 form-select-custom" size="4">
                            <option>นายสมชาย ใจดี</option>
                            <option>นางสาวสมศรี มีสุข</option>
                            <option>นายพัฒนา รักงาน</option>
                        </select>
                        <div class="mt-2 p-4 bg-gray-200 rounded-md min-h-[80px] text-sm text-gray-700">
                            แสดงรายชื่อที่เลือกทั้งหมด
                        </div>
                    </div>
                     <div>
                        <label for="evaluators" class="block text-sm font-medium text-gray-700">รายชื่อผู้ประเมิน :</label>
                        <select id="evaluators" name="evaluators[]" multiple class="mt-1 form-select-custom" size="4">
                            <option>หัวหน้าแผนก</option>
                            <option>ผู้จัดการฝ่าย</option>
                        </select>
                        <div class="mt-2 p-4 bg-gray-200 rounded-md min-h-[80px] text-sm text-gray-700">
                           แสดงรายชื่อที่เลือกทั้งหมด
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================= -->
            <!-- Form Actions                            -->
            <!-- ======================================= -->
            <div class="flex justify-end space-x-3">
                <button type="button" id="reset_form_btn" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400">
                    ล้างค่า
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('myForm').addEventListener('submit', function(event) {
        event.preventDefault(); // ป้องกันการ submit form จริง ๆ เพื่อทดสอบ console.log

        // ดึงข้อมูลจาก form
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // แสดงข้อมูลใน console
        console.log('Form Data:', data); });
    
document.addEventListener('DOMContentLoaded', function () {
        
    // ===========================================
    // ===== JavaScript สำหรับ "ด้านคุณภาพ" =====
    // ===========================================
    const quantAddButton = document.getElementById('add_quantitative_item_btn');
    const quantContainer = document.getElementById('quantitative_items_container');
    const quantTemplate = document.getElementById('quantitative_item_template');

    if (quantAddButton && quantContainer && quantTemplate) {
        quantAddButton.addEventListener('click', function () {
            const clone = quantTemplate.content.cloneNode(true);
            // ไม่ต้องสร้าง wrapper แล้ว เพราะ template มี div ครอบอยู่แล้ว
            quantContainer.appendChild(clone);
        });
    }


    // ===========================================
    // ===== JavaScript สำหรับ "ด้านคุณภาพ" =====
    // ===========================================
    const mainTopicsContainer = document.getElementById('main_topics_container');
    const addMainTopicButton = document.getElementById('add_main_topic_btn');

    const mainTopicTemplate = document.getElementById('main_topic_template');
    const subTopicTemplate = document.getElementById('sub_topic_template');
    const itemTemplate = document.getElementById('qualitative_item_template');
    
    // ---- Event Delegation ที่ Container ใหญ่ที่สุด ----
    if (mainTopicsContainer) {
        mainTopicsContainer.addEventListener('click', function(event) {
            const target = event.target;

            // --- จัดการการ "เพิ่มหัวข้อย่อย" ---
            if (target.classList.contains('add_sub_topic_btn')) {
                const mainTopicBlock = target.closest('.main-topic-block');
                const subTopicsContainer = mainTopicBlock.querySelector('.sub_topics_container');
                const mainIndex = Array.from(mainTopicsContainer.children).indexOf(mainTopicBlock);
                const subIndex = subTopicsContainer.children.length;

                let templateContent = subTopicTemplate.innerHTML
                    .replace(/__MAIN_INDEX__/g, mainIndex)
                    .replace(/__SUB_INDEX__/g, subIndex);
                
                const newSubTopic = document.createElement('div');
                newSubTopic.innerHTML = templateContent;
                subTopicsContainer.appendChild(newSubTopic.firstElementChild);
            }

            // --- จัดการการ "เพิ่มรายการ" ---
            if (target.classList.contains('add_qualitative_item_btn')) {
                const mainTopicBlock = target.closest('.main-topic-block');
                const subTopicBlock = target.closest('.sub-topic-block');
                const itemsContainer = subTopicBlock.querySelector('.qualitative_items_container');

                const mainIndex = Array.from(mainTopicsContainer.children).indexOf(mainTopicBlock);
                const subIndex = Array.from(mainTopicBlock.querySelector('.sub_topics_container').children).indexOf(subTopicBlock);
                const itemIndex = itemsContainer.children.length;
                
                let templateContent = itemTemplate.innerHTML
                    .replace(/__MAIN_INDEX__/g, mainIndex)
                    .replace(/__SUB_INDEX__/g, subIndex)
                    .replace(/__ITEM_INDEX__/g, itemIndex);
                
                const newItem = document.createElement('div');
                newItem.innerHTML = templateContent;
                itemsContainer.appendChild(newItem.firstElementChild);
            }
        });
    }

    // ---- ฟังก์ชันสำหรับ "เพิ่มหัวข้อหลัก" (แยกออกมา) ----
    if (addMainTopicButton) {
        addMainTopicButton.addEventListener('click', function() {
            const mainIndex = mainTopicsContainer.children.length;
            
            const templateContent = mainTopicTemplate.innerHTML.replace(/__MAIN_INDEX__/g, mainIndex);
            
            const newMainTopic = document.createElement('div');
            newMainTopic.innerHTML = templateContent;
            mainTopicsContainer.appendChild(newMainTopic.firstElementChild);
        });
    }

        
    // ===========================================
    // ===== JavaScript สำหรับปุ่ม "ล้างค่า" =====
    // ===========================================
    const resetFormButton = document.getElementById('reset_form_btn');

    if (resetFormButton) {
        resetFormButton.addEventListener('click', function() {
            // 1. แสดงกล่องข้อความยืนยัน
            if (!confirm('คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?')) {
                return; // ถ้าผู้ใช้กด "Cancel" ให้หยุดการทำงาน
            }

            // 2. ล้างค่าใน input, select, textarea ทั้งหมดในฟอร์ม
            const form = resetFormButton.closest('form');
            if (form) {
                form.reset();
            }

            // 3. ลบ element ที่สร้างแบบไดนามิกใน "ด้านปริมาณ"
            const quantitativeContainer = document.getElementById('quantitative_items_container');
            if (quantitativeContainer) {
                // เก็บรายการแรกๆ ไว้ (ตามจำนวนที่แสดงตอนแรก)
                const initialQuantItems = 2; // << ปรับตัวเลขนี้ถ้าจำนวนเริ่มต้นเปลี่ยนไป
                while (quantitativeContainer.children.length > initialQuantItems) {
                    quantitativeContainer.lastChild.remove();
                }
            }

            // 4. ลบ element ที่สร้างแบบไดนามิกใน "ด้านคุณภาพ"
            const mainTopicsContainer = document.getElementById('main_topics_container');
            if (mainTopicsContainer) {
                 // 4.1 ลบ "หัวข้อหลัก" ที่เพิ่มเข้ามาทั้งหมด ให้เหลือแค่ชุดแรก
                const initialMainTopics = 1; // << มีหัวข้อหลักเริ่มต้น 1 ชุด
                while (mainTopicsContainer.children.length > initialMainTopics) {
                    mainTopicsContainer.lastChild.remove();
                }

                // 4.2 ในหัวข้อหลักที่เหลือ, ลบ "หัวข้อย่อย" ที่เพิ่มเข้ามา
                const firstMainTopic = mainTopicsContainer.querySelector('.main-topic-block');
                if (firstMainTopic) {
                    const subTopicsContainer = firstMainTopic.querySelector('.sub_topics_container');
                    const initialSubTopics = 1; // << มีหัวข้อย่อยเริ่มต้น 1 ชุด
                    while (subTopicsContainer.children.length > initialSubTopics) {
                        subTopicsContainer.lastChild.remove();
                    }

                    // 4.3 ในหัวข้อย่อยที่เหลือ, ลบ "รายการ" ที่เพิ่มเข้ามา
                    const firstSubTopic = subTopicsContainer.querySelector('.sub-topic-block');
                    if(firstSubTopic) {
                        const itemsContainer = firstSubTopic.querySelector('.qualitative_items_container');
                        const initialItems = 1; // << มีรายการเริ่มต้น 1 รายการ
                        while(itemsContainer.children.length > initialItems) {
                            itemsContainer.lastChild.remove();
                        }
                    }
                }
            }
            
            // 5. (ทางเลือก) แจ้งเตือนว่าล้างค่าสำเร็จ
            alert('ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว');
        });
    }
});
</script>
@endpush
