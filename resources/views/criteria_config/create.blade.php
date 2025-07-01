@extends('layouts.app')

@section('content')
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">สร้างเกณฑ์การประเมินใหม่</h1>
                <p class="text-gray-600">กรุณากรอกข้อมูลเกณฑ์การประเมินให้ครบถ้วน</p>
            </div>

            <form id="jsonForm" action="{{ route('reports.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Report Datas -->
                <div class="report_datas_block bg-white p-6 rounded-lg shadow-md transition-all hover:shadow-lg">
                    <h2 class="font-bold text-xl text-gray-800 mb-4 flex items-center">
                        <span
                            class="bg-blue-600 text-white rounded-full w-7 h-7 flex items-center justify-center mr-2">1</span>
                        ข้อมูลเกณฑ์การประเมิน
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label for="report_title" class="block text-sm font-medium text-gray-700 mb-1">ชื่อเกณฑ์</label>
                            <input id="report_title"
                                class="report_title border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="ชื่อเกณฑ์การประเมิน">
                        </div>
                        <div>
                            <label for="report_description"
                                class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดเกณฑ์</label>
                            <textarea id="report_description" rows="3"
                                class="report_description border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="รายละเอียดเพิ่มเติมของเกณฑ์"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="assessment_type"
                                    class="block text-sm font-medium text-gray-700 mb-1">ประเภทการประเมิน</label>
                                <select id="assessment_type"
                                    class="assessment_type border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="technical">กลุ่มวิชาการ</option>
                                    <option value="support">กลุ่มสนับสนุน</option>
                                </select>
                            </div>
                            <div>
                                <label for="comment"
                                    class="block text-sm font-medium text-gray-700 mb-1">ความคิดเห็นเพิ่มเติม</label>
                                <input id="comment"
                                    class="comment border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="ความคิดเห็นเพิ่มเติม">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div id="categories_container" class="space-y-6">
                    <h2 class="font-bold text-xl text-gray-800 mb-2 flex items-center">
                        <span
                            class="bg-blue-600 text-white rounded-full w-7 h-7 flex items-center justify-center mr-2">2</span>
                        หมวดหมู่การประเมิน
                    </h2>

                    <!-- Example/Initial Category Block -->
                    <div
                        class="category_block bg-white p-6 rounded-lg shadow border-l-4 border-blue-500 transition-all hover:shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg text-gray-800">หมวดหมู่การประเมิน</h3>
                            <button type="button" class="delete_category_btn text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่หลัก</label>
                                <input
                                    class="main_categories border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="ชื่อหมวดหมู่หลัก">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่ย่อย</label>
                                <input
                                    class="sub_categories border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="ชื่อหมวดหมู่ย่อย">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ลำดับ</label>
                            </div>
                        </div>

                        <div class="evaluation_lists_container space-y-4 mt-6">
                            <h4 class="font-medium text-lg text-gray-700 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                รายการประเมิน
                            </h4>

                            <div class="evaluation_list_block bg-gray-50 p-4 rounded-md border border-gray-200">
                                <div class="flex justify-between items-center mb-3">
                                    <h5 class="font-medium text-gray-700">รายการประเมิน</h5>
                                    <button type="button" class="delete_eval_btn text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อรายการ</label>
                                        <input
                                            class="eval_name border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2"
                                            placeholder="ชื่อรายการประเมิน">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">คะแนนรวม</label>
                                        <input type="number"
                                            class="sum_score border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2"
                                            placeholder="คะแนนรวม">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">ลำดับ</label>
                                        <input type="number"
                                            class="eval_sequence border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2"
                                            placeholder="ลำดับ">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">หมายเหตุ</label>
                                        <input
                                            class="annotation border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2"
                                            placeholder="หมายเหตุ">
                                    </div>
                                </div>

                                <!-- Quantity Criteria Section -->
                                <div class="mb-5">
                                    <h6 class="font-medium text-gray-700 mb-2 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                        </svg>
                                        เกณฑ์ด้านปริมาณ
                                    </h6>

                                    <div
                                        class="quantity_main_criterias_container space-y-3 pl-4 border-l-2 border-green-300">
                                        <div class="quant_criteria_block bg-white p-3 rounded shadow-sm">
                                            <div class="flex justify-between items-center mb-2">
                                                <h6 class="text-sm font-medium text-gray-700">เกณฑ์ปริมาณหลัก</h6>
                                                <button type="button"
                                                    class="delete_quant_btn text-red-500 hover:text-red-700 text-xs">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">ชื่อเกณฑ์</label>
                                                    <input
                                                        class="quant_name border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 block w-full p-1.5 text-sm"
                                                        placeholder="ชื่อเกณฑ์ปริมาณ">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">คำอธิบาย</label>
                                                    <input
                                                        class="quant_tooltips border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 block w-full p-1.5 text-sm"
                                                        placeholder="คำอธิบายเพิ่มเติม">
                                                </div>
                                            </div>

                                            <div
                                                class="quant_sub_criteria_container space-y-2 pl-3 border-l-2 border-green-100 mb-2">
                                                <div class="quant_sub_criteria_block bg-gray-50 p-2 rounded">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span
                                                            class="text-xs font-medium text-gray-600">เกณฑ์ปริมาณย่อย</span>
                                                        <button type="button"
                                                            class="delete_quant_sub_btn text-red-500 hover:text-red-700 text-xs">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-600 mb-1">ชื่อเกณฑ์ย่อย</label>
                                                            <input
                                                                class="quant_sub_name border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 block w-full p-1 text-sm"
                                                                placeholder="ชื่อเกณฑ์ย่อย">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-600 mb-1">ลำดับ</label>
                                                            <input type="number"
                                                                class="quant_sub_sequence border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 block w-full p-1 text-sm"
                                                                placeholder="ลำดับ">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-600 mb-1">คะแนน
                                                                A</label>
                                                            <input type="number"
                                                                class="score_a border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 block w-full p-1 text-sm"
                                                                placeholder="คะแนน A">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-600 mb-1">คะแนน
                                                                B</label>
                                                            <input type="number"
                                                                class="score_b border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 block w-full p-1 text-sm"
                                                                placeholder="คะแนน B">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button"
                                                class="add_quant_sub_criteria_btn text-xs px-2 py-1 bg-green-50 text-green-600 rounded hover:bg-green-100 transition flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                เพิ่มปริมาณย่อย
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button"
                                        class="add_quant_criteria_btn mt-2 text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        เพิ่มเกณฑ์ปริมาณหลัก
                                    </button>
                                </div>

                                <!-- Quality Criteria Section -->
                                <div class="mb-3">
                                    <h6 class="font-medium text-gray-700 mb-2 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-purple-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        เกณฑ์ด้านคุณภาพ
                                    </h6>

                                    <div
                                        class="quality_main_criterias_container space-y-3 pl-4 border-l-2 border-purple-300">
                                        <div class="qual_criteria_block bg-white p-3 rounded shadow-sm">
                                            <div class="flex justify-between items-center mb-2">
                                                <h6 class="text-sm font-medium text-gray-700">เกณฑ์คุณภาพหลัก</h6>
                                                <button type="button"
                                                    class="delete_qual_btn text-red-500 hover:text-red-700 text-xs">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">ชื่อเกณฑ์</label>
                                                    <input
                                                        class="qual_name border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full p-1.5 text-sm"
                                                        placeholder="ชื่อเกณฑ์คุณภาพ">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">สัดส่วน</label>
                                                    <input type="number"
                                                        class="qual_ratio border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full p-1.5 text-sm"
                                                        placeholder="สัดส่วน">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">คำอธิบาย</label>
                                                    <input
                                                        class="qual_tooltips border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full p-1.5 text-sm"
                                                        placeholder="คำอธิบายเพิ่มเติม">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1">ลำดับ</label>
                                                    <input type="number"
                                                        class="qual_sequence border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full p-1.5 text-sm"
                                                        placeholder="ลำดับ">
                                                </div>
                                            </div>

                                            <div
                                                class="qual_sub_criterias_container space-y-2 pl-3 border-l-2 border-purple-100 mb-2">
                                                <div class="qual_sub_criteria_block bg-gray-50 p-2 rounded">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span
                                                            class="text-xs font-medium text-gray-600">เกณฑ์คุณภาพย่อย</span>
                                                        <button type="button"
                                                            class="delete_qual_sub_btn text-red-500 hover:text-red-700 text-xs">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-600 mb-1">ชื่อเกณฑ์ย่อย</label>
                                                            <input
                                                                class="qual_sub_name border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full p-1 text-sm"
                                                                placeholder="ชื่อเกณฑ์ย่อย">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-600 mb-1">ลำดับ</label>
                                                            <input type="number"
                                                                class="qual_sub_sequence border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full p-1 text-sm"
                                                                placeholder="ลำดับ">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-xs font-medium text-gray-600 mb-1">คะแนนสูงสุด</label>
                                                            <input type="number"
                                                                class="num_score border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full p-1 text-sm"
                                                                placeholder="คะแนนสูงสุด">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button"
                                                class="add_qual_sub_criteria_btn text-xs px-2 py-1 bg-purple-50 text-purple-600 rounded hover:bg-purple-100 transition flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                เพิ่มคุณภาพย่อย
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button"
                                        class="add_qual_criteria_btn mt-2 text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        เพิ่มเกณฑ์คุณภาพหลัก
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button"
                            class="add_evaluation_list_btn mt-4 text-sm px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            เพิ่มรายการประเมิน
                        </button>
                    </div>
                </div>

                <button type="button" id="add_category_btn"
                    class="my-4 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    เพิ่มหมวดหมู่การประเมิน
                </button>

                <div class="flex justify-end mt-8 space-x-3">
                    <button type="button" id="reset_form_btn"
                        class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        ล้างฟอร์ม
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
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
        class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-5 rounded-lg shadow-lg text-center">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mx-auto mb-3"></div>
            <p class="text-gray-700">กำลังส่งข้อมูล กรุณารอสักครู่...</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success_modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
            <div class="text-center">
                <div class="bg-green-100 rounded-full p-3 mx-auto w-16 h-16 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">ส่งข้อมูลสำเร็จ</h3>
                <p class="text-gray-600 mb-4">ข้อมูลเกณฑ์การประเมินถูกบันทึกเรียบร้อยแล้ว</p>
                <div class="flex justify-center space-x-3">
                    <button id="close_modal_btn"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">ปิด</button>
                    <a href="{{ route('reports.index') }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">ไปหน้ารายการเกณฑ์</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Helper to clone a block and clear values
        function cloneAndClear(blockSelector) {
            let node = document.querySelector(blockSelector).cloneNode(true);
            // Remove any nested additional arrays (to avoid "copying" other add-on blocks)
            node.querySelectorAll('input').forEach(inp => inp.value = '');
            node.querySelectorAll(
                '.evaluation_list_block:not(:first-child), .quant_criteria_block:not(:first-child), .qual_criteria_block:not(:first-child), .quant_sub_criteria_block:not(:first-child), .qual_sub_criteria_block:not(:first-child)'
            ).forEach(e => e.remove());
            return node;
        }

        function showLoading() {
            document.getElementById('loading_overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading_overlay').classList.add('hidden');
        }

        function showSuccessModal() {
            document.getElementById('success_modal').classList.remove('hidden');
        }

        document.addEventListener('click', function(e) {
            // Delete category
            if (e.target.closest('.delete_category_btn')) {
                const block = e.target.closest('.category_block');
                if (document.querySelectorAll('.category_block').length > 1) {
                    if (confirm('ต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) {
                        block.remove();
                    }
                } else {
                    alert('ไม่สามารถลบหมวดหมู่สุดท้ายได้');
                }
            }
            // Delete evaluation list
            if (e.target.closest('.delete_eval_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
                if (container.querySelectorAll('.evaluation_list_block').length > 1) {
                    block.remove();
                } else {
                    alert('ต้องมีรายการประเมินอย่างน้อย 1 รายการ');
                }
            }
            // Delete quantity criteria
            if (e.target.closest('.delete_quant_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
                if (container.querySelectorAll('.quant_criteria_block').length > 1) {
                    block.remove();
                }
            }
            // Delete quantity sub criteria
            if (e.target.closest('.delete_quant_sub_btn')) {
                const block = e.target.closest('.quant_sub_criteria_block');
                const container = block.closest('.quant_sub_criteria_container');
                if (container.querySelectorAll('.quant_sub_criteria_block').length > 1) {
                    block.remove();
                }
            }
            // Delete quality criteria
            if (e.target.closest('.delete_qual_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
                if (container.querySelectorAll('.qual_criteria_block').length > 1) {
                    block.remove();
                }
            }
            // Delete quality sub criteria
            if (e.target.closest('.delete_qual_sub_btn')) {
                const block = e.target.closest('.qual_sub_criteria_block');
                const container = block.closest('.qual_sub_criterias_container');
                if (container.querySelectorAll('.qual_sub_criteria_block').length > 1) {
                    block.remove();
                }
            }

            // Add category
            if (e.target.closest('#add_category_btn')) {
                let newBlock = cloneAndClear('.category_block');
                document.getElementById('categories_container').appendChild(newBlock);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            // Add evaluation list
            if (e.target.closest('.add_evaluation_list_btn')) {
                let parent = e.target.closest('.category_block').querySelector('.evaluation_lists_container');
                let newBlock = cloneAndClear('.evaluation_list_block');
                parent.appendChild(newBlock);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            // Add quant criteria
            if (e.target.closest('.add_quant_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quantity_main_criterias_container');
                let newBlock = cloneAndClear('.quant_criteria_block');
                parent.appendChild(newBlock);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            // Add quant sub criteria
            if (e.target.closest('.add_quant_sub_criteria_btn')) {
                let parent = e.target.closest('.quant_criteria_block').querySelector(
                    '.quant_sub_criteria_container');
                let newBlock = cloneAndClear('.quant_sub_criteria_block');
                parent.appendChild(newBlock);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            // Add qual criteria
            if (e.target.closest('.add_qual_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quality_main_criterias_container');
                let newBlock = cloneAndClear('.qual_criteria_block');
                parent.appendChild(newBlock);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            // Add qual sub criteria
            if (e.target.closest('.add_qual_sub_criteria_btn')) {
                let parent = e.target.closest('.qual_criteria_block').querySelector(
                    '.qual_sub_criterias_container');
                let newBlock = cloneAndClear('.qual_sub_criteria_block');
                parent.appendChild(newBlock);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });

        // Reset form
        document.getElementById('reset_form_btn').addEventListener('click', function() {
            if (confirm('ต้องการล้างข้อมูลทั้งหมดใช่หรือไม่?')) {
                document.getElementById('jsonForm').reset();
            }
        });

        // Close modal
        document.getElementById('close_modal_btn').addEventListener('click', function() {
            document.getElementById('success_modal').classList.add('hidden');
        });

        // JSON submit logic
        document.getElementById('jsonForm').addEventListener('submit', function(event) {
            event.preventDefault();
            showLoading();
            let finalData = {
                version_name: 'Demo Version 2024',
                created_by: 1,
                report_datas: [],
                categories: []
            };
            // -- report_datas --
            let rd = document.querySelector('.report_datas_block');
            finalData.report_datas.push({
                report_title: rd.querySelector('.report_title').value,
                report_description: rd.querySelector('.report_description').value,
                assessment_type: rd.querySelector('.assessment_type').value,
                comment: rd.querySelector('.comment').value
            });
            // -- categories[] --
            document.querySelectorAll('#categories_container .category_block').forEach((catBlock, catI) => {
                let category = {
                    main_categories: catBlock.querySelector('.main_categories').value,
                    sub_categories: catBlock.querySelector('.sub_categories').value,
                    sequence: Number(catBlock.querySelector('.sequence').value),
                    evaluation_lists: []
                };
                catBlock.querySelectorAll('.evaluation_lists_container .evaluation_list_block').forEach(
                    (evalBlock, evalI) => {
                        let evalList = {
                            name: evalBlock.querySelector('.eval_name').value,
                            sum_score: Number(evalBlock.querySelector('.sum_score').value),
                            sequence: Number(evalBlock.querySelector('.eval_sequence').value),
                            annotation: evalBlock.querySelector('.annotation').value,
                            quantity_main_criterias: [],
                            quality_main_criterias: []
                        };
                        // Quantitative main
                        evalBlock.querySelectorAll(
                            '.quantity_main_criterias_container .quant_criteria_block').forEach(
                            (qMain, qj) => {
                                let quant = {
                                    name: qMain.querySelector('.quant_name').value,
                                    tooltips: qMain.querySelector('.quant_tooltips').value,
                                    quantity_sub_criterias: []
                                };
                                // Quantitative sub
                                qMain.querySelectorAll(
                                    '.quant_sub_criteria_container .quant_sub_criteria_block'
                                ).forEach((subQ, sk) => {
                                    quant.quantity_sub_criterias.push({
                                        name: subQ.querySelector('.quant_sub_name')
                                            .value,
                                        sequence: Number(subQ.querySelector(
                                            '.quant_sub_sequence').value),
                                        score_a: Number(subQ.querySelector(
                                            '.score_a').value),
                                        score_b: Number(subQ.querySelector(
                                            '.score_b').value)
                                    });
                                });
                                evalList.quantity_main_criterias.push(quant);
                            });

                        // Qualitative main
                        evalBlock.querySelectorAll(
                            '.quality_main_criterias_container .qual_criteria_block').forEach((
                            qMain, qj) => {
                            let qual = {
                                name: qMain.querySelector('.qual_name').value,
                                ratio: Number(qMain.querySelector('.qual_ratio').value),
                                tooltips: qMain.querySelector('.qual_tooltips').value,
                                sequence: Number(qMain.querySelector('.qual_sequence')
                                    .value),
                                quality_sub_criterias: []
                            };
                            // Qualitative sub
                            qMain.querySelectorAll(
                                    '.qual_sub_criterias_container .qual_sub_criteria_block')
                                .forEach((subQ, sk) => {
                                    qual.quality_sub_criterias.push({
                                        name: subQ.querySelector('.qual_sub_name')
                                            .value,
                                        sequence: Number(subQ.querySelector(
                                            '.qual_sub_sequence').value),
                                        num_score: Number(subQ.querySelector(
                                            '.num_score').value)
                                    });
                                });
                            evalList.quality_main_criterias.push(qual);
                        });

                        category.evaluation_lists.push(evalList);
                    }
                );
                finalData.categories.push(category);
            });

            let isValid = true;
            let errors = [];

            if (!finalData.report_datas[0].report_title) {
                errors.push('กรุณากรอกชื่อเกณฑ์');
                isValid = false;
            }
            finalData.categories.forEach((cat, index) => {
                if (!cat.main_categories) {
                    errors.push(`กรุณากรอกชื่อหมวดหมู่หลักในหมวดที่ ${index + 1}`);
                    isValid = false;
                }
                if (!cat.sub_categories) {
                    errors.push(`กรุณากรอกชื่อหมวดหมู่ย่อยในหมวดที่ ${index + 1}`);
                    isValid = false;
                }
                cat.evaluation_lists.forEach((evalList, evalIndex) => {
                    if (!evalList.name) {
                        errors.push(
                            `กรุณากรอกชื่อรายการประเมินในหมวดที่ ${index + 1} รายการที่ ${evalIndex + 1}`
                        );
                        isValid = false;
                    }
                });
            });

            if (!isValid) {
                hideLoading();
                alert('กรุณาตรวจสอบข้อมูล:\n' + errors.join('\n'));
                return;
            }

            fetch("{{ route('reports.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(finalData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    hideLoading();
                    showSuccessModal();
                    console.log('Success:', data);
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการส่งข้อมูล: ' + error.message);
                });

            console.log('Final JSON:', finalData);
        });

        function adjustForMobile() {
            if (window.innerWidth < 640) {
                document.querySelectorAll('label').forEach(label => {
                    label.classList.add('text-xs');
                });
            } else {
                document.querySelectorAll('label').forEach(label => {
                    label.classList.remove('text-xs');
                });
            }
        }

        window.addEventListener('load', adjustForMobile);
        window.addEventListener('resize', adjustForMobile);
    </script>
@endpush
