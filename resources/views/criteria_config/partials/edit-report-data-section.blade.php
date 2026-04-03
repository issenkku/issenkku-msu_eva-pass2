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
                    <option value="กลุ่มบริหาร">กลุ่มบริหาร</option>
                </select>
            </div>
            <div>
                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                <textarea id="comment" rows="4" name="comment" class="comment border border-gray-300 text-gray-900 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition duration-200" placeholder="หมายเหตุเพิ่มเติม"></textarea>
            </div>
        </div>
    </div>
</div>
