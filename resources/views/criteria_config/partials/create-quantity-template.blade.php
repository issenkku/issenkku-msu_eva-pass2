                                <!-- Quantity Criteria Section -->
                                <div
                                    class="quantity_main_criterias_container space-y-4 pl-6 border-l-4 border-green-400 hidden">
                                    <h6 class="font-bold text-gray-900 mb-3 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                        </svg>
                                        เกณฑ์ด้านปริมาณ
                                    </h6>
                                    <button type="button"
                                        class="add_quant_criteria_btn mt-2 text-sm px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        เพิ่มเกณฑ์ปริมาณหลัก
                                    </button>
                                    <div
                                        class="quant_criteria_block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                                        draggable="true" data-draggable-level="quantity-main">
                                        <div class="flex justify-between items-center mb-3">
                                            <h6 class="text-sm font-bold text-gray-900">เกณฑ์ปริมาณหลัก</h6>
                                            <div class="flex items-center space-x-3">
                                                <button type="button"
                                                    class="drag_handle inline-flex items-center text-gray-500 hover:text-gray-700 transition duration-200"
                                                    title="ลากเพื่อจัดลำดับ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    class="move_quant_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200"
                                                    disabled>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    class="move_quant_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    class="delete_quant_btn text-red-600 hover:text-red-800 transition duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                                            <div>
                                                <div class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</div>
                                                <div
                                                    class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
                                                    <span class="quant_main_sequence">1.1.1</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span
                                                        class="text-red-500">*</span></div>
                                                <input name="quant_name"
                                                    class="quant_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="ชื่อเกณฑ์ปริมาณ">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div>
                                                <div class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย <span
                                                        class="text-red-500"></span></div>
                                                <textarea name="quant_tooltips" rows="8"
                                                    class="quant_tooltips richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="คำอธิบายเพิ่มเติม"></textarea>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <div class="block text-sm font-medium text-gray-700 mb-2">กำหนดสูตร
                                                    <span class="text-red-500">*</span>
                                                </div>
                                                <span class="text-xs text-gray-500">(A=ค่าน้ำหนัก, B=ภาระงานมาตรฐาน, C=ภาระงานที่ทำได้, D=คะแนนที่คำนวณได้)</span>
                                                <textarea name="quant_formula" rows="3"
                                                    class="quant_formula border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="กำหนดสูตรการคำนวณ เช่น D = A × C / B">D = A × C / B</textarea>
                                            </div>
                                        </div>
                                        <div
                                            class="quant_sub_criteria_container space-y-3 pl-4 border-l-2 border-green-200 mb-3">
                                            <div class="quant_sub_criteria_block bg-gray-50 p-3 rounded-lg"
                                                draggable="true" data-draggable-level="quantity-sub">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-600">เกณฑ์ปริมาณย่อย</span>
                                                    <div class="flex items-center gap-3">
                                                        <button type="button"
                                                            class="drag_handle inline-flex items-center text-gray-500 hover:text-gray-700 transition duration-200"
                                                            title="ลากเพื่อจัดลำดับ">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                                            </svg>
                                                        </button>
                                                        <button type="button"
                                                            class="delete_quant_sub_btn text-red-600 hover:text-red-800 transition duration-200">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="quant_sub_criteria_id"
                                                    class="quant_sub_criteria_id" value="">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 md:items-end">
                                                    <div>
                                                        <div
                                                            class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</div>
                                                        <div
                                                            class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                                            <span class="quant_sub_sequence">1.1.1.1</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div
                                                            class="block text-sm font-medium text-gray-600 mb-2">ชื่อเกณฑ์ย่อย
                                                            <span class="text-red-500">*</span></div>
                                                        <input name="quant_sub_name"
                                                            class="quant_sub_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="ชื่อเกณฑ์ย่อย">
                                                    </div>
                                                    <div>
                                                        <div class="block text-sm font-medium text-gray-600 mb-2">ค่าน้ำหนักคะแนน (A)
                                                            <span class="text-red-500">*</span></div>
                                                        <input type="number" name="score_a"
                                                            class="score_a border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="คะแนน A">
                                                    </div>
                                                    <div>
                                                        <div class="block text-sm font-medium text-gray-600 mb-2">หน่วยภาระงานมาตรฐาน (B)
                                                            <span class="text-red-500">*</span></div>
                                                        <input type="number" name="score_b"
                                                            class="score_b border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="คะแนน B">
                                                    </div>
                                                    <div class="flex flex-col items-start sm:items-end">
                                                        <span
                                                            class="block text-sm font-medium text-gray-600 mb-2 opacity-0 select-none">spacer</span>
                                                        <a href="/workload-config" class="quant_sub_setting_btn inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200" title="ตั้งค่าเกณฑ์ย่อย">
                                                          
                                                            <span class="text-sm font-semibold">ตั้งค่าภาระงาน</span>
                                                        </a>
                                                    </div>
                                                </div>
                                                <label class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                                    <input name="quant_require_evidence" type="checkbox" class="quant_require_evidence h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                    <span>บังคับแนบหลักฐานก่อนบันทึกภาระงาน</span>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button"
                                            class="add_quant_sub_criteria_btn text-sm px-3 py-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            เพิ่มปริมาณย่อย
                                        </button>
                                    </div>
                                </div>
