                            <!-- Evaluation List Template -->
                            <div class="evaluation_list_block bg-gray-100 p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300" draggable="true" data-draggable-level="evaluation">
                                <input type="hidden" class="evaluation_id_value" value="">
                                <div class="flex justify-between items-center mb-4">
                                    <h5 class="font-bold text-gray-900">รายการประเมิน</h5>
                                    <div class="flex items-center space-x-3">
                                        <button type="button" class="drag_handle inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 transition duration-200" title="ลากเพื่อจัดลำดับ">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                            </svg>
                                            <span class="text-xs font-medium">ลากจัดลำดับ</span>
                                        </button>
                                        <button type="button" class="move_eval_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <button type="button" class="move_eval_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" class="delete_eval_btn text-red-600 hover:text-red-800 transition duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                        <div class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-gray-700">
                                            <span class="eval_sequence">1.1</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อรายการ <span class="text-red-500">*</span></label>
                                        <input required name="eval_name" class="eval_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition duration-200" placeholder="ชื่อรายการประเมิน">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">คะแนนรวม <span class="text-red-500">*</span></label>
                                        <input type="number" required name="sum_score" class="sum_score border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition duration-200" placeholder="คะแนนรวม">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                                        <input name="annotation" class="annotation border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition duration-200" placeholder="หมายเหตุ">
                                    </div>
                                </div>
                                <!-- Criteria Type Selection -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ประเภทเกณฑ์</label>
                                    <div class="criteria_type_check_group flex gap-6 text-gray-900">
                                        <label class="flex items-center">
                                            <input type="checkbox" class="criteria_type quantity_criteria_type form-checkbox h-5 w-5 text-green-600 rounded focus:ring-green-500" value="quantity">
                                            <span class="ml-2 text-sm">เกณฑ์ด้านปริมาณ</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="criteria_type quality_criteria_type form-checkbox h-5 w-5 text-purple-600 rounded focus:ring-purple-500" value="quality">
                                            <span class="ml-2 text-sm">เกณฑ์ด้านคุณภาพ</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Quantity Criteria Section -->
                                <div class="quantity_main_criterias_container space-y-4 pl-6 border-l-4 border-green-400 hidden">
                                    <h6 class="font-bold text-gray-900 mb-3 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                        </svg>
                                        เกณฑ์ด้านปริมาณ
                                    </h6>
                                    <button type="button" class="add_quant_criteria_btn mt-2 text-sm px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        เพิ่มเกณฑ์ปริมาณหลัก
                                    </button>
                                    <!-- Quantity Main Criteria Template -->
                                    <div class="quant_criteria_block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200" draggable="true" data-draggable-level="quantity-main">
                                        <input type="hidden" class="quantity_main_id_value" value="">
                                        <div class="flex justify-between items-center mb-3">
                                            <h6 class="text-sm font-bold text-gray-900">เกณฑ์ปริมาณหลัก</h6>
                                            <div class="flex items-center space-x-3">
                                                <button type="button" class="drag_handle inline-flex items-center text-gray-500 hover:text-gray-700 transition duration-200" title="ลากเพื่อจัดลำดับ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="move_quant_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="move_quant_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="delete_quant_btn text-red-600 hover:text-red-800 transition duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                                <div class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
                                                    <span class="quant_main_sequence">1.1.1</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span class="text-red-500">*</span></label>
                                                <input name="quant_name" class="quant_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200" placeholder="ชื่อเกณฑ์ปริมาณ">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย <span class="text-red-500"></span></label>
                                            <textarea name="quant_tooltips" class="quant_tooltips richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200" placeholder="คำอธิบายเพิ่มเติม"></textarea>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">กำหนดสูตร
                                                    <span class="text-xs text-gray-500">(A=ค่าน้ำหนัก, B=ภาระงานมาตรฐาน, C=ภาระงานที่ทำได้, D=คะแนนที่คำนวณได้)</span>
                                                </label>
                                                <textarea name="quant_formula" rows="3" class="quant_formula border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200" placeholder="กำหนดสูตรการคำนวณ เช่น D = A × C / B">D = A × C / B</textarea>
                                            </div>
                                        </div>
                                        <!-- Quantity Sub Criteria Container -->
                                        <div class="quant_sub_criteria_container space-y-3 pl-4 border-l-2 border-green-200 mb-3">
                                            <div class="quant_sub_criteria_block bg-gray-50 p-3 rounded-lg" draggable="true" data-draggable-level="quantity-sub">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-600">เกณฑ์ปริมาณย่อย</span>
                                                    <div class="flex items-center gap-3">
                                                        <button type="button" class="drag_handle inline-flex items-center text-gray-500 hover:text-gray-700 transition duration-200" title="ลากเพื่อจัดลำดับ">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="delete_quant_sub_btn text-red-600 hover:text-red-800 transition duration-200" title="ลบ">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="quant_sub_criteria_id" class="quant_sub_criteria_id" value="">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 md:items-end">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</label>
                                                        <div class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                                            <span class="quant_sub_sequence">1.1.1.1</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ชื่อเกณฑ์ย่อย <span class="text-red-500">*</span></label>
                                                        <input name="quant_sub_name" class="quant_sub_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200" placeholder="ชื่อเกณฑ์ย่อย">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ค่าน้ำหนักคะแนน (A)<span class="text-red-500">*</span></label>
                                                        <input type="number" name="score_a" class="score_a border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200" placeholder="คะแนน A">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2 whitespace-nowrap">หน่วยภาระงานมาตรฐาน (B)&nbsp;<span class="text-red-500">*</span></label>
                                                        <input type="number" name="score_b" class="score_b border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200" placeholder="คะแนน B">
                                                    </div>
                                                    <div class="flex flex-col items-start sm:items-end">
                                                        <span class="block text-sm font-medium text-gray-600 mb-2 opacity-0 select-none">spacer</span>
                                                        <a href="/workload-config" class="quant_sub_setting_btn inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200" title="ตั้งค่าเกณฑ์ย่อย">
                                                          
                                                            <span class="text-sm font-semibold">ตั้งค่าภาระงาน</span>
                                                        </a>
                                                    </div>
                                                </div>
                                                <label class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                                    <input type="checkbox" class="quant_require_evidence h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                    <span>บังคับแนบหลักฐานก่อนบันทึกภาระงาน</span>
                                                </label>
                                                <label class="mt-2 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                                    <input type="checkbox" class="quant_require_subject h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span>หัวข้อนี้ต้องเลือกรายวิชาและใช้ค่าหน่วยกิต</span>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="add_quant_sub_criteria_btn text-sm px-3 py-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            เพิ่มปริมาณย่อย
                                        </button>
                                    </div>
                                </div>

                                <!-- Quality Criteria Section -->
                                <div class="quality_main_criterias_container space-y-4 pl-6 border-l-4 border-purple-400 hidden">
                                    <h6 class="font-bold text-gray-900 mb-3 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        เกณฑ์ด้านคุณภาพ
                                    </h6>
                                    <div class="mb-4 rounded-xl border border-purple-200 bg-purple-50 px-4 py-3 text-sm text-purple-900">
                                        <div class="font-semibold">แนวทางจัดกลุ่มด้านคุณภาพ</div>
                                        <div class="mt-1">แนะนำให้แยกเป็น 2 ชั้น: <span class="font-medium">กลุ่มเกณฑ์หลัก</span> และ <span class="font-medium">ตัวเลือกย่อย</span></div>
                                        <div class="mt-1 text-purple-800">ตัวอย่าง: กลุ่มเกณฑ์หลัก = บทความวิจัย, ทรัพย์สินทางปัญญา | ตัวเลือกย่อย = SCOPUS Q1-2 ผู้ประพันธ์หลัก, สิทธิบัตร ผู้ถือสิทธิร่วม</div>
                                    </div>
                                    <button type="button" class="add_qual_criteria_btn mt-2 text-sm px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        เพิ่มกลุ่มเกณฑ์คุณภาพหลัก
                                    </button>
                                    <!-- Quality Main Criteria Template -->
                                    <div class="qual_criteria_block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200" draggable="true" data-draggable-level="quality-main">
                                        <input type="hidden" class="quality_main_id_value" value="">
                                        <div class="flex justify-between items-center mb-3">
                                            <h6 class="text-sm font-bold text-gray-900">กลุ่มเกณฑ์คุณภาพหลัก</h6>
                                            <div class="flex items-center space-x-3">
                                                <button type="button" class="drag_handle inline-flex items-center text-gray-500 hover:text-gray-700 transition duration-200" title="ลากเพื่อจัดลำดับ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="move_qual_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="move_qual_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="delete_qual_btn text-red-600 hover:text-red-800 transition duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                                <div class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
                                                    <span class="qual_main_sequence">1.1.1</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อกลุ่มเกณฑ์หลัก <span class="text-red-500">*</span></label>
                                                <input name="qual_name" class="qual_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200" placeholder="เช่น บทความวิจัย / ทรัพย์สินทางปัญญา / การประเมินการสอน">
                                                <p class="mt-2 text-xs text-gray-500">ชื่อนี้ควรเป็นชื่อกลุ่มของตัวเลือกย่อยหลายรายการ</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">สัดส่วน <span class="text-red-500">*</span></label>
                                                <input type="number" name="qual_ratio" class="qual_ratio border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200" placeholder="สัดส่วน %">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบายกลุ่ม</label>
                                            <textarea name="qual_tooltips" class="qual_tooltips richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200" placeholder="อธิบายว่ากลุ่มนี้ครอบคลุมผลงานประเภทใด หรือมีหลักเกณฑ์รวมอย่างไร"></textarea>
                                        </div>
                                        <label class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                            <input type="checkbox" class="qual_require_evidence h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                            <span>บังคับแนบหลักฐานเมื่อเลือกเกณฑ์นี้</span>
                                        </label>
                                        <label class="mb-4 ml-4 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                            <input type="checkbox" class="qual_allow_multiple h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                            <span>เลือกได้หลายตัวเลือก</span>
                                        </label>
                                        <!-- Quality Sub Criteria Container -->
                                        <div class="qual_sub_criterias_container space-y-3 pl-4 border-l-2 border-purple-200 mb-3">
                                            <div class="qual_sub_criteria_block bg-purple-50 p-3 rounded-lg" draggable="true" data-draggable-level="quality-sub">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-600">ตัวเลือกย่อยที่ผู้ใช้จะเลือกจริง</span>
                                                    <div class="flex items-center gap-3">
                                                        <button type="button" class="drag_handle inline-flex items-center text-gray-500 hover:text-gray-700 transition duration-200" title="ลากเพื่อจัดลำดับ">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="delete_qual_sub_btn text-red-600 hover:text-red-800 transition duration-200">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</label>
                                                        <div class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                                            <span class="qual_sub_sequence">1.1.1.1</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ชื่อตัวเลือกย่อย <span class="text-red-500">*</span></label>
                                                        <input name="qual_sub_name" class="qual_sub_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200" placeholder="เช่น ISI/SCOPUS Q1-2 ผู้ประพันธ์หลัก, สิทธิบัตร ผู้ถือสิทธิร่วม">
                                                        <p class="mt-2 text-xs text-gray-500">แต่ละรายการควรเป็นตัวเลือกที่ชัดเจน ไม่ควรเอาหลายเงื่อนไขมารวมกันในบรรทัดเดียว</p>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">คะแนนสูงสุด <span class="text-red-500">*</span></label>
                                                        <input type="number" name="num_score" class="num_score border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200" placeholder="คะแนนสูงสุด">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="block text-sm font-medium text-gray-600 mb-2">คำอธิบายการให้คะแนน</label>
                                                    <textarea name="qual_sub_description" rows="6" id="qual_sub_description_1"
                                                        class="qual_sub_description richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200"
                                                        placeholder="ระบุเงื่อนไขการได้คะแนน หรือคำอธิบายที่ช่วยให้ผู้เลือกไม่สับสน"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="add_qual_sub_criteria_btn text-sm px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            เพิ่มตัวเลือกย่อย
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="add_evaluation_list_btn mt-4 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            เพิ่มรายการประเมิน
                        </button>
