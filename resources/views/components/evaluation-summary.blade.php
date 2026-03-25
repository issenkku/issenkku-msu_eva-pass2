{{-- ไฟล์มุมมอง: resources/views\components\evaluation-summary.blade.php --}}
@props(['evaluations', 'statusCounts', 'years'])

@php
    use Carbon\Carbon;

    function formatThaiDate($date)
    {
        if (!$date) {
            return '-';
        }

        Carbon::setLocale('th');
        setlocale(LC_TIME, 'th_TH.UTF-8');

        $thaiMonth = $date->translatedFormat('j F');
        $buddhistYear = $date->year + 543;
        $time = $date->format('H:i');

        return [
            'date' => "{$thaiMonth} {$buddhistYear}",
            'time' => "{$time} น.",
        ];
    }

    // Sort evaluations by most recent first
    $sortedEvaluations = $evaluations->sortByDesc(function ($assignment) {
        $endTime = optional($assignment->assignmentData)->end_time;
        if ($endTime) {
            return Carbon::parse($endTime)->timestamp;
        }

        $startTime = optional($assignment->assignmentData)->start_time;
        if ($startTime) {
            return Carbon::parse($startTime)->timestamp;
        }

        return optional($assignment->report)->updated_at
            ? Carbon::parse($assignment->report->updated_at)->timestamp
            : (optional($assignment)->created_at
                ? Carbon::parse($assignment->created_at)->timestamp
                : 0);
    })->values();

    $filteredStatus = null;
@endphp

