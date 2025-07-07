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
                        รายการประเมิน
                    </th>
                    <th class="px-6 py-3 text-center text-sm font-medium text-gray-700 border-b border-gray-200">
                        หน่วยภาระงานที่ทำได้
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($evaluationItems as $index => $item)
                    @php
                        $isMain = preg_match('/^\d+\./', $item['title'] ?? '') && !preg_match('/^\d+\.\d+/', $item['title']);
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isMain ? 'bg-purple-50 font-semibold' : '' }}">
                        <td class="px-6 py-4 text-base text-gray-800 border-r border-gray-200">
                            {{ $item['title'] ?? "รายการที่ " . ($index + 1) }}
                            @if(!empty($item['subtitle']))
                                <div class="text-sm text-gray-600 mt-1">{{ $item['subtitle'] }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-left">
                            @if($readonly)
                                <div class="text-base text-gray-800 ml-2">
                                    {{ $item['tor_compliant'] ?? 'ไม่ระบุ' }}
                                </div>
                            @elseif(!$isMain)
                                <input type="text" 
                                    name="tor_compliant[{{ $index }}]" 
                                    value="{{ $item['tor_compliant'] ?? '' }}"
                                    class="form-input text-base w-full h-10 ml-2 rounded border-gray-300 focus:ring-purple-500 focus:border-purple-500" 
                                    placeholder="ใส่ข้อมูล">
                            @endif
                        </td>
                    </tr>
                @empty
                    @php
                        $defaultItems = [
                            ['title' => '1. เป้าหมายงาน (40 คะแนน)'],
                            ['title' => '1.1 ภาระงานในหน้าที่'],
                            ['title' => '1.2 ภาระด้านการพัฒนาระบบงาน'],
                            ['title' => '1.3 ภาระงานด้านการขึ้นทะเบียน'],
                            ['title' => '1.4 ภาระด้านการศึกษาวิจัย'],
                            ['title' => '1.5 ภาระงานพัฒนาเจ้าหน้าที่'],
                            ['title' => '1.6 ภาระด้านวิจัย']
                        ];
                    @endphp

                    @foreach($defaultItems as $index => $item)
                        @php
                            $isMain = preg_match('/^\d+\./', $item['title']) && !preg_match('/^\d+\.\d+/', $item['title']);
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isMain ? 'bg-purple-50 font-semibold' : '' }}">
                            <td class="px-6 py-4 text-base text-gray-800 border-r border-gray-200">
                                {{ $item['title'] }}
                            </td>
                            <td class="px-6 py-4 text-left">
                                @unless($isMain)
                                    <input type="text" 
                                        name="tor_compliant[{{ $index }}]" 
                                        class="form-input text-base w-full h-10 rounded border-gray-300 focus:ring-purple-500 focus:border-purple-500" 
                                        placeholder="กรอกหน่วยภาระงานที่ทำได้">
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                @endforelse

                {{-- Evidence field only at the end --}}
                @if(!$readonly)
                    <tr class="bg-gray-50">
                        <td class="px-6 py-4 text-base font-medium text-gray-700 text-right border-r border-gray-200">
                            ลิงก์หลักฐานประกอบ:
                        </td>
                        <td class="px-6 py-4 text-left">
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
                        <td class="px-6 py-4 text-left">
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
