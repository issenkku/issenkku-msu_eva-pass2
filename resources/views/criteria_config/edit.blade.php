@extends('layouts.app')

@section('content')
    <div class="py-12 bg-gradient-to-r from-blue-50 to-indigo-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-3">แก้ไขเกณฑ์การประเมิน</h1>
                <p class="text-gray-600 text-lg">กรุณาแก้ไขข้อมูลเกณฑ์การประเมินตามที่ต้องการ</p>
            </div>

            <form id="editForm" action="{{ route('report-structure.update', ['id' => $id ?? '']) }}" method="POST" class="space-y-8" novalidate>
                @csrf
                @method('PUT')

                <!-- Report Datas -->
                <div class="report_datas_block bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h2 class="font-bold text-2xl text-gray-900 mb-6 flex items-center">
                        <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">1</span>
                        ข้อมูลเกณฑ์การประเมิน
                    </h2>
                    <div class="space-y-6">
                        <div style="display:none">
                            <label for="version_name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อเวอร์ชัน <span class="text-red-500">*</span></label>
                            <input id="version_name" required name="version_name" class="version_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="ชื่อเวอร์ชัน">
                        </div>
                        <div>
                            <label for="report_title" class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span class="text-red-500">*</span></label>
                            <input id="report_title" required name="report_title" class="report_title border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="ชื่อเกณฑ์การประเมิน">
                        </div>
                        <div>
                            <label for="report_description" class="block text-sm font-medium text-gray-700 mb-2">รายละเอียดเกณฑ์ <span class="text-red-500"></span></label>
                            <textarea id="report_description" rows="4" name="report_description" class="report_description border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="รายละเอียดเพิ่มเติมของเกณฑ์"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="assessment_type" class="block text-sm font-medium text-gray-700 mb-2">ประเภทการประเมิน <span class="text-red-500">*</span></label>
                                <select id="assessment_type" required name="assessment_type" class="assessment_type border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200">
                                    <option value="">-- เลือกประเภทการประเมิน --</option>
                                    <option value="กลุ่มวิชาการ">กลุ่มวิชาการ</option>
                                    <option value="กลุ่มสนับสนุน">กลุ่มสนับสนุน</option>
                                    <option value="กลุ่มสนับสนุน">กลุ่มบริหาร</option>
                                </select>
                            </div>
                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                                <textarea id="comment" rows="4" name="comment" class="comment border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="หมายเหตุเพิ่มเติม"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div id="categories_container" class="space-y-8">
                    <!-- Category Template (hidden) -->
                    <div class="category_block bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" style="display: none;">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-xl text-gray-900">หมวดหมู่การประเมิน</h3>
                            <div class="flex space-x-3">
                                <button type="button" class="move_category_up_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button type="button" class="move_category_down_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับ</label>
                                <span class="category_sequence text-gray-700 font-medium text-lg">1</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่หลัก <span class="text-red-500">*</span></label>
                                <input required class="main_categories border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="ชื่อหมวดหมู่หลัก">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่ย่อย <span class="text-red-500">*</span></label>
                                <input required class="sub_categories border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="ชื่อหมวดหมู่ย่อย">
                            </div>
                        </div>
                        
                        <div class="evaluation_lists_container space-y-6 mt-8">
                            <h4 class="font-bold text-lg text-gray-900 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                รายการประเมิน
                            </h4>
                            <!-- Evaluation List Template -->
                            <div class="evaluation_list_block bg-gray-100 p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
                                <div class="flex justify-between items-center mb-4">
                                    <h5 class="font-bold text-gray-900">รายการประเมิน</h5>
                                    <div class="flex space-x-3">
                                        <button type="button" class="move_eval_up_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <button type="button" class="move_eval_down_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
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
                                        <span name="eval_sequence" class="eval_sequence text-gray-700 font-medium text-lg">1</span>
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
                                    <div class="quant_criteria_block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                        <div class="flex justify-between items-center mb-3">
                                            <h6 class="text-sm font-bold text-gray-900">เกณฑ์ปริมาณหลัก</h6>
                                            <div class="flex space-x-3">
                                                <button type="button" class="move_quant_up_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="move_quant_down_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
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
                                                <span name="quant_main_sequence" class="quant_main_sequence text-gray-700 font-medium text-lg">1</span>
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
                                            <div class="quant_sub_criteria_block bg-gray-50 p-3 rounded-lg">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-600">เกณฑ์ปริมาณย่อย</span>
                                                    <button type="button" class="delete_quant_sub_btn text-red-600 hover:text-red-800 transition duration-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</label>
                                                        <span name="quant_sub_sequence" class="quant_sub_sequence text-gray-700 font-medium text-lg">1</span>
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
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">หน่วยภาระงานมาตรฐาน (B) <span class="text-red-500">*</span></label>
                                                        <input type="number" name="score_b" class="score_b border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2 text-sm transition duration-200" placeholder="คะแนน B">
                                                    </div>
                                                </div>
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
                                    <button type="button" class="add_qual_criteria_btn mt-2 text-sm px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        เพิ่มเกณฑ์คุณภาพหลัก
                                    </button>
                                    <!-- Quality Main Criteria Template -->
                                    <div class="qual_criteria_block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                        <div class="flex justify-between items-center mb-3">
                                            <h6 class="text-sm font-bold text-gray-900">เกณฑ์คุณภาพหลัก</h6>
                                            <div class="flex space-x-3">
                                                <button type="button" class="move_qual_up_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200" disabled>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <button type="button" class="move_qual_down_btn text-blue-600 hover:text-blue-800 disabled:text-gray-400 transition duration-200">
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
                                                <span name="qual_main_sequence" class="qual_main_sequence text-gray-700 font-medium text-lg">1</span>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเกณฑ์ <span class="text-red-500">*</span></label>
                                                <input name="qual_name" class="qual_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200" placeholder="ชื่อเกณฑ์คุณภาพ">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">สัดส่วน <span class="text-red-500">*</span></label>
                                                <input type="number" name="qual_ratio" class="qual_ratio border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200" placeholder="สัดส่วน %">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย <span class="text-red-500"></span></label>
                                            <textarea name="qual_tooltips" class="qual_tooltips richtext-editor border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-sm transition duration-200" placeholder="คำอธิบายเพิ่มเติม"></textarea>
                                        </div>
                                        <!-- Quality Sub Criteria Container -->
                                        <div class="qual_sub_criterias_container space-y-3 pl-4 border-l-2 border-purple-200 mb-3">
                                            <div class="qual_sub_criteria_block bg-purple-50 p-3 rounded-lg">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-600">เกณฑ์คุณภาพย่อย</span>
                                                    <button type="button" class="delete_qual_sub_btn text-red-600 hover:text-red-800 transition duration-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ลำดับ</label>
                                                        <span name="qual_sub_sequence" class="qual_sub_sequence text-gray-700 font-medium text-lg">1</span>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">ชื่อเกณฑ์ย่อย <span class="text-red-500">*</span></label>
                                                        <input name="qual_sub_name" class="qual_sub_name border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200" placeholder="ชื่อเกณฑ์ย่อย">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">คะแนนสูงสุด <span class="text-red-500">*</span></label>
                                                        <input type="number" name="num_score" class="num_score border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2 text-sm transition duration-200" placeholder="คะแนนสูงสุด">
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
                                        <button type="button" class="add_qual_sub_criteria_btn text-sm px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            เพิ่มคุณภาพย่อย
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
                    </div>
                    {{-- <!-- Category blocks will be dynamically loaded here -->
                    <div class="flex justify-center py-10 text-gray-500">กำลังโหลดข้อมูล...</div> --}}
                </div>

                <button type="button" id="add_category_btn" class="my-6 px-5 py-2.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    เพิ่มหมวดหมู่การประเมิน
                </button>

                <div class="flex justify-end mt-10 space-x-4">
                    <a href="{{ route('criteria_config.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                        ยกเลิก
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading_overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-700 text-lg">กำลังโหลดข้อมูล กรุณารอสักครู่...</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success_modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full">
            <div class="text-center">
                <div class="bg-green-100 rounded-full p-4 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">บันทึกสำเร็จ</h3>
                <p class="text-gray-600 mb-6">ข้อมูลเกณฑ์การประเมินถูกแก้ไขเรียบร้อยแล้ว</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('criteria_config.index') }}" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">กลับหน้าหลัก</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let originalData = null;

        // Clean up all existing Summernote instances
        function cleanupSummernote() {
            $('.richtext-editor').each(function() {
                if ($(this).summernote && typeof $(this).summernote === 'function') {
                    try {
                        // Check if summernote is initialized
                        if ($(this).next('.note-editor').length > 0) {
                            $(this).summernote('destroy');
                        }
                    } catch (e) {
                        console.log('Error destroying summernote:', e);
                    }
                }
                
                // Remove any leftover Summernote DOM elements
                $(this).next('.note-editor').remove();
                
                // Reset any Summernote classes and attributes
                $(this).removeClass('note-editor note-frame note-editable');
                $(this).removeAttr('style');
                $(this).show(); // Make sure textarea is visible
            });
        }

        // Initialize Summernote for rich text editors
        function initializeSummernote(container = null) {
            // If container is provided, only initialize editors in that container
            const targetSelector = container ? $(container).find('.richtext-editor') : $('.richtext-editor');
            
            // Clean up existing instances in the target area
            if (container) {
                $(container).find('.richtext-editor').each(function() {
                    const $editor = $(this);
                    try {
                        if ($editor.hasClass('note-editor') || $editor.next('.note-editor').length > 0) {
                            $editor.summernote('destroy');
                        }
                    } catch (e) {
                        // Ignore errors during cleanup
                    }
                });
            } else {
                cleanupSummernote();
            }
            
            // Wait a moment for cleanup to complete
            setTimeout(() => {
                targetSelector.each(function() {
                    const $editor = $(this);
                    let placeholder = 'กรุณาใส่คำอธิบายเพิ่มเติม...';
                    
                    // Use specific placeholder for quality sub criteria description
                    if ($editor.hasClass('qual_sub_description') || 
                        $editor.attr('name')?.includes('qual_sub_description')) {
                        placeholder = 'ใส่คำอธิบายการให้คะแนน';
                    }
                    
                    // Double check that summernote is not already initialized
                    if ($editor.next('.note-editor').length === 0 && !$editor.hasClass('note-editor')) {
                        try {
                            $editor.summernote({
                                height: 250,
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'italic', 'underline', 'clear']],
                                    ['color', ['color']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['table', ['table']],
                                    ['insert', ['link', 'hr']],
                                    ['view', ['fullscreen', 'codeview', 'help']]
                                ],
                                placeholder: placeholder,
                                lang: 'th-TH',
                                callbacks: {
                                    onInit: function() {
                                        // Ensure content is loaded properly
                                        console.log('Summernote initialized for:', $editor.attr('class'));
                                    },
                                    onChange: function(contents, $editable) {
                                        $editor.val(contents);
                                    }
                                }
                            });
                        } catch (e) {
                            console.error('Error initializing summernote:', e);
                        }
                    }
                });
            }, 100);
        }

        // Initialize Summernote when document is ready
        $(document).ready(function() {
            // Don't initialize here - let it be handled by populateForm after data is loaded
        });
        
        document.addEventListener('DOMContentLoaded', function () {
            fetchVersionDetails();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Event delegation for dynamic elements
            document.addEventListener('click', function(e) {
                // Category buttons
                if (e.target.closest('.delete_category_btn')) {
                    handleDeleteCategory(e.target.closest('.category_block'));
                }
                if (e.target.closest('.move_category_up_btn')) {
                    handleMoveCategoryUp(e.target.closest('.category_block'));
                }
                if (e.target.closest('.move_category_down_btn')) {
                    handleMoveCategoryDown(e.target.closest('.category_block'));
                }
                if (e.target.closest('#add_category_btn')) {
                    handleAddCategory();
                }

                // Evaluation List buttons
                if (e.target.closest('.delete_eval_btn')) {
                    handleDeleteEvaluation(e.target.closest('.evaluation_list_block'));
                }
                if (e.target.closest('.add_evaluation_list_btn')) {
                    handleAddEvaluation(e.target.closest('.category_block'));
                }

                // Quantity Criteria buttons
                if (e.target.closest('.delete_quant_btn')) {
                    handleDeleteQuantityCriteria(e.target.closest('.quant_criteria_block'));
                }
                if (e.target.closest('.add_quant_criteria_btn')) {
                    handleAddQuantityCriteria(e.target.closest('.evaluation_list_block'));
                }
                if (e.target.closest('.delete_quant_sub_btn')) {
                    handleDeleteQuantitySubCriteria(e.target.closest('.quant_sub_criteria_block'));
                }
                if (e.target.closest('.add_quant_sub_criteria_btn')) {
                    handleAddQuantitySubCriteria(e.target.closest('.quant_criteria_block'));
                }

                // Quality Criteria buttons
                if (e.target.closest('.delete_qual_btn')) {
                    handleDeleteQualityCriteria(e.target.closest('.qual_criteria_block'));
                }
                if (e.target.closest('.add_qual_criteria_btn')) {
                    handleAddQualityCriteria(e.target.closest('.evaluation_list_block'));
                }
                if (e.target.closest('.delete_qual_sub_btn')) {
                    handleDeleteQualitySubCriteria(e.target.closest('.qual_sub_criteria_block'));
                }
                if (e.target.closest('.add_qual_sub_criteria_btn')) {
                    handleAddQualitySubCriteria(e.target.closest('.qual_criteria_block'));
                }
            });

            // Criteria type checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('criteria_type')) {
                    handleCriteriaTypeChange(e.target.closest('.evaluation_list_block'));
                }
            });
        }

        function cloneAndClear(blockSelector) {
            let node = document.querySelector(blockSelector).cloneNode(true);
            
            // Properly handle Summernote instances in cloned node
            $(node).find('.richtext-editor').each(function() {
                const $editor = $(this);
                
                // Remove any existing Summernote DOM elements
                $editor.next('.note-editor').remove();
                
                // Generate new unique ID for cloned editor
                const newId = 'editor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                this.id = newId;
                this.value = ''; // Clear content
                
                // Remove any Summernote-related classes and reset styles
                $editor.removeClass('note-editor note-frame note-editable');
                $editor.removeAttr('style');
                $editor.show(); // Ensure textarea is visible
            });
            
            node.querySelectorAll('input[type="checkbox"]').forEach(inp => inp.checked = false);
            node.querySelectorAll('input:not([type="checkbox"])').forEach(inp => inp.value = '');
            node.querySelectorAll('textarea:not(.quant_formula):not(.richtext-editor)').forEach(textarea => textarea.value = '');
            node.querySelectorAll('textarea.quant_formula').forEach(textarea => textarea.value = 'D = A × C / B');
            node.querySelectorAll(
                '.evaluation_list_block:not(:first-child), .quant_criteria_block:not(:first-child), .qual_criteria_block:not(:first-child), .quant_sub_criteria_block:not(:first-child), .qual_sub_criteria_block:not(:first-child)'
            ).forEach(e => e.remove());

            if (blockSelector === '.evaluation_list_block') {
                const container = node.closest('.category_block').querySelector('.evaluation_lists_container');
                const index = container.querySelectorAll('.evaluation_list_block').length + 1;
                node.querySelector('.eval_sequence').textContent = index;
                node.querySelector('.quantity_main_criterias_container').classList.add('hidden');
                node.querySelector('.quality_main_criterias_container').classList.add('hidden');
            }
            if (blockSelector === '.category_block') {
                const container = document.getElementById('categories_container');
                const index = container.querySelectorAll('.category_block').length + 1;
                node.querySelector('.category_sequence').textContent = index;
            }
            
            return node;
        }

        function updateButtonStates(containerSelector, upBtnSelector, downBtnSelector) {
            const items = document.querySelectorAll(containerSelector);
            items.forEach((item, index) => {
                const upBtn = item.querySelector(upBtnSelector);
                const downBtn = item.querySelector(downBtnSelector);
                if (upBtn) upBtn.disabled = index === 0;
                if (downBtn) downBtn.disabled = index === items.length - 1;
            });
        }

        function updateSequences() {
            // Update category sequences
            document.querySelectorAll('.category_block:not([style*="display: none"])').forEach((catBlock, index) => {
                catBlock.querySelector('.category_sequence').textContent = index + 1;
            });

            // Update evaluation sequences
            document.querySelectorAll('.evaluation_lists_container').forEach(container => {
                container.querySelectorAll('.evaluation_list_block').forEach((evalBlock, index) => {
                    evalBlock.querySelector('.eval_sequence').textContent = index + 1;
                });
            });

            // Update quantity main criteria sequences
            document.querySelectorAll('.quantity_main_criterias_container').forEach(container => {
                container.querySelectorAll('.quant_criteria_block').forEach((block, index) => {
                    block.querySelector('.quant_main_sequence').textContent = index + 1;
                });
            });

            // Update quantity sub criteria sequences
            document.querySelectorAll('.quant_sub_criteria_container').forEach(container => {
                container.querySelectorAll('.quant_sub_criteria_block').forEach((block, index) => {
                    block.querySelector('.quant_sub_sequence').textContent = index + 1;
                });
            });

            // Update quality main criteria sequences
            document.querySelectorAll('.quality_main_criterias_container').forEach(container => {
                container.querySelectorAll('.qual_criteria_block').forEach((block, index) => {
                    block.querySelector('.qual_main_sequence').textContent = index + 1;
                });
            });

            // Update quality sub criteria sequences
            document.querySelectorAll('.qual_sub_criterias_container').forEach(container => {
                container.querySelectorAll('.qual_sub_criteria_block').forEach((block, index) => {
                    block.querySelector('.qual_sub_sequence').textContent = index + 1;
                });
            });
        }

        // Category handlers
        function handleDeleteCategory(categoryBlock) {
            const container = document.getElementById('categories_container');
            if (container.querySelectorAll('.category_block:not([style*="display: none"])').length > 1) {
                if (confirm('ต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) {
                    categoryBlock.remove();
                    updateSequences();
                    updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
                }
            } else {
                alert('ต้องมีหมวดหมู่การประเมินอย่างน้อย 1 รายการ');
            }
        }

        function handleMoveCategoryUp(categoryBlock) {
            const previous = categoryBlock.previousElementSibling;
            if (previous && previous.classList.contains('category_block')) {
                categoryBlock.parentNode.insertBefore(categoryBlock, previous);
                updateSequences();
                updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
            }
        }

        function handleMoveCategoryDown(categoryBlock) {
            const next = categoryBlock.nextElementSibling;
            if (next && next.classList.contains('category_block')) {
                categoryBlock.parentNode.insertBefore(next, categoryBlock);
                updateSequences();
                updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
            }
        }

        function handleAddCategory() {
            const newBlock = cloneAndClear('.category_block');
            newBlock.style.display = 'block';
            document.getElementById('categories_container').appendChild(newBlock);
            updateSequences();
            updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Evaluation handlers
        function handleDeleteEvaluation(evaluationBlock) {
            const container = evaluationBlock.closest('.evaluation_lists_container');
            if (container.querySelectorAll('.evaluation_list_block').length > 1) {
                evaluationBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีรายการประเมินอย่างน้อย 1 รายการ');
            }
        }

        function handleAddEvaluation(categoryBlock) {
            const container = categoryBlock.querySelector('.evaluation_lists_container');
            const newBlock = cloneAndClear('.evaluation_list_block');
            container.appendChild(newBlock);
            updateSequences();
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleCriteriaTypeChange(evaluationBlock) {
            const quantityContainer = evaluationBlock.querySelector('.quantity_main_criterias_container');
            const qualityContainer = evaluationBlock.querySelector('.quality_main_criterias_container');
            const quantityCheckbox = evaluationBlock.querySelector('.quantity_criteria_type');
            const qualityCheckbox = evaluationBlock.querySelector('.quality_criteria_type');
            
            quantityContainer.classList.toggle('hidden', !quantityCheckbox.checked);
            qualityContainer.classList.toggle('hidden', !qualityCheckbox.checked);
        }

        // Quantity criteria handlers
        function handleDeleteQuantityCriteria(quantBlock) {
            const container = quantBlock.closest('.quantity_main_criterias_container');
            if (container.querySelectorAll('.quant_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(quantBlock).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                quantBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์ปริมาณหลักอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQuantityCriteria(evaluationBlock) {
            const container = evaluationBlock.querySelector('.quantity_main_criterias_container');
            const newBlock = cloneAndClear('.quant_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            
            // Initialize Summernote for new rich text editors with specific targeting
            setTimeout(function() {
                $(newBlock).find('.richtext-editor').each(function() {
                    if (!$(this).hasClass('note-editor')) {
                        $(this).summernote({
                            height: 250,
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['table', ['table']],
                                ['insert', ['link', 'hr']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ],
                            placeholder: 'กรุณาใส่คำอธิบายเพิ่มเติม...',
                            lang: 'th-TH'
                        });
                    }
                });
            }, 100);
            
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleDeleteQuantitySubCriteria(subBlock) {
            const container = subBlock.closest('.quant_sub_criteria_container');
            if (container.querySelectorAll('.quant_sub_criteria_block').length > 1) {
                subBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์ปริมาณย่อยอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQuantitySubCriteria(quantBlock) {
            const container = quantBlock.querySelector('.quant_sub_criteria_container');
            const newBlock = cloneAndClear('.quant_sub_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Quality criteria handlers
        function handleDeleteQualityCriteria(qualBlock) {
            const container = qualBlock.closest('.quality_main_criterias_container');
            if (container.querySelectorAll('.qual_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(qualBlock).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                qualBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์คุณภาพหลักอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQualityCriteria(evaluationBlock) {
            const container = evaluationBlock.querySelector('.quality_main_criterias_container');
            const newBlock = cloneAndClear('.qual_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            
            // Initialize Summernote for new rich text editors with specific targeting
            setTimeout(function() {
                $(newBlock).find('.richtext-editor').each(function() {
                    if (!$(this).hasClass('note-editor')) {
                        $(this).summernote({
                            height: 250,
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['table', ['table']],
                                ['insert', ['link', 'hr']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ],
                            placeholder: 'กรุณาใส่คำอธิบายเพิ่มเติม...',
                            lang: 'th-TH'
                        });
                    }
                });
            }, 100);
            
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleDeleteQualitySubCriteria(subBlock) {
            const container = subBlock.closest('.qual_sub_criterias_container');
            if (container.querySelectorAll('.qual_sub_criteria_block').length > 1) {
                subBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์คุณภาพย่อยอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQualitySubCriteria(qualBlock) {
            const container = qualBlock.querySelector('.qual_sub_criterias_container');
            const newBlock = cloneAndClear('.qual_sub_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            
            // Initialize Summernote for new rich text editors in the new block
            initializeSummernote(newBlock);
            
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function fetchVersionDetails() {
            showLoading();
            const url = "{{ route('report-structure.show', ['id' => $id ?? '']) }}";
            
            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.data) {
                    originalData = data.data;
                    populateForm(data.data);
                    
                    // Initialize Summernote for all rich text editors after populating data
                    setTimeout(() => {
                        initializeSummernote();
                    }, 500);
                } else {
                    showError('ไม่พบข้อมูลเวอร์ชัน');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            });
        }

        function populateForm(data) {
            // Populate basic information
            document.getElementById('version_name').value = data.version_name || '';
            
            if (data.report_datas && data.report_datas.length > 0) {
                const reportData = data.report_datas[0];
                document.getElementById('report_title').value = reportData.report_title || '';
                document.getElementById('report_description').value = reportData.report_description || '';
                document.getElementById('assessment_type').value = reportData.assessment_type || '';
                document.getElementById('comment').value = reportData.comment || '';
            }

            const categoriesContainer = document.getElementById('categories_container');
            const loadingMessage = categoriesContainer.querySelector('.flex.justify-center');
            if (loadingMessage) {
                loadingMessage.remove();
            }

            // Clear any previously populated blocks to prevent duplication
            categoriesContainer.querySelectorAll('.category_block:not([style*="display: none"])').forEach(b => b.remove());

            // Populate categories
            if (data.categories && data.categories.length > 0) {
                data.categories.forEach(categoryData => {
                    const categoryBlock = createCategoryFromData(categoryData);
                    categoriesContainer.appendChild(categoryBlock);
                });
            } else {
                // If there are no categories from the server, add one empty one to start with
                handleAddCategory();
            }

            updateSequences();
            updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
            
            // Initialize Summernote after all data is populated and DOM is ready
            setTimeout(function() {
                console.log('Initializing Summernote after data population...');
                initializeSummernote();
            }, 1500); // Increased timeout to ensure all DOM manipulation is complete
        }

        function createCategoryFromData(categoryData) {
            const template = document.querySelector('.category_block');
            const newBlock = template.cloneNode(true);
            newBlock.style.display = 'block';

            newBlock.querySelector('.main_categories').value = categoryData.main_categories || '';
            newBlock.querySelector('.sub_categories').value = categoryData.sub_categories || '';

            const evaluationContainer = newBlock.querySelector('.evaluation_lists_container');
            const evalTemplate = evaluationContainer.querySelector('.evaluation_list_block');
            if(evalTemplate) evalTemplate.remove();

            if (categoryData.evaluation_lists && categoryData.evaluation_lists.length > 0) {
                categoryData.evaluation_lists.forEach(evalData => {
                    const evalBlock = createEvaluationFromData(evalData);
                    evaluationContainer.appendChild(evalBlock);
                });
            } else {
                const emptyEvalBlock = createEvaluationFromData({});
                evaluationContainer.appendChild(emptyEvalBlock);
            }

            return newBlock;
        }

        function createEvaluationFromData(evalData) {
            const template = document.querySelector('.evaluation_list_block');
            const newBlock = template.cloneNode(true);

            newBlock.querySelector('.eval_name').value = evalData.name || '';
            newBlock.querySelector('.sum_score').value = evalData.sum_score || '';
            newBlock.querySelector('.annotation').value = evalData.annotation || '';

            const hasQuantity = evalData.quantity_main_criterias && evalData.quantity_main_criterias.length > 0;
            const hasQuality = evalData.quality_main_criterias && evalData.quality_main_criterias.length > 0;

            const quantityCheckbox = newBlock.querySelector('.quantity_criteria_type');
            const qualityCheckbox = newBlock.querySelector('.quality_criteria_type');
            const quantityContainer = newBlock.querySelector('.quantity_main_criterias_container');
            const qualityContainer = newBlock.querySelector('.quality_main_criterias_container');

            quantityCheckbox.checked = hasQuantity;
            qualityCheckbox.checked = hasQuality;
            quantityContainer.classList.toggle('hidden', !hasQuantity);
            qualityContainer.classList.toggle('hidden', !hasQuality);

            if (hasQuantity) {
                populateQuantityCriteria(quantityContainer, evalData.quantity_main_criterias);
            }
            if (hasQuality) {
                populateQualityCriteria(qualityContainer, evalData.quality_main_criterias);
            }

            return newBlock;
        }

        function populateQuantityCriteria(container, quantityData) {
            const quantTemplate = container.querySelector('.quant_criteria_block');
            if(quantTemplate) quantTemplate.remove();

            quantityData.forEach(quantMain => {
                const template = document.querySelector('.quant_criteria_block');
                const newBlock = template.cloneNode(true);

                newBlock.querySelector('.quant_name').value = quantMain.name || '';
                
                // Set content for Summernote editor
                const tooltipsTextarea = newBlock.querySelector('.quant_tooltips');
                tooltipsTextarea.value = quantMain.tooltips || '';
                
                if (quantMain.formulas && quantMain.formulas.length > 0) {
                    newBlock.querySelector('.quant_formula').value = quantMain.formulas[0].condition || 'D = A × C / B';
                }

                const subContainer = newBlock.querySelector('.quant_sub_criteria_container');
                const subTemplate = subContainer.querySelector('.quant_sub_criteria_block');
                if(subTemplate) subTemplate.remove();

                if (quantMain.quantity_sub_criterias && quantMain.quantity_sub_criterias.length > 0) {
                    quantMain.quantity_sub_criterias.forEach(subData => {
                        const subBlockTemplate = document.querySelector('.quant_sub_criteria_block');
                        const subBlock = subBlockTemplate.cloneNode(true);
                        subBlock.querySelector('.quant_sub_name').value = subData.name || '';
                        subBlock.querySelector('.score_a').value = subData.score_a || '';
                        subBlock.querySelector('.score_b').value = subData.score_b || '';
                        subContainer.appendChild(subBlock);
                    });
                } else {
                    const subBlockTemplate = document.querySelector('.quant_sub_criteria_block');
                    const subBlock = subBlockTemplate.cloneNode(true);
                    subContainer.appendChild(subBlock);
                }
                container.appendChild(newBlock);
            });

            // Don't initialize Summernote here - will be done after all data is populated
        }

        function populateQualityCriteria(container, qualityData) {
            const qualTemplate = container.querySelector('.qual_criteria_block');
            if(qualTemplate) qualTemplate.remove();

            qualityData.forEach(qualMain => {
                const template = document.querySelector('.qual_criteria_block');
                const newBlock = template.cloneNode(true);

                newBlock.querySelector('.qual_name').value = qualMain.name || '';
                newBlock.querySelector('.qual_ratio').value = qualMain.ratio || '';
                
                // Set content for Summernote editor
                const tooltipsTextarea = newBlock.querySelector('.qual_tooltips');
                tooltipsTextarea.value = qualMain.tooltips || '';

                const subContainer = newBlock.querySelector('.qual_sub_criterias_container');
                const subTemplate = subContainer.querySelector('.qual_sub_criteria_block');
                if(subTemplate) subTemplate.remove();

                if (qualMain.quality_sub_criterias && qualMain.quality_sub_criterias.length > 0) {
                    qualMain.quality_sub_criterias.forEach(subData => {
                        const subBlockTemplate = document.querySelector('.qual_sub_criteria_block');
                        const subBlock = subBlockTemplate.cloneNode(true);
                        subBlock.querySelector('.qual_sub_name').value = subData.name || '';
                        subBlock.querySelector('.num_score').value = subData.num_score || '';
                        
                        // Handle description with potential HTML content
                        const descTextarea = subBlock.querySelector('.qual_sub_description');
                        descTextarea.value = subData.description || '';
                        
                        subContainer.appendChild(subBlock);
                    });
                } else {
                    const subBlockTemplate = document.querySelector('.qual_sub_criteria_block');
                    const subBlock = subBlockTemplate.cloneNode(true);
                    subContainer.appendChild(subBlock);
                }
                container.appendChild(newBlock);
            });

            // Don't initialize Summernote here - will be done after all data is populated
        }

        function showLoading() {
            document.getElementById('loading_overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading_overlay').classList.add('hidden');
        }

        function showSuccess() {
            document.getElementById('success_modal').classList.remove('hidden');
        }

        function showError(message) {
            alert(message);
        }

        // Form submission with full data structure
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Save all Summernote content back to textareas before collecting data
            $('.richtext-editor').each(function() {
                if ($(this).hasClass('note-editor')) {
                    const content = $(this).summernote('code');
                    this.value = content;
                }
            });
            
            // Validate basic information
            const versionName = document.getElementById('version_name').value.trim();
            const reportTitle = document.getElementById('report_title').value.trim();
            const reportDescription = document.getElementById('report_description').value.trim();
            const assessmentType = document.getElementById('assessment_type').value.trim();

            if (!versionName || !reportTitle || !assessmentType) {
                alert('กรุณากรอกข้อมูลพื้นฐานให้ครบถ้วน');
                return;
            }

            showLoading();

            // Collect all form data in the same structure as create
            let formData;
            try {
                formData = collectFormData();
            } catch (error) {
                hideLoading();
                alert(error.message);
                return;
            }
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData),
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success !== false) {
                    showSuccess();
                } else {
                    let errorMessage = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                    if (data.message) {
                        errorMessage = data.message;
                    }
                    if (data.error) {
                        errorMessage += "\\n" + Object.values(data.error).flat().join("\\n");
                    }
                    showError(errorMessage);
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showError('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            });
        });

        function collectFormData() {
            const formData = {
                _method: 'PUT',
                version_name: document.getElementById('version_name').value.trim(),
                created_by: {{ Auth::user()->id }},
                report_datas: [{
                    report_title: document.getElementById('report_title').value.trim(),
                    report_description: document.getElementById('report_description').value.trim(),
                    assessment_type: document.getElementById('assessment_type').value.trim(),
                    comment: document.getElementById('comment').value.trim() || null
                }],
                categories: []
            };

            // Collect categories
            document.querySelectorAll('.category_block:not([style*="display: none"])').forEach((catBlock, catIndex) => {
                const mainCategories = catBlock.querySelector('.main_categories').value.trim();
                const subCategories = catBlock.querySelector('.sub_categories').value.trim();

                if (!mainCategories || !subCategories) {
                    throw new Error(`กรุณากรอกชื่อหมวดหมู่หลักและหมวดหมู่ย่อยสำหรับหมวดหมู่ที่ ${catIndex + 1}`);
                }

                const category = {
                    main_categories: mainCategories,
                    sub_categories: subCategories,
                    sequence: catIndex + 1,
                    evaluation_lists: []
                };

                // Collect evaluation lists
                catBlock.querySelectorAll('.evaluation_list_block').forEach((evalBlock, evalIndex) => {
                    const evalName = evalBlock.querySelector('.eval_name').value.trim();
                    const sumScore = evalBlock.querySelector('.sum_score').value;

                    if (!evalName || !sumScore) {
                        throw new Error(`กรุณากรอกชื่อรายการและคะแนนรวมสำหรับรายการที่ ${evalIndex + 1} ในหมวดหมู่ที่ ${catIndex + 1}`);
                    }

                    const evalData = {
                        name: evalName,
                        sum_score: parseFloat(sumScore),
                        sequence: evalIndex + 1,
                        annotation: evalBlock.querySelector('.annotation').value.trim() || null,
                        quantity_main_criterias: [],
                        quality_main_criterias: []
                    };

                            // Collect quantity criteria if enabled
                            if (evalBlock.querySelector('.quantity_criteria_type').checked) {
                                evalBlock.querySelectorAll('.quant_criteria_block').forEach((quantBlock, quantIndex) => {
                                    const quantName = quantBlock.querySelector('.quant_name').value.trim();
                                    
                                    // Get content from Summernote or textarea
                                    const tooltipsElement = quantBlock.querySelector('.quant_tooltips');
                                    let quantTooltips = '';
                                    if ($(tooltipsElement).hasClass('note-editor')) {
                                        quantTooltips = $(tooltipsElement).summernote('code').trim();
                                    } else {
                                        quantTooltips = tooltipsElement.value.trim();
                                    }
                                    
                                    const quantFormula = quantBlock.querySelector('.quant_formula').value.trim();

                                    if (!quantName ) {
                                        throw new Error(`กรุณากรอกข้อมูลเกณฑ์ปริมาณหลักที่ ${quantIndex + 1}`);
                                    }

                                    const quantMain = {
                                        name: quantName,
                                        tooltips: quantTooltips || null,
                                        formula: quantFormula || 'D = A × C / B',
                                        quantity_sub_criterias: []
                                    };

                                    // Collect quantity sub criteria
                                    quantBlock.querySelectorAll('.quant_sub_criteria_block').forEach((subBlock, subIndex) => {
                                        const subName = subBlock.querySelector('.quant_sub_name').value.trim();
                                        const scoreA = subBlock.querySelector('.score_a').value;
                                        const scoreB = subBlock.querySelector('.score_b').value;

                                        if (!subName || !scoreA || !scoreB) {
                                            throw new Error(`กรุณากรอกข้อมูลเกณฑ์ปริมาณย่อยที่ ${subIndex + 1}`);
                                        }

                                        quantMain.quantity_sub_criterias.push({
                                            name: subName,
                                            sequence: subIndex + 1,
                                            score_a: parseFloat(scoreA),
                                            score_b: parseFloat(scoreB)
                                        });
                                    });

                                    evalData.quantity_main_criterias.push(quantMain);
                                });
                            }

                            // Collect quality criteria if enabled
                            if (evalBlock.querySelector('.quality_criteria_type').checked) {
                                evalBlock.querySelectorAll('.qual_criteria_block').forEach((qualBlock, qualIndex) => {
                                    const qualName = qualBlock.querySelector('.qual_name').value.trim();
                                    const qualRatio = qualBlock.querySelector('.qual_ratio').value;
                                    
                                    // Get content from Summernote or textarea
                                    const tooltipsElement = qualBlock.querySelector('.qual_tooltips');
                                    let qualTooltips = '';
                                    if ($(tooltipsElement).hasClass('note-editor')) {
                                        qualTooltips = $(tooltipsElement).summernote('code').trim();
                                    } else {
                                        qualTooltips = tooltipsElement.value.trim();
                                    }

                                    if (!qualName || !qualRatio) {
                                        throw new Error(`กรุณากรอกชื่อเกณฑ์และสัดส่วนคะแนนสำหรับเกณฑ์คุณภาพหลักที่ ${qualIndex + 1}`);
                                    }

                                    const qualMain = {
                                        name: qualName,
                                        ratio: parseInt(qualRatio),
                                        tooltips: qualTooltips || null,
                                        sequence: qualIndex + 1,
                                        quality_sub_criterias: []
                                    };

                            // Collect quality sub criteria
                            qualBlock.querySelectorAll('.qual_sub_criteria_block').forEach((subBlock, subIndex) => {
                                const subName = subBlock.querySelector('.qual_sub_name').value.trim();
                                const numScore = subBlock.querySelector('.num_score').value;
                                // Get content from Summernote editor if available, otherwise from textarea
                                const descriptionTextarea = subBlock.querySelector('.qual_sub_description');
                                const subDescription = $(descriptionTextarea).hasClass('note-editor')
                                    ? $(descriptionTextarea).summernote('code') 
                                    : descriptionTextarea.value.trim() || '';

                                if (!subName || !numScore) {
                                    throw new Error(`กรุณากรอกข้อมูลเกณฑ์คุณภาพย่อยที่ ${subIndex + 1}`);
                                }

                                qualMain.quality_sub_criterias.push({
                                    name: subName,
                                    sequence: subIndex + 1,
                                    num_score: parseFloat(numScore),
                                    description: subDescription
                                });
                            });

                            evalData.quality_main_criterias.push(qualMain);
                        });
                    }

                    category.evaluation_lists.push(evalData);
                });

                formData.categories.push(category);
            });

            return formData;
        }
    </script>
@endpush
