{{-- ตารางรายการรอบการประเมิน --}}
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">รายการรอบการประเมิน</h2>
    </div>

    @if ($assignmentData->count() > 0)
        <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[160px]">
                            ระยะเวลาประเมิน
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[220px]">
                            เกณฑ์การประเมิน
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px]">
                            ผู้ประเมิน
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[160px]">
                            ผู้รับการประเมิน
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[120px]">
                            สถานะ
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[140px]">
                            การดำเนินการ
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($assignmentData as $assignmentRow)
                        @include('assignment-data.partials.index-table-row', [
                            'assignmentRow' => $assignmentRow,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $assignmentData->links() }}
        </div>
    @else
        @include('assignment-data.partials.index-empty-state')
    @endif
</div>
