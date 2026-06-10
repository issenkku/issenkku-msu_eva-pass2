                                <!-- Quality Criteria Section -->
                                <div
                                    class="quality_main_criterias_container space-y-4 pl-6 border-l-4 border-purple-400 hidden">
                                    <h6 class="font-bold text-gray-900 mb-3 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        เกณฑ์ด้านคุณภาพ
                                    </h6>
                                    <button type="button"
                                        class="add_qual_criteria_btn mt-2 text-sm px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        เพิ่มเกณฑ์คุณภาพหลัก
                                    </button>
                                    <div
                                        class="qual_criteria_block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                                        draggable="true" data-draggable-level="quality-main">
                                        <div class="flex justify-between items-center mb-3">
                                            <h6 class="text-sm font-bold text-gray-900">เกณฑ์คุณภาพหลัก</h6>
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
                                                    class="move_qual_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200"
                                                    disabled>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    class="move_qual_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    class="delete_qual_btn text-red-600 hover:text-red-800 transition duration-200">
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
                                                    <span class="qual_main_sequence">1.1.1</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span
                                                        class="text-red-500">*</span></div>
                                                <input name="qual_name"
                                                    class="qual_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="ชื่อเกณฑ์คุณภาพ">
                                            </div>
                                            <div>
                                                <div class="block text-sm font-medium text-gray-700 mb-2">สัดส่วน <span
                                                        class="text-red-500">*</span></div>
                                                <input type="number" name="qual_ratio"
                                                    class="qual_ratio border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="สัดส่วน">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div>
                                                <div class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย <span
                                                        class="text-red-500"></span></div>
                                                <textarea name="qual_tooltips" rows="8"
                                                    class="qual_tooltips richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="คำอธิบายเพิ่มเติม"></textarea>
                                            </div>
                                        </div>
                                        <label class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                            <input name="qual_require_evidence" type="checkbox" class="qual_require_evidence h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                            <span>บังคับแนบหลักฐานเมื่อเลือกเกณฑ์นี้</span>
                                        </label>
                                        <label class="mb-4 ml-4 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                            <input name="qual_allow_multiple" type="checkbox" class="qual_allow_multiple h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                            <span>เลือกได้หลายตัวเลือก</span>
                                        </label>
                                        <div
                                            class="qual_sub_criterias_container space-y-3 pl-4 border-l-2 border-purple-200 mb-3">
                                            <div class="qual_sub_criteria_block bg-gray-50 p-3 rounded-lg"
                                                draggable="true" data-draggable-level="quality-sub">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-600">เกณฑ์คุณภาพย่อย</span>
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
                                                            class="delete_qual_sub_btn text-red-600 hover:text-red-800 transition duration-200">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                                    <div>
                                                        <div
                                                            class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</div>
                                                        <div
                                                            class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                                            <span class="qual_sub_sequence">1.1.1.1</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div
                                                            class="block text-sm font-medium text-gray-600 mb-2">ชื่อเกณฑ์ย่อย
                                                            <span class="text-red-500">*</span></div>
                                                        <input name="qual_sub_name"
                                                            class="qual_sub_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="ชื่อเกณฑ์ย่อย">
                                                    </div>
                                                    <div>
                                                        <div
                                                            class="block text-sm font-medium text-gray-600 mb-2">คะแนนสูงสุด
                                                            <span class="text-red-500">*</span></div>
                                                        <input type="number" name="num_score"
                                                            class="num_score border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="คะแนนสูงสุด">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="block text-sm font-medium text-gray-600 mb-2">คำอธิบาย</div>
                                                    <textarea name="qual_sub_description" rows="6"
                                                        class="qual_sub_description richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200"
                                                        placeholder="ใส่คำอธิบายการให้คะแนน"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button"
                                            class="add_qual_sub_criteria_btn text-sm px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            เพิ่มคุณภาพย่อย
                                        </button>
                                    </div>
                                </div>
