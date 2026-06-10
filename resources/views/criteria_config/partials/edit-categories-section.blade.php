                <!-- Categories -->
                <div id="categories_container" class="space-y-8">
                    <!-- Category Template (hidden) -->
                    <div class="category_block bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" style="display: none;" draggable="true" data-draggable-level="category">
                        <input type="hidden" class="category_id_value" value="">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-xl text-gray-900">หมวดหมู่การประเมิน</h3>
                            <div class="flex items-center space-x-3">
                                <button type="button" class="drag_handle inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 transition duration-200" title="ลากเพื่อจัดลำดับ">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                    </svg>
                                    <span class="text-sm font-medium">ลากจัดลำดับ</span>
                                </button>
                                <button type="button" class="move_category_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button type="button" class="move_category_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <button type="button" class="delete_category_btn text-red-600 hover:text-red-800 transition duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <div class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</div>
                                <div class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-semibold text-gray-700">
                                    <span class="category_sequence">1</span>
                                </div>
                            </div>
                            <div>
                                <div class="block text-sm font-medium text-gray-700 mb-2">หมวดหลัก <span class="text-red-500">*</span></div>
                                <input name="main_category" required class="main_categories border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="เช่น งานบริการวิชาการ / งานวิจัย / งานสอน">
                                <p class="mt-2 text-xs text-gray-500">ใช้สำหรับแบ่งภาพรวมของงานในระดับใหญ่</p>
                            </div>
                            <div>
                                <div class="block text-sm font-medium text-gray-700 mb-2">หัวข้อย่อยของหมวด <span class="text-red-500">*</span></div>
                                <input name="sub_category" required class="sub_categories border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="เช่น ด้านคุณภาพผลงาน / ด้านปริมาณผลงาน">
                                <p class="mt-2 text-xs text-gray-500">ใช้แยกหัวข้อภายในหมวดหลักอีกชั้นหนึ่ง</p>
                            </div>
                        </div>
                        
                        <div class="evaluation_lists_container space-y-6 mt-8">
                            <h4 class="font-bold text-lg text-gray-900 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                รายการประเมิน
                            </h4>
                            @include('criteria_config.partials.edit-evaluation-template')
                    </div>
                </div>

                <button type="button" id="add_category_btn" class="my-6 px-5 py-2.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    เพิ่มหมวดหมู่การประเมิน
                </button>
