@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form action="#" method="POST">
            @csrf

            <!-- ======================================= -->
            <!-- Section 1: ชื่อเกณฑ์และกลุ่มงาน           -->
            <!-- ======================================= -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">ชื่อ section</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-3 items-center">
                        <label for="criterion_name" class="col-span-1 text-sm font-medium text-gray-700">ชื่อเกณฑ์ :</label>
                        <input type="text" name="criterion_name" id="criterion_name" class="col-span-2 mt-1 form-input-custom">
                    </div>
                    <div class="grid grid-cols-3 items-center">
                        <label for="department" class="col-span-1 text-sm font-medium text-gray-700">เลือกกลุ่มงาน :</label>
                        <select id="department" name="department" class="col-span-2 mt-1 form-select-custom">
                            <option>กลุ่มวิชาการ</option>
                            <option>กลุ่มสนับสนุน</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ======================================= -->
            <!-- Section 2: ด้านปริมาณ (Quantitative)     -->
            <!-- ======================================= -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">ชื่อ section ด้านปริมาณ</h2>
                <!-- ... (ส่วนของชื่อหัวข้อหลัก/ย่อย เหมือนเดิม) ... -->
                <hr class="my-6">
                
                <!-- Container สำหรับเก็บรายการที่เพิ่มเข้ามา -->
                <div class="space-y-4" id="quantitative_items_container">
                    <!-- Mockup Item 1 (แสดงเป็นตัวอย่าง) -->
                    <div class="grid grid-cols-10 gap-4 items-center">
                        <label class="col-span-1 text-sm text-gray-700">ชื่อรายการ</label>
                        <input type="text" class="col-span-5 form-input-custom">
                        <label class="col-span-1 text-sm text-gray-700 text-right">ค่าน้ำหนัก</label>
                        <input type="text" class="col-span-1 form-input-custom">
                        <label class="col-span-2 text-sm text-gray-700 text-center">หน่วยภาระงานมาตรฐาน</label>
                    </div>
                </div>

                <!-- 1. เพิ่ม ID ให้กับปุ่ม -->
                <div class="flex justify-end mt-4">
                    <button type="button" id="add_quantitative_item_btn" class="px-4 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-full hover:bg-gray-300">
                        เพิ่มรายการ
                    </button>
                </div>
            </div>

            <!-- 2. สร้าง Template เพื่อเก็บ HTML ที่จะคัดลอก -->
            <template id="quantitative_item_template">
                <div class="grid grid-cols-10 gap-4 items-center">
                    <label class="col-span-1 text-sm text-gray-700">ชื่อรายการ</label>
                    <input type="text" class="col-span-5 form-input-custom">
                    <label class="col-span-1 text-sm text-gray-700 text-right">ค่าน้ำหนัก</label>
                    <input type="text" class="col-span-1 form-input-custom">
                    <label class="col-span-2 text-sm text-gray-700 text-center">หน่วยภาระงานมาตรฐาน</label>
                </div>
            </template>


            <!-- ======================================= -->
            <!-- Section 3: ด้านคุณภาพ (Qualitative)      -->
            <!-- ======================================= -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">ชื่อ section ด้านคุณภาพ</h2>

                <!-- 1. สร้าง Container หลักสำหรับเก็บ "หัวข้อหลัก" -->
                <div id="main_topics_container" class="space-y-10">
                    
                    <!-- Block ของ "หัวข้อหลัก" ชุดแรก -->
                    <div class="main-topic-block border border-gray-200 p-4 rounded-lg">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">ชื่อหัวข้อหลัก :</label>
                                <input type="text" name="main_topics[0][name]" class="mt-1 form-input-custom">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ค่าน้ำหนักคะแนน</label>
                                <input type="text" name="main_topics[0][weight]" class="mt-1 form-input-custom">
                            </div>
                        </div>
                        <hr class="my-6">

                        <!-- Container สำหรับ "หัวข้อย่อย" ของหัวข้อหลักชุดนี้ -->
                        <div class="sub_topics_container space-y-8">
                            <!-- Block ของ "หัวข้อย่อย" ชุดแรก -->
                            <div class="sub-topic-block">
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-2"><label class="block text-sm font-medium text-gray-700">ชื่อหัวข้อย่อย</label><input type="text" name="main_topics[0][sub_topics][0][name]" class="mt-1 form-input-custom"></div>
                                    <div><label class="block text-sm font-medium text-gray-700">สัดส่วน</label><input type="text" name="main_topics[0][sub_topics][0][ratio]" class="mt-1 form-input-custom"></div>
                                </div>
                                <div class="my-3 border-t border-dashed border-gray-300"></div>
                                <div class="space-y-6 qualitative_items_container">
                                    <div class="grid grid-cols-5 gap-4">
                                        <div class="col-span-4"><label class="block text-sm font-medium text-gray-700 mb-1">ชื่อรายการ</label><input type="text" name="main_topics[0][sub_topics][0][items][0][name]" class="w-full form-input-custom"></div>
                                        <div class="col-span-1"><label class="block text-sm font-medium text-gray-700 mb-1">ค่าคะแนน</label><input type="text" name="main_topics[0][sub_topics][0][items][0][score]" class="w-full form-input-custom"></div>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-4"><button type="button" class="add_qualitative_item_btn px-4 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-full hover:bg-gray-300">เพิ่มรายการ</button></div>
                            </div>
                        </div>
                        
                        <!-- ปุ่มเพิ่มหัวข้อย่อยของหัวข้อหลักชุดนี้ -->
                        <div class="mt-6 space-y-4">
                            <hr><div class="flex justify-center"><button type="button" class="add_sub_topic_btn px-4 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">เพิ่มหัวข้อย่อย</button></div>
                        </div>

                        <!-- ส่วนหมายเหตุ -->
                        <div class="mt-6">
                            <label for="main_topics[0][notes]" class="block text-sm font-medium text-gray-700">หมายเหตุ (คำแนะนำการให้คะแนน)</label>
                            <textarea id="main_topics[0][notes]" name="main_topics[0][notes]" rows="3" class="form-input-custom !py-2"></textarea>
                        </div>
                    </div>
                </div>


                <div class="mt-6 space-y-4">
                    <hr>
                    <!-- 2. ปุ่มเพิ่มหัวข้อหลัก -->
                    <div class="flex justify-center">
                        <button type="button" id="add_main_topic_btn" class="px-4 py-1.5 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600">
                            เพิ่มหัวข้อหลัก
                        </button>
                    </div>
                </div>
            </div>


            <!-- =========================================== -->
            <!-- Templates (ต้องอัปเดตทั้งหมด)               -->
            <!-- =========================================== -->

            <!-- 3. Template สำหรับ "หัวข้อหลัก" ทั้งชุด (ใหม่) -->
            <template id="main_topic_template">
                <div class="main-topic-block border border-gray-200 p-4 rounded-lg">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2"><label class="block text-sm font-medium text-gray-700">ชื่อหัวข้อหลัก :</label><input type="text" name="main_topics[__MAIN_INDEX__][name]" class="mt-1 form-input-custom"></div>
                        <div><label class="block text-sm font-medium text-gray-700">ค่าน้ำหนักคะแนน</label><input type="text" name="main_topics[__MAIN_INDEX__][weight]" class="mt-1 form-input-custom"></div>
                    </div>
                    <hr class="my-6">
                    <div class="sub_topics_container space-y-8">
                        <div class="sub-topic-block">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2"><label class="block text-sm font-medium text-gray-700">ชื่อหัวข้อย่อย</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][0][name]" class="mt-1 form-input-custom"></div>
                                <div><label class="block text-sm font-medium text-gray-700">สัดส่วน</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][0][ratio]" class="mt-1 form-input-custom"></div>
                            </div>
                            <div class="my-3 border-t border-dashed border-gray-300"></div>
                            <div class="space-y-6 qualitative_items_container">
                                <div class="grid grid-cols-5 gap-4">
                                    <div class="col-span-4"><label class="block text-sm font-medium text-gray-700 mb-1">ชื่อรายการ</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][0][items][0][name]" class="w-full form-input-custom"></div>
                                    <div class="col-span-1"><label class="block text-sm font-medium text-gray-700 mb-1">ค่าคะแนน</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][0][items][0][score]" class="w-full form-input-custom"></div>
                                </div>
                            </div>
                            <div class="flex justify-end mt-4"><button type="button" class="add_qualitative_item_btn px-4 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-full hover:bg-gray-300">เพิ่มรายการ</button></div>
                        </div>
                    </div>
                    <div class="mt-6 space-y-4"><hr><div class="flex justify-center"><button type="button" class="add_sub_topic_btn px-4 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">เพิ่มหัวข้อย่อย</button></div></div>

                    <div class="mt-6">
                        <label for="main_topics[__MAIN_INDEX__][notes]" class="block text-sm font-medium text-gray-700">หมายเหตุ (คำแนะนำการให้คะแนน)</label>
                        <textarea id="main_topics[__MAIN_INDEX__][notes]" name="main_topics[__MAIN_INDEX__][notes]" rows="3" class="form-input-custom !py-2"></textarea>
                    </div>
                </div>
            </template>

            <template id="sub_topic_template">
                <div class="sub-topic-block">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2"><label class="block text-sm font-medium text-gray-700">ชื่อหัวข้อย่อย</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][__SUB_INDEX__][name]" class="mt-1 form-input-custom"></div>
                        <div><label class="block text-sm font-medium text-gray-700">สัดส่วน</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][__SUB_INDEX__][ratio]" class="mt-1 form-input-custom"></div>
                    </div>
                    <div class="my-3 border-t border-dashed border-gray-300"></div>
                    <div class="space-y-6 qualitative_items_container">
                        <div class="grid grid-cols-5 gap-4">
                            <div class="col-span-4"><label class="block text-sm font-medium text-gray-700 mb-1">ชื่อรายการ</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][__SUB_INDEX__][items][0][name]" class="w-full form-input-custom"></div>
                            <div class="col-span-1"><label class="block text-sm font-medium text-gray-700 mb-1">ค่าคะแนน</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][__SUB_INDEX__][items][0][score]" class="w-full form-input-custom"></div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4"><button type="button" class="add_qualitative_item_btn px-4 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-full hover:bg-gray-300">เพิ่มรายการ</button></div>
                </div>
            </template>

            <template id="qualitative_item_template">
                <div class="grid grid-cols-5 gap-4">
                    <div class="col-span-4"><label class="block text-sm font-medium text-gray-700 mb-1">ชื่อรายการ</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][__SUB_INDEX__][items][__ITEM_INDEX__][name]" class="w-full form-input-custom"></div>
                    <div class="col-span-1"><label class="block text-sm font-medium text-gray-700 mb-1">ค่าคะแนน</label><input type="text" name="main_topics[__MAIN_INDEX__][sub_topics][__SUB_INDEX__][items][__ITEM_INDEX__][score]" class="w-full form-input-custom"></div>
                </div>
            </template>

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