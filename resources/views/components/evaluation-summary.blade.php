@props(['evaluations', 'statusCounts'])

<div class="bg-white rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">ภาพรวมสถานะการประเมิน</h3>
    
    <!-- Status Badges -->
    <div class="flex gap-3 mb-6 flex-wrap">
        @foreach($statusCounts as $status => $count)
            <x-status-badge :status="$status" :count="$count" />
        @endforeach
    </div>
    
    <!-- Table Format -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="text-left p-4 border-b font-medium text-gray-800">อันดับ</th>
                    <th class="text-left p-4 border-b font-medium text-gray-800">รายการประเมิน</th>
                    <th class="text-left p-4 border-b font-medium text-gray-800">วันที่เริ่มประเมิน</th>
                    <th class="text-left p-4 border-b font-medium text-gray-800">วันที่สิ้นสุดประเมิน</th>
                    <th class="text-left p-4 border-b font-medium text-gray-800">ผู้ประเมิน</th>
                    <th class="text-center p-4 border-b font-medium text-gray-800">สถานะ</th>
                    <th class="text-center p-4 border-b font-medium text-gray-800">การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluations as $index => $evaluation)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 border-b text-gray-500">{{ $index + 1 }}</td>
                        <td class="p-4 border-b">
                            <div class="font-medium text-gray-800">{{ $evaluation['title'] }}</div>
                            @if(isset($evaluation['description']))
                                <div class="text-sm text-gray-500 mt-1">{{ $evaluation['description'] }}</div>
                            @endif
                        </td>
                        <td class="p-4 border-b text-gray-500">{{ $evaluation['start_date'] ?? '-' }}</td>
                        <td class="p-4 border-b text-gray-500">{{ $evaluation['end_date'] ?? '-' }}</td>
                        <td class="p-4 border-b text-gray-500">{{ $evaluation['evaluator'] ?? '-' }}</td>
                        <td class="p-4 border-b text-center">
                            @php
                                $statusClasses = [
                                    'ยังไม่ประเมิน' => 'bg-red-100 text-red-800',
                                    'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800',
                                    'รอผลการประเมิน' => 'bg-yellow-100 text-yellow-800',
                                    'ประเมินแล้ว' => 'bg-green-100 text-green-800',
                                    'แสดงผลการประเมิน' => 'bg-purple-100 text-purple-800'
                                ];
                                $statusClass = $statusClasses[$evaluation['status']] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                                {{ $evaluation['status'] }}
                            </span>
                        </td>
                        <td class="p-4 border-b text-center">
                            @php
                                $actions = [
                                    'ยังไม่ประเมิน' => [
                                        'label' => 'เริ่มประเมิน',
                                        'classes' => 'bg-red-500 hover:bg-red-600 text-white'
                                    ],
                                    'กำลังดำเนินการ' => [
                                        'label' => 'ประเมินต่อ',
                                        'classes' => 'bg-blue-500 hover:bg-blue-600 text-white'
                                    ],
                                    'แสดงผลการประเมิน' => [
                                        'label' => 'แสดงผล',
                                        'classes' => 'bg-purple-500 hover:bg-purple-600 text-white'
                                    ],
                                ];

                                $action = $actions[$evaluation['status']] ?? null;
                            @endphp

                            @if($action)
                                <a href="{{ route('evaluation.show', $evaluation['id']) }}"
                                class="inline-block px-4 py-2 text-sm font-medium rounded-md shadow transition duration-200 {{ $action['classes'] }}">
                                    {{ $action['label'] }}
                                </a>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            <p>ไม่มีข้อมูลการประเมิน</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
