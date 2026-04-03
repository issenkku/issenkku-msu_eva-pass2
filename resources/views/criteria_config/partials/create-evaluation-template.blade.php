                            <div
                                class="evaluation_list_block bg-gray-100 p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300"
                                draggable="true" data-draggable-level="evaluation">
                                <div class="flex justify-between items-center mb-4">
                                    <h5 class="font-bold text-gray-900">รายการประเมิน</h5>
                                    <div class="flex items-center space-x-3">
                                        <button type="button"
                                            class="drag_handle inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 transition duration-200"
                                            title="ลากเพื่อจัดลำดับ">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                            </svg>
                                            <span class="text-xs font-medium">ลากจัดลำดับ</span>
                                        </button>
                                        <button type="button"
                                            class="move_eval_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200"
                                            disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                            class="move_eval_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                            class="delete_eval_btn text-red-600 hover:text-red-800 transition duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                        <div
                                            class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-gray-700">
                                            <span class="eval_sequence">1.1</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อรายการ <span
                                                class="text-red-500">*</span></label>
                                        <input required name="eval_name"
                                            class="eval_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition duration-200"
                                            placeholder="ชื่อรายการประเมิน">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">คะแนนรวม <span
                                                class="text-red-500">*</span></label>
                                        <input type="number" required name="sum_score"
                                            class="sum_score border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition duration-200"
                                            placeholder="คะแนนรวม">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                                        <input name="annotation"
                                            class="annotation border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition duration-200"
                                            placeholder="หมายเหตุ">
                                    </div>
                                </div>
                                <!-- Criteria Type Selection -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ประเภทเกณฑ์</label>
                                    <div class="criteria_type_check_group flex gap-6 text-gray-900">
                                        <label class="flex items-center">
                                            <input type="checkbox"
                                                class="criteria_type quantity_criteria_type form-checkbox h-5 w-5 text-green-600 rounded focus:ring-green-500"
                                                value="quantity">
                                            <span class="ml-2 text-sm">เกณฑ์ด้านปริมาณ</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox"
                                                class="criteria_type quality_criteria_type form-checkbox h-5 w-5 text-purple-600 rounded focus:ring-purple-500"
                                                value="quality">
                                            <span class="ml-2 text-sm">เกณฑ์ด้านคุณภาพ</span>
                                        </label>
                                    </div>
                                </div>

                                @include('criteria_config.partials.create-quantity-template')
                                @include('criteria_config.partials.create-quality-template')
                            </div>
                        </div>

                        <button type="button"
                            class="add_evaluation_list_btn mt-6 text-sm px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            เพิ่มรายการประเมิน
                        </button>
