@extends('layouts.app')

@section('content')
<div class="py-12 bg-gradient-to-r from-blue-50 to-indigo-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-3">แก้ไขชื่อเวอร์ชันเกณฑ์การประเมิน</h1>
            <p class="text-gray-600 text-lg">คุณสามารถแก้ไขชื่อเวอร์ชันได้เท่านั้น ข้อมูลอื่น ๆ จะแสดงเพื่ออ้างอิง</p>
        </div>
        <form id="editVersionForm" action="{{ route('report-structure.update', ['id' => $id ?? '']) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <label class="block text-gray-700 font-bold mb-2">ชื่อเวอร์ชัน</label>
                <input type="text" name="version_name" id="version_name" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="" required>
                <div class="flex justify-end mt-6 space-x-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-500">บันทึกชื่อเวอร์ชัน</button>
                    <a href="{{ route('criteria_config.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md font-semibold hover:bg-gray-400">ย้อนกลับ</a>
                </div>
            </div>
            <div id="criteria-details">
                <!-- ข้อมูลรายละเอียดเวอร์ชันจะแสดงที่นี่ -->
                <div class="flex justify-center py-10 text-gray-500">Loading...</div>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetchVersionDetails();
    });
    function fetchVersionDetails() {
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
            if (data.data) {
                renderVersionDetails(data.data);
                document.getElementById('version_name').value = data.data.version_name || '';
            } else {
                document.getElementById('criteria-details').innerHTML = '<div class="text-center text-red-500">ไม่พบข้อมูลเวอร์ชัน</div>';
            }
        })
        .catch(() => {
            document.getElementById('criteria-details').innerHTML = '<div class="text-center text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        });
    }
    function renderVersionDetails(data) {
        let html = '';
        html += `<div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div class="text-2xl font-bold text-blue-700 flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    ${data.version_name ?? '-'}
                </div>
            </div>`;
        if (data.report_datas && data.report_datas.length > 0) {
            html += `<div class="mb-6">
                <div class="font-semibold text-lg text-gray-800 mb-2">ข้อมูลเกณฑ์</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            `;
            data.report_datas.forEach(rd => {
                html += `<div class="bg-blue-50 border border-blue-100 rounded-lg p-4 shadow-sm">
                    <div class="font-bold text-blue-700 text-base">ชื่อเกณฑ์: ${rd.report_title}</div>
                    <div class="text-gray-700 text-sm mb-1">คำอธิบายเกณฑ์: ${rd.report_description}</div>
                    <div class="text-xs text-gray-500">ประเภท: <span class="font-semibold">${rd.assessment_type}</span></div>
                    ${rd.comment ? `<div class=\"text-xs text-gray-400 mt-1\">หมายเหตุ: ${rd.comment}</div>` : ''}
                </div>`;
            });
            html += `</div></div>`;
        }
        if (data.categories && data.categories.length > 0) {
            html += `<div class="mb-2 font-semibold text-lg text-gray-800">หมวดหมู่เกณฑ์ประเมิน</div>`;
            data.categories.forEach(cat => {
                html += `<div class="border border-gray-200 rounded-lg p-4 my-3 bg-gray-50 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="font-bold text-indigo-700">${cat.main_categories}</span>
                    </div>`;
                if (cat.evaluation_lists && cat.evaluation_lists.length > 0) {
                    html += `<div class="ml-2">
                        <div class="space-y-3">
                    `;
                    cat.evaluation_lists.forEach(ev => {
                        html += `<div class="bg-white border border-gray-100 rounded p-3 shadow-sm">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-semibold text-blue-700">${ev.name}</span>
                                <span class="text-xs text-gray-500">(คะแนนรวม: ${ev.sum_score})</span>
                            </div>`;
                        if (ev.annotation) html += `<div class="text-xs text-gray-500 mb-1">หมายเหตุ: ${ev.annotation}</div>`;
                        if (ev.quantity_main_criterias && ev.quantity_main_criterias.length > 0) {
                            html += `<div class="ml-2 mt-2">
                                <ul class="list-disc ml-6 mt-1 space-y-1">
                            `;
                            ev.quantity_main_criterias.forEach(qm => {
                                html += `<li><span class="font-semibold text-green-700">${qm.name}</span> <span class="text-xs text-gray-400">[${qm.tooltips}]</span>`;
                                if (qm.quantity_sub_criterias && qm.quantity_sub_criterias.length > 0) {
                                    html += `<div class=\"overflow-x-auto mt-2\"><table class=\"min-w-[320px] w-full text-sm border border-gray-200 rounded\">`;
                                    html += `<thead class=\"bg-green-100\"><tr>
                                        <th class=\"px-3 py-2 text-left font-semibold text-black\">ชื่อเกณฑ์ย่อย</th>
                                        <th class=\"px-3 py-2 text-left font-semibold text-black\">ค่านํ้ำหนักคะแนน(A)</th>
                                        <th class=\"px-3 py-2 text-left font-semibold text-black\">หน่วยภาระงานมาตรฐาน(B)</th>
                                    </tr></thead><tbody>`;
                                    qm.quantity_sub_criterias.forEach(qs => {
                                        html += `<tr class=\"even:bg-green-50\">`;
                                        html += `<td class=\"px-3 py-2 text-black\">${qs.name}</td>`;
                                        html += `<td class=\"px-3 py-2 text-black\">${qs.score_a}</td>`;
                                        html += `<td class=\"px-3 py-2 text-black\">${qs.score_b}</td>`;
                                        html += `</tr>`;
                                    });
                                    html += `</tbody></table></div>`;
                                }
                                html += `</li>`;
                            });
                            html += `</ul></div>`;
                        }
                        if (ev.quality_main_criterias && ev.quality_main_criterias.length > 0) {
                            html += `<div class="ml-2 mt-2">
                                <ul class="list-disc ml-6 mt-1 space-y-1">
                            `;
                            ev.quality_main_criterias.forEach(qm => {
                                html += `<li><span class="font-semibold text-purple-700">${qm.name}</span> <span class="text-xs text-gray-400">[${qm.tooltips}]</span> <span class="text-xs text-gray-500">(สัดส่วน: ${qm.ratio})</span>`;
                                if (qm.quality_sub_criterias && qm.quality_sub_criterias.length > 0) {
                                    html += `<div class=\"overflow-x-auto mt-2\"><table class=\"min-w-[320px] w-full text-sm border border-gray-200 rounded\">`;
                                    html += `<thead class=\"bg-purple-100\"><tr>
                                        <th class=\"px-3 py-2 text-left font-semibold text-black\">ชื่อเกณฑ์ย่อย</th>
                                        <th class=\"px-3 py-2 text-left font-semibold text-black\">คะแนน</th>
                                    </tr></thead><tbody>`;
                                    qm.quality_sub_criterias.forEach(qs => {
                                        html += `<tr class=\"even:bg-purple-50\">`;
                                        html += `<td class=\"px-3 py-2 text-black\">${qs.name}</td>`;
                                        html += `<td class=\"px-3 py-2 text-black\">${qs.num_score}</td>`;
                                        html += `</tr>`;
                                    });
                                    html += `</tbody></table></div>`;
                                }
                                html += `</li>`;
                            });
                            html += `</ul></div>`;
                        }
                        html += `</div>`;
                    });
                    html += `</div></div>`;
                }
                html += `</div>`;
            });
        }
        html += `</div>`;
        document.getElementById('criteria-details').innerHTML = html;
    }
</script>
@endsection
