@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\criteria_config\create.blade.php --}}

@section('content')
    {{-- บล็อกเนื้อหา --}}
    <div class="py-12 bg-gradient-to-r from-blue-50 to-indigo-50 min-h-screen">
        {{-- บล็อกเนื้อหา --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-3">สร้างเกณฑ์การประเมินใหม่</h1>
                <p class="text-gray-600 text-lg">กรุณากรอกข้อมูลเกณฑ์การประเมินให้ครบถ้วนเพื่อสร้างเกณฑ์ที่สมบูรณ์</p>
            </div>

            {{-- ฟอร์ม --}}
            <form id="jsonForm" action="{{ route('report-structure.store') }}" method="POST" class="space-y-8" novalidate>
                @csrf

                <!-- Report Datas -->
                <div
                    class="report_datas_block bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h2 class="font-bold text-2xl text-gray-900 mb-6 flex items-center">
                        <span
                            class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">1</span>
                        ข้อมูลเกณฑ์การประเมิน
                    </h2>
                    <div class="space-y-6">
                        <!-- ซ่อนช่องกรอกปีผู้ประเมิน -->
                        <div style="display: none;">
                            <input id="version_name" name="version_name" class="version_name" type="text">
                            <input type="hidden" id="auth-user-id" value="{{ Auth::user()->id }}">
                        </div>
                        <div>
                            <label for="report_title" class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span
                                    class="text-red-500">*</span></label>
                            <input id="report_title" required name="report_title"
                                class="report_title border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200"
                                placeholder="ชื่อเกณฑ์การประเมิน">
                        </div>
                        <div>
                            <label for="report_description"
                                class="block text-sm font-medium text-gray-700 mb-2">รายละเอียดเกณฑ์ <span
                                    class="text-red-500"></span></label>
                            <textarea id="report_description" rows="4"  name="report_description"
                                class="report_description border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200"
                                placeholder="รายละเอียดเพิ่มเติมของเกณฑ์"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="assessment_type"
                                    class="block text-sm font-medium text-gray-700 mb-2">ประเภทการประเมิน <span
                                        class="text-red-500">*</span></label>
                                <select id="assessment_type" required name="assessment_type"
                                    class="assessment_type border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200">
                                    <option value="">-- เลือกประเภทการประเมิน --</option>
                                    <option value="กลุ่มวิชาการ">กลุ่มวิชาการ</option>
                                    <option value="กลุ่มสนับสนุน">กลุ่มสนับสนุน</option>
                                    <option value="กลุ่มบริหาร">กลุ่มบริหาร</option>
                                </select>
                            </div>
                            <div>
                                <label for="comment"
                                    class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                                <input id="comment"
                                    class="comment border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200"
                                    placeholder="หมายเหตุ">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div id="categories_container" class="space-y-8">
                    <h2 class="font-bold text-2xl text-gray-900 mb-4 flex items-center">
                        <span
                            class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">2</span>
                        หมวดหมู่การประเมิน
                    </h2>
                    <!-- Category Block -->
                    <div
                        class="category_block bg-white p-8 rounded-xl shadow-lg border-l-4 border-blue-600 hover:shadow-xl transition-shadow duration-300"
                        draggable="true" data-draggable-level="category">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-xl text-gray-900">หมวดหมู่การประเมิน</h3>
                            <div class="flex items-center space-x-3">
                                <button type="button"
                                    class="drag_handle inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 transition duration-200"
                                    title="ลากเพื่อจัดลำดับ">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                    </svg>
                                    <span class="text-sm font-medium">ลากจัดลำดับ</span>
                                </button>
                                <button type="button"
                                    class="move_category_up_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200"
                                    disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button type="button"
                                    class="move_category_down_btn hidden text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <button type="button"
                                    class="delete_category_btn text-red-600 hover:text-red-800 transition duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                <div
                                    class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-semibold text-gray-700">
                                    <span class="category_sequence">1</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่หลัก <span
                                        class="text-red-500">*</span></label>
                                <input required
                                    class="main_categories border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200"
                                    placeholder="ชื่อหมวดหมู่หลัก">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่ย่อย <span
                                        class="text-red-500">*</span></label>
                                <input required
                                    class="sub_categories border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200"
                                    placeholder="ชื่อหมวดหมู่ย่อย">
                            </div>
                        </div>
                        <div class="evaluation_lists_container space-y-6 mt-8">
                            <h4 class="font-bold text-lg text-gray-900 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                รายการประเมิน
                            </h4>
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
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                                <div
                                                    class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
                                                    <span class="quant_main_sequence">1.1.1</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span
                                                        class="text-red-500">*</span></label>
                                                <input name="quant_name"
                                                    class="quant_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="ชื่อเกณฑ์ปริมาณ">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย <span
                                                        class="text-red-500"></span></label>
                                                <textarea name="quant_tooltips" rows="8" id="quant_tooltips_1"
                                                    class="quant_tooltips richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="คำอธิบายเพิ่มเติม"></textarea>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">กำหนดสูตร
                                                    <span class="text-red-500">*</span>
                                                </label>
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
                                                        <label
                                                            class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</label>
                                                        <div
                                                            class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                                            <span class="quant_sub_sequence">1.1.1.1</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-600 mb-2">ชื่อเกณฑ์ย่อย
                                                            <span class="text-red-500">*</span></label>
                                                        <input name="quant_sub_name"
                                                            class="quant_sub_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="ชื่อเกณฑ์ย่อย">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ค่าน้ำหนักคะแนน (A)
                                                            <span class="text-red-500">*</span></label>
                                                        <input type="number" name="score_a"
                                                            class="score_a border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="คะแนน A">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">หน่วยภาระงานมาตรฐาน (B)
                                                            <span class="text-red-500">*</span></label>
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
                                                    <input type="checkbox" class="quant_require_evidence h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
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
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                                <div
                                                    class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700">
                                                    <span class="qual_main_sequence">1.1.1</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span
                                                        class="text-red-500">*</span></label>
                                                <input name="qual_name"
                                                    class="qual_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="ชื่อเกณฑ์คุณภาพ">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">สัดส่วน <span
                                                        class="text-red-500">*</span></label>
                                                <input type="number" name="qual_ratio"
                                                    class="qual_ratio border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="สัดส่วน">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย <span
                                                        class="text-red-500"></span></label>
                                                <textarea name="qual_tooltips" rows="8" id="qual_tooltips_1"
                                                    class="qual_tooltips richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200"
                                                    placeholder="คำอธิบายเพิ่มเติม"></textarea>
                                            </div>
                                        </div>
                                        <label class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                            <input type="checkbox" class="qual_require_evidence h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                            <span>บังคับแนบหลักฐานเมื่อเลือกเกณฑ์นี้</span>
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
                                                        <label
                                                            class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</label>
                                                        <div
                                                            class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                                            <span class="qual_sub_sequence">1.1.1.1</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-600 mb-2">ชื่อเกณฑ์ย่อย
                                                            <span class="text-red-500">*</span></label>
                                                        <input name="qual_sub_name"
                                                            class="qual_sub_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="ชื่อเกณฑ์ย่อย">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-600 mb-2">คะแนนสูงสุด
                                                            <span class="text-red-500">*</span></label>
                                                        <input type="number" name="num_score"
                                                            class="num_score border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200"
                                                            placeholder="คะแนนสูงสุด">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="block text-sm font-medium text-gray-600 mb-2">คำอธิบาย</label>
                                                    <textarea name="qual_sub_description" rows="6" id="qual_sub_description_1"
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
                    </div>
                </div>
                <!-- END Category Block -->

                <button type="button" id="add_category_btn"
                    class="my-6 px-5 py-2.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    เพิ่มหมวดหมู่การประเมิน
                </button>

                <div class="flex justify-end mt-10 space-x-4">
                    <button type="button" id="reset_form_btn"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        ล้างฟอร์ม
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading_overlay"
        class="fixed inset-0  bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
        {{-- บล็อกเนื้อหา --}}
        <div class="bg-white p-6 rounded-lg shadow-xl text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-700 text-lg">กำลังส่งข้อมูล กรุณารอสักครู่...</p>
        </div>
    </div>
    <!-- Confirmation Modal -->
    <div id="confirm_modal"
        class="fixed inset-0 bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
        {{-- บล็อกเนื้อหา --}}
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full">
            <div class="text-center">
                <div class="bg-blue-100 rounded-full p-4 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">ยืนยันการบันทึกข้อมูล</h3>
                <p class="text-gray-600 mb-3">ชื่อเกณฑ์: <span id="version_name_display" class="font-medium"></span></p>
                <p class="text-gray-600 mb-6">คุณต้องการบันทึกข้อมูลเกณฑ์การประเมินนี้หรือไม่?</p>
                <div class="flex justify-center space-x-4">
                    <button id="cancel_modal_btn"
                        class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">ยกเลิก</button>
                    <button id="confirm_submit_btn"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Success Modal -->
    <div id="success_modal"
        class="fixed inset-0 bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
        {{-- บล็อกเนื้อหา --}}
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full">
            <div class="text-center">
                <div class="bg-green-100 rounded-full p-4 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">ส่งข้อมูลสำเร็จ</h3>
                <p class="text-gray-600 mb-6">ข้อมูลเกณฑ์การประเมินถูกบันทึกเรียบร้อยแล้ว</p>
                <p class="text-gray-500 text-sm mb-6">กำลังเปลี่ยนเส้นทางใน <span id="countdown">5</span> วินาที...</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('criteria_config.index') }}"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">ไปหน้ารายการเกณฑ์</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize Summernote for rich text editors
        function initializeSummernote() {
            $('.richtext-editor').each(function() {
                const $editor = $(this);
                let placeholder = 'กรุณาใส่คำอธิบายเพิ่มเติม...';
                
                // Use specific placeholder for quality sub criteria description
                if ($editor.hasClass('qual_sub_description')) {
                    placeholder = 'ใส่คำอธิบายการให้คะแนน';
                }
                
                $editor.summernote({
                    height: 250,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    placeholder: placeholder,
                    lang: 'th-TH',
                    callbacks: {
                        onChange: function(contents, $editable) {
                            // Update the textarea value when content changes
                            $(this).val(contents);
                        }
                    }
                });
            });
        }

        // Initialize Summernote when document is ready
        $(document).ready(function() {
            setTimeout(function() {
                initializeSummernote();
            }, 100);
        });

        function cloneAndClear(blockSelector) {
            let node = document.querySelector(blockSelector).cloneNode(true);
            
            // Destroy Summernote instances from cloned node
            $(node).find('.richtext-editor').each(function() {
                const $editor = $(this);
                
                // Remove Summernote wrapper if it exists
                if ($editor.parent().hasClass('note-editor')) {
                    // Get the original textarea
                    const content = $editor.summernote('code');
                    $editor.summernote('destroy');
                    $editor.val(''); // Clear content after destroying
                } else if ($editor.next().hasClass('note-editor')) {
                    // Handle case where editor wrapper is a sibling
                    $editor.next('.note-editor').remove();
                    $editor.val('');
                }
                
                // Remove any remaining note-editor wrappers
                $(this).siblings('.note-editor').remove();
                $(this).parent('.note-editor').children('textarea').unwrap();
                
                // Generate new unique ID for cloned editor
                const newId = 'editor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                this.id = newId;
                this.value = ''; // Clear content
                
                // Remove any Summernote classes
                $(this).removeClass('note-editor note-frame note-airframe');
            });
            
            node.querySelectorAll('input[type="checkbox"]').forEach(inp => inp.checked = false);
            node.querySelectorAll('input:not([type="checkbox"])').forEach(inp => inp.value = '');
            node.querySelectorAll('textarea:not(.quant_formula):not(.richtext-editor)').forEach(textarea => textarea.value = '');
            node.querySelectorAll('textarea.quant_formula').forEach(textarea => textarea.value = 'D = A × C / B');
            node.querySelectorAll(
                '.evaluation_list_block:not(:first-child), .quant_criteria_block:not(:first-child), .qual_criteria_block:not(:first-child), .quant_sub_criteria_block:not(:first-child), .qual_sub_criteria_block:not(:first-child)'
            ).forEach(e => e.remove());

            if (blockSelector === '.evaluation_list_block') {
                const container = document.querySelector('.evaluation_lists_container');
                const index = container.querySelectorAll('.evaluation_list_block').length + 1;
                setSequenceInputValue(node.querySelector('.eval_sequence'), index);
                node.querySelector('.quantity_main_criterias_container').classList.add('hidden');
                node.querySelector('.quality_main_criterias_container').classList.add('hidden');
            }
            if (blockSelector === '.category_block') {
                const container = document.getElementById('categories_container');
                const index = container.querySelectorAll('.category_block').length + 1;
                setSequenceInputValue(node.querySelector('.category_sequence'), index);
            }
            
            return node;
        }

        function updateButtonStates(containerSelector, upBtnSelector, downBtnSelector) {
            const items = document.querySelectorAll(containerSelector);
            items.forEach((item, index) => {
                const upBtn = item.querySelector(upBtnSelector);
                const downBtn = item.querySelector(downBtnSelector);
                upBtn.disabled = index === 0;
                downBtn.disabled = index === items.length - 1;
            });
        }

        function bindSequenceInputs() {}

        function setSequenceInputValue(input, value) {
            if (!input) {
                return;
            }

            input.textContent = String(value);
        }

        function getSequenceValue(block, selector, fallback) {
            const input = block.querySelector(selector);
            return (input?.textContent || '').trim() || String(fallback);
        }

        function parseSequenceValue(value) {
            return String(value || '')
                .split('.')
                .map((part) => {
                    const parsed = Number(part);
                    return Number.isFinite(parsed) ? parsed : Number.MAX_SAFE_INTEGER;
                });
        }

        function compareSequenceValues(left, right) {
            const leftParts = parseSequenceValue(left);
            const rightParts = parseSequenceValue(right);
            const maxLength = Math.max(leftParts.length, rightParts.length);

            for (let index = 0; index < maxLength; index++) {
                const leftPart = leftParts[index] ?? -1;
                const rightPart = rightParts[index] ?? -1;
                if (leftPart !== rightPart) {
                    return leftPart - rightPart;
                }
            }

            return String(left).localeCompare(String(right), undefined, { numeric: true });
        }

        function updateEvalSequence(container, categoryPrefix = '') {
            container.querySelectorAll('.evaluation_list_block').forEach((evalBlock, index) => {
                const evalPrefix = categoryPrefix ? `${categoryPrefix}.${index + 1}` : `${index + 1}`;
                setSequenceInputValue(evalBlock.querySelector('.eval_sequence'), evalPrefix);

                const quantityContainer = evalBlock.querySelector('.quantity_main_criterias_container');
                const qualityContainer = evalBlock.querySelector('.quality_main_criterias_container');
                if (quantityContainer) {
                    updateQuantMainSequence(quantityContainer, evalPrefix);
                }
                if (qualityContainer) {
                    updateQualMainSequence(qualityContainer, evalPrefix);
                }
            });
        }

        function updateCategorySequence(container) {
            container.querySelectorAll('.category_block').forEach((catBlock, index) => {
                const categoryPrefix = `${index + 1}`;
                setSequenceInputValue(catBlock.querySelector('.category_sequence'), categoryPrefix);

                const evalContainer = catBlock.querySelector('.evaluation_lists_container');
                if (evalContainer) {
                    updateEvalSequence(evalContainer, categoryPrefix);
                }
            });
        }

        function updateQuantMainSequence(container, evalPrefix = '') {
            container.querySelectorAll('.quant_criteria_block').forEach((block, idx) => {
                const quantPrefix = evalPrefix ? `${evalPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.quant_main_sequence'), quantPrefix);

                const subContainer = block.querySelector('.quant_sub_criteria_container');
                if (subContainer) {
                    updateQuantSubSequence(subContainer, quantPrefix);
                }
            });
        }

        function updateQuantSubSequence(container, quantPrefix = '') {
            container.querySelectorAll('.quant_sub_criteria_block').forEach((block, idx) => {
                const subPrefix = quantPrefix ? `${quantPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.quant_sub_sequence'), subPrefix);
            });
            updateQuantSubSettingLinks();
        }

        function updateQuantSubSettingLinks() {
            const baseUrl = '/workload-config';
            document.querySelectorAll('.quant_sub_criteria_block').forEach(block => {
                const idInput = block.querySelector('.quant_sub_criteria_id');
                const link = block.querySelector('.quant_sub_setting_btn');
                if (!link) {
                    return;
                }
                const idValue = idInput ? idInput.value.trim() : '';
                link.setAttribute('href', idValue ? `${baseUrl}?quant_sub_criteria_id=${encodeURIComponent(idValue)}` : baseUrl);
            });
        }

        function updateQualMainSequence(container, evalPrefix = '') {
            container.querySelectorAll('.qual_criteria_block').forEach((block, idx) => {
                const qualPrefix = evalPrefix ? `${evalPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.qual_main_sequence'), qualPrefix);

                const subContainer = block.querySelector('.qual_sub_criterias_container');
                if (subContainer) {
                    updateQualSubSequence(subContainer, qualPrefix);
                }
            });
        }

        function updateQualSubSequence(container, qualPrefix = '') {
            container.querySelectorAll('.qual_sub_criteria_block').forEach((block, idx) => {
                const subPrefix = qualPrefix ? `${qualPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.qual_sub_sequence'), subPrefix);
            });
        }

        let draggedBlock = null;

        function refreshOrderUI() {
            updateCategorySequence(document.getElementById('categories_container'));
            updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
            updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
            updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
            updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
        }

        document.addEventListener('pointerdown', function (e) {
            document.querySelectorAll('[data-drag-armed="true"]').forEach((block) => {
                delete block.dataset.dragArmed;
            });

            const handle = e.target.closest('.drag_handle');
            if (!handle) {
                return;
            }

            const block = handle.closest('[data-draggable-level]');
            if (block) {
                block.dataset.dragArmed = 'true';
            }
        });

        document.addEventListener('pointerup', function () {
            document.querySelectorAll('[data-drag-armed="true"]').forEach((block) => {
                delete block.dataset.dragArmed;
            });
        });

        document.addEventListener('dragstart', function (e) {
            const block = e.target.closest('[data-draggable-level]');
            if (!block || block.dataset.dragArmed !== 'true') {
                e.preventDefault();
                return;
            }

            draggedBlock = block;
            block.classList.add('opacity-60');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', block.dataset.draggableLevel || 'move');
        });

        document.addEventListener('dragover', function (e) {
            if (!draggedBlock) {
                return;
            }

            const target = e.target.closest('[data-draggable-level]');
            if (!target || target === draggedBlock) {
                return;
            }

            if (target.dataset.draggableLevel !== draggedBlock.dataset.draggableLevel || target.parentElement !== draggedBlock.parentElement) {
                return;
            }

            e.preventDefault();
            const rect = target.getBoundingClientRect();
            const insertAfter = (e.clientY - rect.top) > (rect.height / 2);
            target.parentElement.insertBefore(draggedBlock, insertAfter ? target.nextElementSibling : target);
            refreshOrderUI();
        });

        document.addEventListener('dragend', function () {
            if (draggedBlock) {
                draggedBlock.classList.remove('opacity-60');
                delete draggedBlock.dataset.dragArmed;
            }
            draggedBlock = null;
        });

        function showLoading() {
            document.getElementById('loading_overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading_overlay').classList.add('hidden');
        }

        function showConfirmModal(reportTitle) {
            document.getElementById('version_name_display').textContent = reportTitle || 'ไม่ระบุ';
            document.getElementById('confirm_modal').classList.remove('hidden');
        }

        // Modal-based alert for validation error
        function showValidationErrorModal(message) {
            let modal = document.getElementById('custom-alert-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'custom-alert-modal';
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
                modal.style.background = 'rgba(0,0,0,0.6)';
                modal.innerHTML = `
                    <div id="custom-alert-box" class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center animate-fade-in">
                        <div class="flex justify-center mb-4">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100">
                                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </span>
                        </div>
                        <div class="text-lg font-semibold mb-2 text-red-600">กรอกข้อมูลไม่ครบถ้วน</div>
                        <div class="mb-4 text-gray-700">${message}</div>
                        <button id="custom-alert-ok" class="mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">ตกลง</button>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
                modal.style.background = 'rgba(0,0,0,0.6)';
                modal.querySelector('#custom-alert-box').className = `bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center animate-fade-in`;
                modal.querySelector('#custom-alert-box').innerHTML = `
                    <div class="flex justify-center mb-4">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100">
                            <svg class=\"w-7 h-7 text-red-500\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M6 18L18 6M6 6l12 12\"/></svg>
                        </span>
                    </div>
                    <div class="text-lg font-semibold mb-2 text-red-600">กรอกข้อมูลไม่ครบถ้วน</div>
                    <div class="mb-4 text-gray-700">${message}</div>
                    <button id=\"custom-alert-ok\" class=\"mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none\">ตกลง</button>
                `;
                modal.style.display = '';
            }
            modal.querySelector('#custom-alert-ok').onclick = function() {
                modal.style.display = 'none';
            };
        }
        // // Validate required fields before showing confirm modal
        document.getElementById('jsonForm').addEventListener('submit', function(e) {
            // Prevent default submit for custom validation
            e.preventDefault();
            // Basic required fields (ไม่ต้องตรวจสอบ version_name อีกต่อไป)
            const reportTitle = document.getElementById('report_title').value.trim();
            const reportDescription = document.getElementById('report_description').value.trim();
            const assessmentType = document.getElementById('assessment_type').value.trim();
            let errorMsg = '';
            if (!reportTitle) errorMsg += 'กรุณากรอกชื่อเกณฑ์\n';
            if (!assessmentType) errorMsg += 'กรุณาเลือกประเภทการประเมิน\n';
            if (errorMsg) {
                showValidationErrorModal(errorMsg.replace(/\n/g, '<br>'));
                return false;
            }
            // Generate version_name automatically
            const currentYear = new Date().getFullYear() + 543; // Convert to Buddhist Era
            const versionName = `${currentYear}_AUTO`;
            document.getElementById('version_name').value = "เกณฑ์เวอร์ชั่น" + versionName;

            // If valid, show confirm modal
            showConfirmModal(reportTitle); // Show report title instead of version name
        }, true);

        function hideConfirmModal() {
            document.getElementById('confirm_modal').classList.add('hidden');
        }

        function showSuccessModal() {
            document.getElementById('success_modal').classList.remove('hidden');
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const interval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.location.href = "{{ route('criteria_config.index') }}";
                }
            }, 1000);
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete_category_btn')) {
                const block = e.target.closest('.category_block');
                const container = document.getElementById('categories_container');
                if (confirm('ต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) {
            if (container.querySelectorAll('.category_block').length > 1) {
                block.remove();
                updateCategorySequence(container);
                updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
            } else {
                showValidationErrorModal('ต้องมีหมวดหมู่การประเมินอย่างน้อย 1 รายการ');
            }
                }
            }

            if (e.target.closest('.delete_eval_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
            if (container.querySelectorAll('.evaluation_list_block').length > 1) {
                block.remove();
                updateEvalSequence(container);
                updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
            } else {
                showValidationErrorModal('ต้องมีรายการประเมินอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_quant_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
            if (container.querySelectorAll('.quant_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(block).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                block.remove();
                updateQuantMainSequence(container);
                updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์ปริมาณหลักอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_quant_sub_btn')) {
                const block = e.target.closest('.quant_sub_criteria_block');
                const container = block.closest('.quant_sub_criteria_container');
            if (container.querySelectorAll('.quant_sub_criteria_block').length > 1) {
                block.remove();
                updateQuantSubSequence(container);
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์ปริมาณย่อยอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_qual_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
            if (container.querySelectorAll('.qual_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(block).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                block.remove();
                updateQualMainSequence(container);
                updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์คุณภาพหลักอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_qual_sub_btn')) {
                const block = e.target.closest('.qual_sub_criteria_block');
                const container = block.closest('.qual_sub_criterias_container');
            if (container.querySelectorAll('.qual_sub_criteria_block').length > 1) {
                block.remove();
                updateQualSubSequence(container);
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์คุณภาพย่อยอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.move_category_up_btn')) {
                const block = e.target.closest('.category_block');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('category_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                    updateCategorySequence(document.getElementById('categories_container'));
                }
            }

            if (e.target.closest('.move_category_down_btn')) {
                const block = e.target.closest('.category_block');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('category_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                    updateCategorySequence(document.getElementById('categories_container'));
                }
            }

            if (e.target.closest('.move_eval_up_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('evaluation_list_block')) {
                    container.insertBefore(block, previous);
                    updateEvalSequence(container);
                    updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                }
            }

            if (e.target.closest('.move_eval_down_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('evaluation_list_block')) {
                    container.insertBefore(next, block);
                    updateEvalSequence(container);
                    updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                }
            }

            if (e.target.closest('.move_quant_up_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('quant_criteria_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                    updateQuantMainSequence(container);
                }
            }

            if (e.target.closest('.move_quant_down_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('quant_criteria_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                    updateQuantMainSequence(container);
                }
            }

            if (e.target.closest('.move_qual_up_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('qual_criteria_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                    updateQualMainSequence(container);
                }
            }

            if (e.target.closest('.move_qual_down_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('qual_criteria_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                    updateQualMainSequence(container);
                }
            }

            if (e.target.closest('#add_category_btn')) {
                let newBlock = cloneAndClear('.category_block');
                document.getElementById('categories_container').appendChild(newBlock);
                updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                updateCategorySequence(document.getElementById('categories_container'));
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_evaluation_list_btn')) {
                let parent = e.target.closest('.category_block').querySelector('.evaluation_lists_container');
                let newBlock = cloneAndClear('.evaluation_list_block');
                parent.appendChild(newBlock);
                updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                updateEvalSequence(parent);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_quant_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quantity_main_criterias_container');
                let newBlock = cloneAndClear('.quant_criteria_block');
                parent.appendChild(newBlock);
                updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                updateQuantMainSequence(parent);
                
                // Initialize Summernote for new rich text editors
                setTimeout(function() {
                    $(newBlock).find('.richtext-editor').summernote({
                        height: 250,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'กรุณาใส่คำอธิบายเพิ่มเติม...',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_quant_sub_criteria_btn')) {
                let parent = e.target.closest('.quant_criteria_block').querySelector(
                    '.quant_sub_criteria_container');
                let newBlock = cloneAndClear('.quant_sub_criteria_block');
                parent.appendChild(newBlock);
                updateQuantSubSequence(parent);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_qual_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quality_main_criterias_container');
                let newBlock = cloneAndClear('.qual_criteria_block');
                parent.appendChild(newBlock);
                updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                updateQualMainSequence(parent);
                
                // Initialize Summernote for new rich text editors
                setTimeout(function() {
                    $(newBlock).find('.richtext-editor').summernote({
                        height: 250,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'กรุณาใส่คำอธิบายเพิ่มเติม...',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_qual_sub_criteria_btn')) {
                let parent = e.target.closest('.qual_criteria_block').querySelector(
                    '.qual_sub_criterias_container');
                let newBlock = cloneAndClear('.qual_sub_criteria_block');
                parent.appendChild(newBlock);
                updateQualSubSequence(parent);
                
                // Initialize Summernote for new rich text editors in the new block
                setTimeout(function() {
                    $(newBlock).find('.qual_sub_description.richtext-editor').summernote({
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'ใส่คำอธิบายการให้คะแนน',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('criteria_type')) {
                const evalBlock = e.target.closest('.evaluation_list_block');
                const quantityContainer = evalBlock.querySelector('.quantity_main_criterias_container');
                const qualityContainer = evalBlock.querySelector('.quality_main_criterias_container');
                const quantityCheckbox = evalBlock.querySelector('.quantity_criteria_type');
                const qualityCheckbox = evalBlock.querySelector('.quality_criteria_type');
                quantityContainer.classList.toggle('hidden', !quantityCheckbox.checked);
                qualityContainer.classList.toggle('hidden', !qualityCheckbox.checked);
            }
        });

        document.getElementById('reset_form_btn').addEventListener('click', function() {
            showValidationErrorModal('ต้องการล้างข้อมูลทั้งหมดใช่หรือไม่? <br><br><button id="confirm-reset-btn" class="mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">ยืนยัน</button>');
            setTimeout(() => {
                const confirmBtn = document.getElementById('confirm-reset-btn');
                if (confirmBtn) {
                    confirmBtn.onclick = function() {
                        document.getElementById('custom-alert-modal').style.display = 'none';
                        document.getElementById('jsonForm').reset();
                        document.querySelectorAll('.quantity_main_criterias_container, .quality_main_criterias_container')
                            .forEach(container => container.classList.add('hidden'));
                        updateCategorySequence(document.getElementById('categories_container'));
                        document.querySelectorAll('.evaluation_lists_container').forEach(updateEvalSequence);
                        document.querySelectorAll('.quantity_main_criterias_container').forEach(updateQuantMainSequence);
                        document.querySelectorAll('.quant_sub_criteria_container').forEach(updateQuantSubSequence);
                        document.querySelectorAll('.quality_main_criterias_container').forEach(updateQualMainSequence);
                        document.querySelectorAll('.qual_sub_criterias_container').forEach(updateQualSubSequence);
                    };
                }
            }, 100);
        });

        let finalData = null;

        document.getElementById('jsonForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // ไม่ต้องตรวจสอบ version_name เพราะจะ generate อัตโนมัติ
            const reportTitle = document.querySelector('.report_title').value.trim();
            const reportDescription = document.querySelector('.report_description').value.trim();
            if (!reportTitle) {
                //alert('กรุณากรอกชื่อเกณฑ์และรายละเอียดเกณฑ์');
                return;
            }

            // Generate version_name อัตโนมัติ
            const currentYear = new Date().getFullYear() + 543; // Convert to Buddhist Era
            const versionName = `เกณฑ์ประเมินปี ${currentYear} ครั้งที่ AUTO`;
            
            // Save all Summernote content back to textareas before collecting data
            $('.richtext-editor').each(function() {
                if ($(this).hasClass('note-editor')) {
                    $(this).val($(this).summernote('code'));
                }
            });
            
            finalData = {
                version_name: versionName, // สร้างชื่ออัตโนมัติ
                created_by: document.getElementById('auth-user-id')?.value || 1,
                report_datas: [],
                categories: []
            };

            let rd = document.querySelector('.report_datas_block');
            const assessmentType = rd.querySelector('.assessment_type').value || null;
            finalData.report_datas.push({
                report_title: reportTitle,
                report_description: reportDescription || null ,
                assessment_type: assessmentType,
                comment: rd.querySelector('.comment').value || null
            });

            Array.from(document.querySelectorAll('#categories_container .category_block'))
                .sort((left, right) => compareSequenceValues(
                    getSequenceValue(left, '.category_sequence', 1),
                    getSequenceValue(right, '.category_sequence', 1)
                ))
                .forEach((catBlock, catI) => {
                const mainCategories = catBlock.querySelector('.main_categories').value.trim();
                const subCategories = catBlock.querySelector('.sub_categories').value.trim();
                if (!mainCategories || !subCategories) {
                    //alert(`กรุณากรอกหมวดหมู่หลักและหมวดหมู่ย่อยสำหรับหมวดหมู่ที่ ${catI + 1}`);
                    return;
                }

                let category = {
                    main_categories: mainCategories,
                    sub_categories: subCategories,
                    sequence: catI + 1,
                    evaluation_lists: []
                };

                Array.from(catBlock.querySelectorAll('.evaluation_lists_container .evaluation_list_block'))
                    .sort((left, right) => compareSequenceValues(
                        getSequenceValue(left, '.eval_sequence', `${catI + 1}.1`),
                        getSequenceValue(right, '.eval_sequence', `${catI + 1}.1`)
                    ))
                    .forEach((
                    evalBlock, evalI) => {
                    const evalName = evalBlock.querySelector('.eval_name').value.trim();
                    const sumScore = evalBlock.querySelector('.sum_score').value;
                    if (!evalName || !sumScore) {
                        // alert(
                        //     `กรุณากรอกชื่อรายการประเมินและคะแนนรวมสำหรับรายการที่ ${evalI + 1} ในหมวดหมู่ที่ ${catI + 1}`
                        // );
                        return;
                    }

                    const quantityChecked = evalBlock.querySelector('.quantity_criteria_type')
                        .checked;
                    const qualityChecked = evalBlock.querySelector('.quality_criteria_type')
                        .checked;

                    let evalList = {
                        name: evalName,
                        sum_score: Number(sumScore),
                        sequence: evalI + 1,
                        annotation: evalBlock.querySelector('.annotation').value || null,
                        quantity_main_criterias: [],
                        quality_main_criterias: []
                    };

                    if (quantityChecked) {
                        let valid = true;
                        Array.from(evalBlock.querySelectorAll(
                            '.quantity_main_criterias_container .quant_criteria_block'))
                            .sort((left, right) => compareSequenceValues(
                                getSequenceValue(left, '.quant_main_sequence', `${catI + 1}.${evalI + 1}.1`),
                                getSequenceValue(right, '.quant_main_sequence', `${catI + 1}.${evalI + 1}.1`)
                            ))
                            .forEach(
                            (qMain, qj) => {
                                const quantName = qMain.querySelector('.quant_name').value
                                    .trim();
                                // Get content from Summernote editor if available, otherwise from textarea
                                const tooltipsTextarea = qMain.querySelector('.quant_tooltips');
                                const quantTooltips = $(tooltipsTextarea).hasClass('note-editor')
                                    ? $(tooltipsTextarea).summernote('code') 
                                    : tooltipsTextarea.value.trim();
                                const quantFormula = qMain.querySelector('.quant_formula')?.value.trim() || '';
                                if (!quantName) {
                                    // alert(
                                    //     `กรุณากรอกชื่อเกณฑ์และคำอธิบายสำหรับเกณฑ์ปริมาณหลักที่ ${qj + 1} ในรายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`
                                    // );
                                    valid = false;
                                    return;
                                }

                                let quantMain = {
                                    name: quantName,
                                    tooltips: quantTooltips || null,
                                    description: qMain.querySelector('.quant_description')?.value.trim() || '',
                                    sequence: qj + 1,
                                    formula: quantFormula,
                                    quantity_sub_criterias: []
                                };

                                Array.from(qMain.querySelectorAll(
                                    '.quant_sub_criteria_container .quant_sub_criteria_block'
                                ))
                                    .sort((left, right) => compareSequenceValues(
                                        getSequenceValue(left, '.quant_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`),
                                        getSequenceValue(right, '.quant_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`)
                                    ))
                                    .forEach((subQ, sk) => {
                                    const subName = subQ.querySelector(
                                        '.quant_sub_name').value.trim();
                                    const scoreA = subQ.querySelector('.score_a').value;
                                    const scoreB = subQ.querySelector('.score_b').value;
                                    if (!subName || !scoreA || !scoreB) {
                                        alert(
                                            `กรุณากรอกชื่อเกณฑ์ย่อย, คะแนน A, และคะแนน B สำหรับเกณฑ์ปริมาณย่อยที่ ${sk + 1} ในเกณฑ์ปริมาณหลักที่ ${qj + 1} รายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`
                                        );
                                        valid = false;
                                        return;
                                    }

                                    quantMain.quantity_sub_criterias.push({
                                        name: subName,
                                        sequence: sk + 1,
                                        score_a: Number(scoreA),
                                        score_b: Number(scoreB),
                                        require_evidence: subQ.querySelector('.quant_require_evidence')?.checked || false
                                    });
                                });

                                if (valid) {
                                    evalList.quantity_main_criterias.push(quantMain);
                                }
                            });
                        if (!valid) return;
                    }

                    if (qualityChecked) {
                        let valid = true;
                        Array.from(evalBlock.querySelectorAll(
                            '.quality_main_criterias_container .qual_criteria_block'))
                            .sort((left, right) => compareSequenceValues(
                                getSequenceValue(left, '.qual_main_sequence', `${catI + 1}.${evalI + 1}.1`),
                                getSequenceValue(right, '.qual_main_sequence', `${catI + 1}.${evalI + 1}.1`)
                            ))
                            .forEach((
                            qMain, qj) => {
                            const qualName = qMain.querySelector('.qual_name').value.trim();
                            const qualRatio = qMain.querySelector('.qual_ratio').value;
                            // Get content from Summernote editor if available, otherwise from textarea
                            const tooltipsTextarea = qMain.querySelector('.qual_tooltips');
                            const qualTooltips = $(tooltipsTextarea).hasClass('note-editor')
                                ? $(tooltipsTextarea).summernote('code') 
                                : tooltipsTextarea.value.trim();
                            if (!qualName || !qualRatio) {
                                alert(
                                    `กรุณากรอกชื่อเกณฑ์และสัดส่วนคะแนนสำหรับเกณฑ์คุณภาพหลักที่ ${qj + 1} ในรายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`
                                );
                                valid = false;
                                return;
                            }

                            let qualMain = {
                                name: qualName,
                                ratio: Number(qualRatio),
                                tooltips: qualTooltips || null,
                                sequence: qj + 1,
                                require_evidence: qMain.querySelector('.qual_require_evidence')?.checked || false,
                                quality_sub_criterias: []
                            };

                            Array.from(qMain.querySelectorAll(
                                '.qual_sub_criterias_container .qual_sub_criteria_block'
                            ))
                                .sort((left, right) => compareSequenceValues(
                                    getSequenceValue(left, '.qual_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`),
                                    getSequenceValue(right, '.qual_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`)
                                ))
                                .forEach((subQ, sk) => {
                                const subName = subQ.querySelector('.qual_sub_name')
                                    .value.trim();
                                const numScore = subQ.querySelector('.num_score')
                                    .value;
                                // Get content from Summernote editor if available, otherwise from textarea
                                const descriptionTextarea = subQ.querySelector('.qual_sub_description');
                                const subDescription = $(descriptionTextarea).hasClass('note-editor')
                                    ? $(descriptionTextarea).summernote('code') 
                                    : descriptionTextarea.value.trim() || '';
                                if (!subName || !numScore) {
                                    showValidationErrorModal(`กรุณากรอกชื่อเกณฑ์ย่อยและคะแนนสูงสุดสำหรับเกณฑ์คุณภาพย่อยที่ ${sk + 1} ในเกณฑ์คุณภาพหลักที่ ${qj + 1} รายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`);
                                    valid = false;
                                    return;
                                }

                                qualMain.quality_sub_criterias.push({
                                    name: subName,
                                    sequence: sk + 1,
                                    num_score: Number(numScore),
                                    description: subDescription
                                });
                            });

                            if (valid) {
                                evalList.quality_main_criterias.push(qualMain);
                            }
                        });
                        if (!valid) return;
                    }

                    category.evaluation_lists.push(evalList);
                });

                if (category.evaluation_lists.length === 0) {
                    showValidationErrorModal(`กรุณาเพิ่มรายการประเมินอย่างน้อย 1 รายการในหมวดหมู่ที่ ${catI + 1}`);
                    return;
                }

                finalData.categories.push(category);
            });

            if (finalData.categories.length === 0) {
                showValidationErrorModal('กรุณาเพิ่มหมวดหมู่การประเมินอย่างน้อย 1 หมวดหมู่');
                return;
            }

            showConfirmModal(reportTitle); // แสดง report_title แทน finalData.version_name
        });

        document.getElementById('confirm_submit_btn').addEventListener('click', async function handleSubmit() {
            hideConfirmModal();
            showLoading();

            try {
                const response = await fetch("{{ route('report-structure.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value ||
                            document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(finalData)
                });

                const data = await response.json();

                hideLoading();

                if (response.ok && data.success) {
                    // Success case (HTTP 201)
                    showSuccessModal();
                } else if (response.status === 422) {
                    // Validation error (HTTP 422)
                    let errorMessage = 'เกิดข้อผิดพลาดในการตรวจสอบข้อมูล:\n';

                    // Check for both 'error' and 'errors' to handle potential response variations
                    const errors = data.error || data.errors || {};

                    if (Object.keys(errors).length > 0) {
                        // Process validation errors if present
                        for (const [field, messages] of Object.entries(errors)) {
                            errorMessage +=
                                `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}\n`;
                        }
                    } else {
                        // Fallback if no specific errors are provided
                        errorMessage += data.message || 'ไม่พบรายละเอียดข้อผิดพลาด';
                    }

                    alert(errorMessage);
                } else {
                    // Other errors (e.g., HTTP 500)
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกข้อมูลได้'));
                }
            } catch (error) {
                // Network or unexpected errors
                hideLoading();
                console.error('Fetch error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message);
            }
        });
    </script>
@endpush
