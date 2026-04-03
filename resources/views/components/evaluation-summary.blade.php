{{-- สรุปภาพรวมสถานะการประเมินของผู้ถูกประเมิน --}}
@props(['summary', 'evaluations', 'years'])

<div id="evaluationSummary" class="rounded-lg bg-white p-6">
    <h3 class="mb-4 text-lg font-semibold text-gray-800">ภาพรวมสถานะการประเมิน</h3>

    <div class="mb-4 flex flex-wrap justify-between gap-4 border-b px-3 pb-4">
        <x-search-bar placeholder="ค้นหาชื่อ, รายงาน..." />
        <x-filter-badge-single
            name="year"
            placeholder="ปีการประเมินทั้งหมด"
            :options="$years->mapWithKeys(fn($y) => [$y => $y + 543])->toArray()"
        />
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        @foreach ($summary['statusFilters'] as $filter)
            <button
                type="button"
                data-status-filter="{{ $filter['filter'] }}"
                class="evaluation-status-filter inline-block rounded-full px-3 py-1 text-sm font-medium transition {{ $filter['classes'] }} {{ $filter['active'] ? 'ring-2 ring-offset-2 ring-blue-300' : '' }}">
                {{ $filter['label'] }} ({{ $filter['count'] }})
            </button>
        @endforeach
    </div>

    <div class="relative overflow-x-auto">
        <div class="pointer-events-none absolute left-0 top-0 z-10 h-full w-10 bg-gradient-to-r from-white to-transparent"></div>
        <div class="pointer-events-none absolute right-0 top-0 z-10 h-full w-10 bg-gradient-to-l from-white to-transparent"></div>

        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <table class="min-w-[900px] w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">อันดับ</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">รายการประเมิน</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">วันที่เริ่มประเมิน</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">วันที่สิ้นสุดประเมิน</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">ผู้ประเมิน</th>
                        <th class="min-w-[180px] whitespace-nowrap border-b p-4 text-center font-medium text-gray-800">สถานะ</th>
                        <th class="whitespace-nowrap border-b p-4 text-center font-medium text-gray-800">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary['rows'] as $row)
                        @include('components.evaluation-summary-row', ['row' => $row])
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                <i class="fas fa-inbox mb-2 block text-3xl"></i>
                                <p>ไม่มีข้อมูลการประเมิน</p>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="evaluationSummaryEmptyState" class="hidden">
                        <td colspan="7" class="py-8 text-center text-gray-500">
                            <i class="fas fa-filter mb-2 block text-3xl"></i>
                            <p>ไม่มีข้อมูลที่ตรงกับการค้นหา</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $evaluations->links() }}
    </div>
</div>

@include('components.evaluation-summary-reviewer-modal')
@include('components.evaluation-summary-script')
