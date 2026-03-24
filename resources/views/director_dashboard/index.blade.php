@extends('layouts.app')

@section('content')
<div id="directorDashboardRoot" class="max-w-8xl mx-auto space-y-6">
    <x-profile-card
        :user="$user"
        title="ข้อมูลกรรมการ" />

    <div class="py-4 rounded-xl lg:mx-10 my-4 lg:px-13">
        {{-- <div class="mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">ภาพรวมงานพิจารณาของกรรมการ</h2>
            <p class="text-gray-600">เน้นดูว่างานไหนรอกรรมการพิจารณา งานไหนกำลังดำเนินการ และงานไหนส่งต่อผู้บริหารแล้ว</p>
        </div> --}}

        @php
            $hasDirectorFilters = collect(request()->only(['search', 'start_time', 'end_time', 'department_name', 'status', 'urgency', 'year']))
                ->filter(fn ($value) => filled($value))
                ->isNotEmpty();
        @endphp
        <div class="bg-white rounded-xl shadow-md mb-8 border border-gray-200 overflow-hidden">
            <button type="button" id="directorFilterToggle" class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-7 7v5l-4 2v-7L3 6V4z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 leading-tight">กรองข้อมูลการประเมิน</h2>
                        <p class="mt-0.5 text-xs text-gray-500 sm:text-sm">{{ $hasDirectorFilters ? 'มีตัวกรองที่กำลังใช้งานอยู่ กดเพื่อแก้ไขหรือรีเซ็ต' : 'กดเพื่อแสดงตัวเลือกการกรองเพิ่มเติม' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if ($hasDirectorFilters)
                        <span class="hidden sm:inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">กำลังกรอง</span>
                    @endif
                    <svg id="directorFilterChevron" class="h-4.5 w-4.5 text-gray-500 transition-transform duration-200 {{ $hasDirectorFilters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>
            <div id="directorFilterPanel" class="{{ $hasDirectorFilters ? '' : 'hidden' }} border-t border-gray-100 px-5 pb-5 pt-2">
                <form id="filterForm" method="get" class="space-y-1">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <div class="md:col-span-2 xl:col-span-1">
                            <label class="block mb-1 text-gray-700 font-medium text-sm">ค้นหาชื่อ / รหัสพนักงาน / ชื่องาน</label>
                            <input name="search" type="text" value="{{ request('search', '') }}" placeholder="พิมพ์เพื่อค้นหา" class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full" />
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่เริ่มต้น</label>
                            <input name="start_time" type="date" value="{{ request('start_time', '') }}" class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full" />
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่สิ้นสุด</label>
                            <input name="end_time" type="date" value="{{ request('end_time', '') }}" class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full" />
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">หน่วยงาน / แผนก</label>
                            <select name="department_name" class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                                <option value="">ทุกหน่วยงาน</option>
                                @foreach ($departments ?? [] as $dept)
                                    <option value="{{ $dept->department_name }}" {{ request('department_name') == $dept->department_name ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">สถานะ</label>
                            <select name="status" class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                                <option value="">ทั้งหมด</option>
                                <option value="director_waiting" {{ request('status') === 'director_waiting' ? 'selected' : '' }}>รอกรรมการพิจารณา</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>กำลังพิจารณา</option>
                                <option value="forwarded" {{ request('status') === 'forwarded' ? 'selected' : '' }}>ส่งต่อผู้บริหารแล้ว</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">ความเร่งด่วน</label>
                            <select name="urgency" class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                                <option value="">ทั้งหมด</option>
                                <option value="due_soon" {{ request('urgency') === 'due_soon' ? 'selected' : '' }}>ใกล้ครบกำหนด</option>
                                <option value="overdue" {{ request('urgency') === 'overdue' ? 'selected' : '' }}>เลยกำหนด</option>
                                <option value="normal" {{ request('urgency') === 'normal' ? 'selected' : '' }}>ยังไม่เร่งด่วน</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">ปีประเมิน</label>
                            <select name="year" class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                                <option value="">ทั้งหมด</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ (string) request('year') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:justify-end lg:justify-end gap-2 pt-3">
                        <button type="button" onclick="resetFilters()" class="px-5 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition">ล้างค่า</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">กรองข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>

        @php
            $activeDirectorFilters = collect(request()->only(['search', 'start_time', 'end_time', 'department_name', 'status', 'urgency', 'year']))
                ->filter(fn ($value) => filled($value));
            $directorOverviewChart = [
                'id' => 'directorOverviewChart',
                'labels' => ['รอการกรอกข้อมูล', 'ยังไม่ประเมิน', 'กำลังดำเนินการ', 'รอผลการประเมิน', 'ประเมินเสร็จสิ้น'],
                'data' => [
                    $statusCounts['รอการกรอกข้อมูล'] ?? 0,
                    $awaitingDirectorCount,
                    $directorInProgressCount,
                    $forwardedToManagerCount,
                    $completedCount,
                ],
                'colors' => ['#f97316', '#ef4444', '#3b82f6', '#f59e0b', '#22c55e'],
                'filters' => [
                    request()->fullUrlWithQuery(['status' => 'รอการกรอกข้อมูล']),
                    request()->fullUrlWithQuery(['status' => 'ยังไม่ประเมิน']),
                    request()->fullUrlWithQuery(['status' => 'กำลังดำเนินการ']),
                    request()->fullUrlWithQuery(['status' => 'รอผลการประเมิน']),
                    request()->fullUrlWithQuery(['status' => 'ประเมินเสร็จสิ้น']),
                ],
                'centerValue' => $progressPercent.'%',
                'centerLabel' => 'ความคืบหน้ารวม',
                'centerMeta' => "ปิดงานแล้ว {$completedCount} จาก {$totalEvaluations} รายการ",
            ];
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 rounded-2xl bg-white p-6 shadow-md border border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">ภาพรวมการพิจารณา</h3>
                        <p class="mt-1 text-sm text-gray-500">ใช้ติดตามคิวงานที่ต้องเข้าพิจารณาและงานที่รอส่งต่อให้ผู้บริหาร</p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-gray-100 bg-slate-50/60 p-5 lg:p-6">
                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[300px_minmax(0,1fr)] xl:items-center">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-1">
                                <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white px-4 py-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-base font-bold leading-tight text-blue-600">งานทั้งหมด</div>
                                            <div class="mt-3 text-2xl font-extrabold leading-none text-slate-900">{{ $totalEvaluations }}</div>
                                        </div>
                                        <div class="shrink-0 rounded-full bg-blue-100 px-3 py-1.5 text-sm font-bold text-blue-700">รายการ</div>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white px-4 py-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-base font-bold leading-tight text-emerald-600">ผู้เข้าประเมิน</div>
                                            <div class="mt-3 text-2xl font-extrabold leading-none text-slate-900">{{ $totalEvaluatees }}</div>
                                        </div>
                                        <div class="shrink-0 rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-bold text-emerald-700">คน</div>
                                    </div>
                                </div>
                            </div>

                            <div class="overview-chart-wrap mx-auto xl:mx-0">
                                <canvas id="{{ $directorOverviewChart['id'] }}"></canvas>
                                <div class="overview-chart-center">
                                    <div id="{{ $directorOverviewChart['id'] }}Value" class="overview-chart-value">{{ $directorOverviewChart['centerValue'] }}</div>
                                    <div id="{{ $directorOverviewChart['id'] }}Label" class="overview-chart-label">{{ $directorOverviewChart['centerLabel'] }}</div>
                                    <div id="{{ $directorOverviewChart['id'] }}Meta" class="overview-chart-meta">{{ $directorOverviewChart['centerMeta'] }}</div>
                                </div>
                            </div>

                            @if ($activeDirectorFilters->isNotEmpty())
                                <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-start">
                                    @if (request('status'))
                                        <div class="rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 ring-1 ring-orange-100">
                                            กรองอยู่: {{ request('status') }}
                                        </div>
                                    @endif
                                    <button type="button" onclick="resetFilters()" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-gray-200 transition hover:bg-gray-50">
                                        ล้างการกรอง
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2.5">
                            @foreach ($directorOverviewChart['labels'] as $index => $label)
                                @php
                                    $count = $directorOverviewChart['data'][$index];
                                    $percent = $totalEvaluations > 0 ? round(($count / $totalEvaluations) * 100, 1) : 0;
                                    $cardColor = $directorOverviewChart['colors'][$index];
                                @endphp
                                <div class="rounded-xl border px-4 py-3.5 shadow-sm" style="border-color: {{ $cardColor }}22; background: linear-gradient(135deg, {{ $cardColor }}12 0%, #ffffff 55%); box-shadow: inset 4px 0 0 {{ $cardColor }};">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $cardColor }}"></span>
                                                <div class="truncate text-[15px] font-semibold text-gray-900">{{ $label }}</div>
                                            </div>
                                        </div>
                                        <div class="shrink-0 rounded-2xl px-3 py-2 text-center" style="min-width: 86px; background-color: {{ $cardColor }}14;">
                                            <div class="text-2xl font-extrabold leading-none" style="color: {{ $cardColor }}">{{ $count }}</div>
                                            <div class="mt-1 text-xs font-semibold" style="color: {{ $cardColor }}">รายการ</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 text-sm">
                                        <div class="text-gray-600">
                                            คิดเป็น <span class="font-semibold" style="color: {{ $cardColor }}">{{ $percent }}%</span> ของงานทั้งหมด {{ $totalEvaluations }} รายการ
                                        </div>
                                    </div>

                                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-white/80">
                                        <div class="h-full rounded-full" style="width: {{ min($percent, 100) }}%; background-color: {{ $cardColor }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-md border border-gray-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">รายการที่ควรติดตาม</h3>
                        <p class="mt-1 text-sm text-gray-500">แสดงงานที่รอกรรมการพิจารณาก่อน และเรียงตามกำหนดเวลา</p>
                    </div>
                    <a href="#evaluation-table" class="text-sm font-medium text-blue-600 hover:text-blue-700">ดูทั้งหมด</a>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse($followUpEvaluations->take(5) as $item)
                        <div class="rounded-2xl border border-gray-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">{{ $item['evaluatee_name'] }}</p>
                                    <p class="text-sm text-gray-500">สถานะ: {{ $item['pretty_status'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">{{ $item['progress_percent'] }}%</p>
                                </div>
                            </div>
                            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ min($item['progress_percent'], 100) }}%;"></div>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <span>ครบกำหนด {{ $item['due_date'] }}</span>
                                <span>{{ $item['remaining_text'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-500">
                            ไม่มีรายการที่ต้องติดตาม
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div id="evaluation-table">
        <x-director-table
            :evaluations="$evaluations"
            :statusCounts="$statusCounts"
            :years="$years"
         />
    </div>
</div>

@if(session('success'))
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif

<style>
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }

    .max-w-4xl {
        max-width: 100%;
        padding: 0 1rem;
    }
}

.overview-chart-wrap {
    position: relative;
    width: min(100%, 280px);
    aspect-ratio: 1 / 1;
}

.overview-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

.overview-chart-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    text-align: center;
    padding: 0 2rem;
}

.overview-chart-value {
    color: #0f172a;
    font-size: 2.1rem;
    font-weight: 800;
    line-height: 1;
}

.overview-chart-label {
    margin-top: 0.5rem;
    color: #64748b;
    font-size: 0.95rem;
    font-weight: 700;
}

.overview-chart-meta {
    margin-top: 0.35rem;
    color: #94a3b8;
    font-size: 0.75rem;
    line-height: 1.4;
}
</style>
@endsection

@push('scripts')
    <script>
        function initDirectorOverviewChart(root = document) {
            const overviewChartCanvas = root.getElementById(@json($directorOverviewChart['id']));
            if (overviewChartCanvas && typeof Chart !== 'undefined') {
                const overviewChartValue = root.getElementById(@json($directorOverviewChart['id'].'Value'));
                const overviewChartLabel = root.getElementById(@json($directorOverviewChart['id'].'Label'));
                const overviewChartMeta = root.getElementById(@json($directorOverviewChart['id'].'Meta'));
                const overviewChartDefaults = {
                    value: @json($directorOverviewChart['centerValue']),
                    label: @json($directorOverviewChart['centerLabel']),
                    meta: @json($directorOverviewChart['centerMeta']),
                };
                const overviewChartLabels = @json($directorOverviewChart['labels']);
                const overviewChartData = @json($directorOverviewChart['data']);
                const overviewChartFilters = @json($directorOverviewChart['filters']);

                const setOverviewCenter = function(value, label, meta) {
                    overviewChartValue.textContent = value;
                    overviewChartLabel.textContent = label;
                    overviewChartMeta.textContent = meta;
                };

                new Chart(overviewChartCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: overviewChartLabels,
                        datasets: [{
                            data: overviewChartData,
                            backgroundColor: @json($directorOverviewChart['colors']),
                            borderColor: '#ffffff',
                            borderWidth: 4,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        onHover: function(event, activeElements) {
                            const target = event?.native?.target;
                            if (target) {
                                target.style.cursor = activeElements.length ? 'pointer' : 'default';
                            }
                            if (!activeElements.length) {
                                setOverviewCenter(overviewChartDefaults.value, overviewChartDefaults.label, overviewChartDefaults.meta);
                                return;
                            }
                            const index = activeElements[0].index;
                            const count = overviewChartData[index] || 0;
                            const total = overviewChartData.reduce((sum, item) => sum + item, 0);
                            const percent = total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';
                            setOverviewCenter(percent + '%', overviewChartLabels[index], 'จำนวน ' + count + ' รายการ');
                        },
                        onClick: function(event, activeElements) {
                            if (!activeElements.length) {
                                return;
                            }
                            const targetUrl = overviewChartFilters[activeElements[0].index];
                            if (targetUrl) {
                                applyDirectorDashboardRequest(targetUrl);
                            }
                        }
                    }
                });
            }
        }

        function applyDirectorDashboardRequest(url, pushState = true) {
            const root = document.getElementById('directorDashboardRoot');
            if (!root) {
                return Promise.resolve();
            }
            root.classList.add('opacity-70', 'pointer-events-none');
            return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(function(html) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const incomingRoot = doc.getElementById('directorDashboardRoot');
                    if (!incomingRoot) {
                        window.location.href = url;
                        return;
                    }
                    root.innerHTML = incomingRoot.innerHTML;
                    root.className = incomingRoot.className;
                    if (pushState) {
                        window.history.pushState({}, '', url);
                    }
                    initDirectorOverviewChart(document);
                    initDirectorDashboardAjax();
                    initDirectorFilterToggle(document);
                })
                .catch(function() {
                    window.location.href = url;
                })
                .finally(function() {
                    const currentRoot = document.getElementById('directorDashboardRoot');
                    if (currentRoot) {
                        currentRoot.classList.remove('opacity-70', 'pointer-events-none');
                    }
                });
        }

        function resetFilters() {
            const form = document.getElementById('filterForm');
            if (!form) {
                return;
            }
            form.querySelector('input[name="search"]').value = '';
            form.querySelector('input[name="start_time"]').value = '';
            form.querySelector('input[name="end_time"]').value = '';
            form.querySelector('select[name="department_name"]').value = '';
            form.querySelector('select[name="status"]').value = '';
            form.querySelector('select[name="urgency"]').value = '';
            form.querySelector('select[name="year"]').value = '';
            applyDirectorDashboardRequest(window.location.pathname);
        }

        function initDirectorFilterToggle(scope = document) {
            const toggle = scope.getElementById ? scope.getElementById('directorFilterToggle') : scope.querySelector('#directorFilterToggle');
            const panel = scope.getElementById ? scope.getElementById('directorFilterPanel') : scope.querySelector('#directorFilterPanel');
            const chevron = scope.getElementById ? scope.getElementById('directorFilterChevron') : scope.querySelector('#directorFilterChevron');
            if (!toggle || !panel || !chevron || toggle.dataset.bound === 'true') {
                return;
            }
            toggle.dataset.bound = 'true';
            toggle.addEventListener('click', function() {
                panel.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
            });
        }

        function initDirectorDashboardAjax() {
            const root = document.getElementById('directorDashboardRoot');
            if (!root || root.dataset.ajaxReady === '1') {
                return;
            }
            root.dataset.ajaxReady = '1';

            root.addEventListener('submit', function(event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || (form.method || '').toUpperCase() !== 'GET') {
                    return;
                }
                event.preventDefault();
                const action = form.getAttribute('action') || window.location.pathname;
                const targetUrl = new URL(action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                params.forEach(function(value, key) {
                    if (value !== '') {
                        targetUrl.searchParams.append(key, value);
                    }
                });
                applyDirectorDashboardRequest(targetUrl.toString());
            });

            root.addEventListener('click', function(event) {
                const link = event.target.closest('[data-director-ajax-link]');
                if (!link) {
                    return;
                }
                event.preventDefault();
                applyDirectorDashboardRequest(link.href);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initDirectorOverviewChart(document);
            initDirectorDashboardAjax();
            initDirectorFilterToggle(document);

            const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
            messages.forEach(function(message) {
                setTimeout(function() {
                    if (message.parentElement) {
                        message.style.transform = 'translateX(100%)';
                        setTimeout(function() {
                            if (message.parentElement) {
                                message.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            });

            window.addEventListener('popstate', function() {
                applyDirectorDashboardRequest(window.location.href, false);
            });
        });
    </script>
@endpush