<div id="evaluationSummary" class="bg-white rounded-lg p-6" data-initial-status="{{ $filteredStatus ?? '' }}">
    <h3 class="mb-4 text-lg font-semibold text-gray-800">ภาพรวมสถานะการประเมิน</h3>

    <div class="mb-4 flex flex-wrap justify-between gap-4 border-b pb-4 pl-3 pr-3">
        <x-search-bar
            placeholder="ค้นหาชื่อ, รายงาน..."
        />
        <x-filter-badge-single
            name="year"
            placeholder="ปีการประเมินทั้งหมด"
            :options="$years->mapWithKeys(fn($y) => [$y => $y + 543])->toArray()"
        />
    </div>

    @php
        $statusStyles = [
            'ทั้งหมด' => 'bg-gray-100 text-gray-800 hover:bg-gray-200',
            'ยังไม่ประเมิน' => 'bg-red-100 text-red-800 hover:bg-red-200',
            'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800 hover:bg-blue-200',
            'รอผลการประเมิน' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
            'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800 hover:bg-green-200',
        ];

        $firstStatus = array_key_first($statusCounts);
    @endphp

    <div class="mb-6 flex flex-wrap items-center gap-3">
        @foreach($statusCounts as $status => $count)
            @php
                $isShowAll = $status === $firstStatus;
                $isActive = $isShowAll ? is_null(request('status')) : request('status') === $status;
                $style = $statusStyles[$status] ?? 'bg-gray-100 text-gray-800 hover:bg-gray-200';
                $activeClass = $isActive ? 'ring-2 ring-offset-2 ring-blue-300' : '';
            @endphp

            <button
                type="button"
                data-status-filter="{{ $isShowAll ? 'all' : $status }}"
                class="evaluation-status-filter inline-block rounded-full px-3 py-1 text-sm font-medium transition {{ $style }} {{ $activeClass }}">
                {{ $status }} ({{ $count }})
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
                    @forelse($sortedEvaluations as $index => $assignment)
                        @php
                            $report = $assignment->report;
                            $assignmentData = $assignment->assignmentData;

                            $reportTitle = optional(optional($assignmentData)->report)->reportData->report_title
                                ?? optional($report)->reportData->report_title
                                ?? '-';

                            $statusFromDB = optional($report)->status ?? 'Assigned';
                            $statusMapping = [
                                'Assigned' => 'ยังไม่ประเมิน',
                                'Draft' => 'กำลังดำเนินการ',
                                'Pending' => 'รอผู้ประเมินประเมิน',
                                'Evaluator_draft' => 'ผู้ประเมินเริ่มประเมิน',
                                'Director_assigned' => 'รอกรรมการรับรองผล',
                                'Director_draft' => 'กรรมการเริ่มรับรองผล',
                                'Manager_assign' => 'รอคณบดีรับรองผล',
                                'Manager_draft' => 'คณบดีเริ่มรับรองผล',
                                'Completed' => 'ประเมินเสร็จสิ้น',
                            ];
                            $statusGroupMapping = [
                                'Assigned' => 'ยังไม่ประเมิน',
                                'Draft' => 'กำลังดำเนินการ',
                                'Pending' => 'รอผลการประเมิน',
                                'Evaluator_draft' => 'รอผลการประเมิน',
                                'Director_assigned' => 'รอผลการประเมิน',
                                'Director_draft' => 'รอผลการประเมิน',
                                'Manager_assign' => 'รอผลการประเมิน',
                                'Manager_draft' => 'รอผลการประเมิน',
                                'Completed' => 'ประเมินเสร็จสิ้น',
                            ];
                            $status = $statusMapping[$statusFromDB] ?? $statusFromDB;
                            $statusGroup = $statusGroupMapping[$statusFromDB] ?? $status;
                            $reviewerEntries = collect($assignment->reviewerEntries ?? []);
                            $primaryReviewer = $reviewerEntries->first();
                            $additionalReviewerCount = max($reviewerEntries->count() - 1, 0);

                            $start = optional($assignmentData)->start_time ? Carbon::parse($assignmentData->start_time) : null;
                            $end = optional($assignmentData)->end_time ? Carbon::parse($assignmentData->end_time) : null;

                            $startFormatted = formatThaiDate($start);
                            $endFormatted = formatThaiDate($end);

                            $isRecent = false;
                            if ($end && $end->gt(Carbon::now()->subDays(3))) {
                                $isRecent = true;
                            } elseif (!$end && $start && $start->gt(Carbon::now()->subDays(3))) {
                                $isRecent = true;
                            }
                        @endphp

                        <tr data-evaluation-row data-status-group="{{ $statusGroup }}" class="transition-colors hover:bg-gray-50 {{ $isRecent ? 'bg-blue-50' : '' }}">
                            <td class="border-b p-4 text-gray-500">
                                {{ $index + 1 }}
                                @if($isRecent)
                                    <span class="ml-2 inline-block h-2 w-2 rounded-full bg-blue-500" title="รายการล่าสุด"></span>
                                @endif
                            </td>

                            <td class="border-b p-4">
                                <div class="font-medium text-gray-800">{{ $reportTitle }}</div>
                                @if($isRecent)
                                    <div class="mt-1 text-xs text-blue-600">รายการล่าสุด</div>
                                @endif
                            </td>

                            <td class="border-b p-4 text-gray-500">
                                @if($startFormatted !== '-')
                                    <div class="font-medium">{{ $startFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $startFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="border-b p-4 text-gray-500">
                                @if($endFormatted !== '-')
                                    <div class="font-medium">{{ $endFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $endFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="min-w-[150px] border-b p-4 text-gray-500 align-top">
                                <div class="max-w-[280px]">
                                    @if($primaryReviewer)
                                        <div class="break-words font-medium text-gray-700">
                                          {{ $primaryReviewer['name'] }}
                                        </div>
                                        @if($additionalReviewerCount > 0)
                                            <button
                                                type="button"
                                                class="mt-1 text-xs font-medium text-blue-600 hover:text-blue-800 underline"
                                                onclick='window.openEvaluateeReviewerModal(@json($reviewerEntries->values()))'
                                            >
                                                เพิ่มเติม ({{ $additionalReviewerCount }} คน)
                                            </button>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>

                            <td class="min-w-[180px] border-b px-2 py-4 text-center">
                                @php
                                    $statusClasses = [
                                        'ยังไม่ประเมิน' => 'bg-red-100 text-red-800',
                                        'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800',
                                        'รอผู้ประเมินประเมิน' => 'bg-yellow-100 text-yellow-800',
                                        'ผู้ประเมินเริ่มประเมิน' => 'bg-yellow-100 text-yellow-800',
                                        'รอกรรมการรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'กรรมการเริ่มรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'รอคณบดีรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'คณบดีเริ่มรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800',
                                    ];
                                    $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="border-b p-4 text-center">
                                @php
                                    $actions = [
                                        'ยังไม่ประเมิน' => [
                                            'label' => 'เริ่มประเมิน',
                                            'classes' => 'bg-red-500 hover:bg-red-600 text-white',
                                        ],
                                        'กำลังดำเนินการ' => [
                                            'label' => 'ประเมินต่อ',
                                            'classes' => 'bg-blue-500 hover:bg-blue-600 text-white',
                                        ],
                                        'รอผู้ประเมินประเมิน' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'ผู้ประเมินเริ่มประเมิน' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'รอกรรมการรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'กรรมการเริ่มรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'รอคณบดีรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'คณบดีเริ่มรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'ประเมินเสร็จสิ้น' => [
                                            'label' => 'ดูผล',
                                            'classes' => 'bg-green-500 hover:bg-green-600 text-white',
                                        ],
                                    ];
                                    $action = $actions[$status] ?? null;

                                    $url = route('evaluation.show', ['id' => $report->id ?? 0]);
                                    if ($statusGroup === 'รอผลการประเมิน' || $status === 'ประเมินเสร็จสิ้น') {
                                        $url .= '?readonly=1';
                                    }
                                @endphp

                                @if($action)
                                    <a
                                        href="{{ $url }}"
                                        class="inline-block min-w-[140px] rounded-xl px-4 py-2 text-sm font-medium shadow transition duration-200 {{ $action['classes'] }}">
                                        {{ $action['label'] }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
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

<div id="evaluateeReviewerModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 px-4 py-6">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">รายชื่อผู้ประเมิน</h3>
                    <p class="text-sm text-slate-500">แสดงผู้ประเมินทั้งหมดตามลำดับที่กำหนด</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" onclick="window.closeEvaluateeReviewerModal()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="evaluateeReviewerModalBody" class="space-y-3 px-5 py-5"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const summaryRoot = document.getElementById('evaluationSummary');
        if (!summaryRoot) {
            return;
        }

        const filterButtons = Array.from(summaryRoot.querySelectorAll('.evaluation-status-filter'));
        const clearFilterButton = document.getElementById('evaluationClearFilter');
        const rows = Array.from(summaryRoot.querySelectorAll('[data-evaluation-row]'));
        const emptyState = document.getElementById('evaluationSummaryEmptyState');
        const initialStatus = 'all';

        const clearStatusQuery = () => {
            const url = new URL(window.location.href);
            if (url.searchParams.has('status')) {
                url.searchParams.delete('status');
                window.history.replaceState({}, '', url);
            }
        };

        const setActiveButton = (status) => {
            filterButtons.forEach((button) => {
                const isActive = button.dataset.statusFilter === status;
                button.classList.toggle('ring-2', isActive);
                button.classList.toggle('ring-offset-2', isActive);
                button.classList.toggle('ring-blue-300', isActive);
            });
        };

        const applyFilter = (status = 'all') => {
            let visibleCount = 0;

            rows.forEach((row) => {
                const matches = status === 'all' || row.dataset.statusGroup === status;
                row.classList.toggle('hidden', !matches);
                if (matches) {
                    visibleCount += 1;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleCount > 0);
            }

            setActiveButton(status);
        };

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                clearStatusQuery();
                applyFilter(button.dataset.statusFilter || 'all');
            });
        });

        if (clearFilterButton) {
            clearFilterButton.addEventListener('click', () => {
                clearStatusQuery();
                applyFilter('all');
            });
        }

        window.applyEvaluationStatusFilter = applyFilter;
        clearStatusQuery();
        applyFilter(initialStatus);
    });

    window.closeEvaluateeReviewerModal = window.closeEvaluateeReviewerModal || function () {
        const modal = document.getElementById('evaluateeReviewerModal');
        const body = document.getElementById('evaluateeReviewerModalBody');
        if (modal) modal.classList.add('hidden');
        if (body) body.innerHTML = '';
    };

    window.openEvaluateeReviewerModal = window.openEvaluateeReviewerModal || function (reviewers) {
        const modal = document.getElementById('evaluateeReviewerModal');
        const body = document.getElementById('evaluateeReviewerModalBody');
        if (!modal || !body) return;

        body.innerHTML = (reviewers || []).map((reviewer, index) => `
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ลำดับที่ ${index + 1}</div>
                <div class="mt-1 text-sm font-semibold text-slate-900">${reviewer.label}: ${reviewer.name}</div>
                <div class="mt-1 text-xs text-slate-500">${reviewer.position ?? '-'}</div>
            </div>
        `).join('');

        modal.classList.remove('hidden');
    };

    window.bindEvaluateeReviewerModal = window.bindEvaluateeReviewerModal || function () {
        const modal = document.getElementById('evaluateeReviewerModal');
        if (!modal || modal.dataset.bound === 'true') return;

        modal.dataset.bound = 'true';
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                window.closeEvaluateeReviewerModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                window.closeEvaluateeReviewerModal();
            }
        });
    };

    window.bindEvaluateeReviewerModal();
</script>
