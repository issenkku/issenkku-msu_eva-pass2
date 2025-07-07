@props([
    'evaluationItems' => [],
    'title' => 'ด้านปริมาณ',
    'readonly' => false
])

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="bg-purple-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead class="bg-purple-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b border-r border-gray-200">
                        รายการที่ประเมิน
                    </th>
                    <th class="px-6 py-3 text-center text-sm font-medium text-gray-700 border-b border-gray-200">
                        หน่วยภาระงานที่ทำได้ (ตาม TOR)
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($evaluationItems as $index => $item)
                    @php
                        $isMain = $item['is_main'] ?? (preg_match('/^\d+\./', $item['title'] ?? '') && !preg_match('/^\d+\.\d+/', $item['title']));
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isMain ? 'bg-purple-50 font-semibold' : '' }}">
                        <td class="px-6 py-4 text-base text-gray-800 border-r border-gray-200">
                            @if($isMain)
                                {{ $item['title'] ?? "รายการที่ " . ($index + 1) }}
                            @else
                                {{ ($item['title'] ?? "รายการที่ " . ($index + 1)) }}
                            @endif
                            @if(!empty($item['subtitle']))
                                <div class="text-sm text-gray-600 mt-1">{{ $item['subtitle'] }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-left border-r border-gray-200">
                            @if($readonly)
                                <div class="text-base text-gray-800 ml-2">
                                    {{ $item['tor_compliant'] ?? 'ไม่ระบุ' }}
                                </div>
                            @elseif(!$isMain)
                                <input type="text" 
                                    name="tor_compliant[{{ $item['sub_criteria_id'] ?? $index }}]" 
                                    value="{{ $item['tor_compliant'] ?? '' }}"
                                    class="form-input text-base w-full h-10 ml-2 px-3 rounded border-gray-300 focus:ring-purple-500 focus:border-purple-500" 
                                    placeholder="ใส่ข้อมูล">
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $readonly ? 2 : 5 }}" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium">ไม่พบเกณฑ์การประเมิน</p>
                                <p class="text-sm">กรุณาติดต่อผู้ดูแลระบบเพื่อตั้งค่าเกณฑ์การประเมิน</p>
                            </div>
                        </td>
                    </tr>
                @endforelse

                {{-- Evidence field only at the end --}}
                @if(!$readonly)
                    <tr class="bg-gray-50">
                        <td class="px-6 py-4 text-base font-medium text-gray-700 text-right border-r border-gray-200">
                            ลิงก์หลักฐานประกอบ:
                        </td>
                        <td class="px-6 py-4 text-left" colspan="{{ $readonly ? 1 : 4 }}">
                            <input type="url"
                                name="evidence_link"
                                class="form-input text-base w-full h-10 ml-2 rounded border-gray-300 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="ใส่ URL ของหลักฐาน">
                        </td>
                    </tr>
                @elseif(!empty($evaluationItems[0]['evidence']))
                    <tr class="bg-gray-50">
                        <td class="px-6 py-4 text-base font-medium text-gray-700 text-right border-r border-gray-200">
                            ลิงก์หลักฐาน:
                        </td>
                        <td class="px-6 py-4 text-left" colspan="{{ $readonly ? 1 : 4 }}">
                            <a href="{{ $evaluationItems[0]['evidence'] }}" target="_blank" class="text-purple-600 text-sm underline ml-2">
                                {{ $evaluationItems[0]['evidence'] }}
                            </a>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>