@extends('layouts.app')

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
    <div class="max-w-8xl mx-auto space-y-6">
        <x-profile-card :user="$user" title="ข้อมูลผู้รับการประเมิน" />

        <div class="mx-5 rounded-2xl border px-10 pb-6 pt-6 shadow-md"
            style="background: linear-gradient(135deg, #f5f3ff 0%, #fff 50%, #fdf2f8 100%); border-color: #ede9fe;">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-2xl font-extrabold tracking-wide text-purple-700">
                    <i class="fas fa-bell text-fuchsia-500"></i>
                    การประเมินที่ยังไม่เสร็จ
                </h3>
            </div>

            <div class="grid grid-cols-1 gap-6">
                @forelse($unfinishedAssignments as $assignment)
                    <x-evaluation-header :title="$assignment['title']" :period="$assignment['period']" :deadline="$assignment['deadline']" :daysLeft="$assignment['daysLeft']"
                        :evaluationId="$assignment['id']" />
                @empty
                    <div class="col-span-full rounded-xl border border-gray-100 bg-white py-10 text-center shadow-inner">
                        <p class="text-lg text-gray-500">ไม่มีการประเมินที่ค้างอยู่</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- <div class="grid grid-cols-1 gap-6 px-10 md:grid-cols-2 xl:grid-cols-4">
        <x-summary-score
            title="งานประเมินทั้งหมด"
            :value="$totalAssignments"
            subtitle="จำนวนรายการประเมินทั้งหมดของคุณ"
            color="blue"
            icon="fas fa-clipboard-list"
            iconSize="text-3xl"
        />

        <x-summary-score
            title="งานที่ต้องทำตอนนี้"
            :value="$actionRequiredAssignments"
            subtitle="รายการที่ยังไม่เริ่มหรือกำลังกรอกอยู่"
            color="red"
            icon="fas fa-bolt"
            iconSize="text-3xl"
        />

        <x-summary-score
            title="ใกล้ครบกำหนด"
            :value="$dueSoonAssignments"
            subtitle="รายการที่ครบกำหนดภายใน 3 วัน"
            color="yellow"
            icon="fas fa-hourglass-half"
            iconSize="text-3xl"
        />

        <x-summary-score
            title="ประเมินเสร็จแล้ว"
            :value="$completedAssignments"
            subtitle="รายการที่ดำเนินการเสร็จสมบูรณ์แล้ว"
            color="green"
            icon="fas fa-check-circle"
            iconSize="text-3xl"
        />
    </div> --}}

        <div class="px-10">
            @php
                $statusTotal = max(
                    $notStartedAssignments + $inProgressAssignments + $inReviewAssignments + $completedAssignments,
                    1,
                );
                $notStartedPercent = round(($notStartedAssignments / $statusTotal) * 100, 1);
                $inProgressPercent = round(($inProgressAssignments / $statusTotal) * 100, 1);
                $actionPercent = round(($actionRequiredAssignments / $statusTotal) * 100, 1);
                $reviewPercent = round(($inReviewAssignments / $statusTotal) * 100, 1);
                $completedPercent = round(($completedAssignments / $statusTotal) * 100, 1);
                $dueSoonList = collect($unfinishedAssignments)
                    ->filter(fn($assignment) => $assignment['daysLeft'] !== null && $assignment['daysLeft'] <= 3)
                    ->take(5)
                    ->values();
                $overdueList = collect($overdueAssignments)->take(5)->values();
                $statusChart = [
                    'id' => 'evaluateeStatusChart',
                    'labels' => ['ยังไม่ประเมิน', 'กำลังดำเนินการ', 'รอผลการประเมิน', 'ประเมินเสร็จสิ้น'],
                    'data' => [
                        $notStartedAssignments,
                        $inProgressAssignments,
                        $inReviewAssignments,
                        $completedAssignments,
                    ],
                    'colors' => ['#ef4444', '#3b82f6', '#eab308', '#22c55e'],
                    'filters' => ['ยังไม่ประเมิน', 'กำลังดำเนินการ', 'รอผลการประเมิน', 'ประเมินเสร็จสิ้น'],
                    'centerValue' => $totalAssignments,
                    'centerLabel' => 'งานประเมินทั้งหมด',
                ];
            @endphp

            <div class="rounded-2xl border bg-white p-6 shadow-md">
                <h3 class="text-xl font-bold text-gray-900">สถานะของฉัน</h3>

                {{-- <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-rose-100 bg-rose-50/70 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-rose-600">ยังไม่เริ่ม / กำลังกรอก</div>
                            <div class="mt-3 text-4xl font-extrabold leading-none text-rose-700">{{ $actionRequiredAssignments }}</div>
                        </div>
                        <div class="rounded-full bg-white px-3 py-1 text-sm font-bold text-rose-500 shadow-sm">{{ $actionPercent }}%</div>
                    </div>
                    <div class="mt-4 text-xs leading-5 text-rose-500">งานที่ยังต้องลงมือทำหรืออยู่ระหว่างกรอกข้อมูล</div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-amber-600">รอการพิจารณา</div>
                            <div class="mt-3 text-4xl font-extrabold leading-none text-amber-700">{{ $inReviewAssignments }}</div>
                        </div>
                        <div class="rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-500 shadow-sm">{{ $reviewPercent }}%</div>
                    </div>
                    <div class="mt-4 text-xs leading-5 text-amber-600">งานที่ส่งแล้วและกำลังรอขั้นตอนถัดไป</div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-emerald-600">เสร็จสิ้นแล้ว</div>
                            <div class="mt-3 text-4xl font-extrabold leading-none text-emerald-700">{{ $completedAssignments }}</div>
                        </div>
                        <div class="rounded-full bg-white px-3 py-1 text-sm font-bold text-emerald-500 shadow-sm">{{ $completedPercent }}%</div>
                    </div>
                    <div class="mt-4 text-xs leading-5 text-emerald-600">งานที่ประเมินเสร็จสมบูรณ์เรียบร้อยแล้ว</div>
                </div>
            </div> --}}

                <section class="mt-5 grid grid-cols-1 items-stretch gap-6 xl:grid-cols-[minmax(0,1fr),340px,340px]">
                    <div class="h-full rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h4 class="text-base font-bold text-slate-800">ภาพรวมสถานะงาน</h4>
                                <p class="text-sm text-slate-500">ดูจำนวนงานในแต่ละสถานะทั้งหมด</p>

                            </div>
                            <div class="hidden flex flex-wrap items-center gap-2">

                                <button type="button" id="evaluationClearFilterTop"
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                    ล้างการกรอง
                                </button>
                            </div>
                        </div>

                        <div
                            class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[280px,minmax(0,360px)] lg:items-start lg:justify-between">
                            <div>
                                <div class="relative mx-auto h-[260px] w-full max-w-[260px]">
                                    <canvas id="{{ $statusChart['id'] }}"></canvas>
                                    <div
                                        class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <div id="statusChartCenterValue"
                                            class="text-4xl font-extrabold leading-none text-slate-900">
                                            {{ $statusChart['centerValue'] }}</div>
                                        <div id="statusChartCenterLabel" class="mt-2 text-sm font-medium text-slate-500">
                                            {{ $statusChart['centerLabel'] }}</div>
                                    </div>
                                </div>

                                <div class="mt-4 text-center">
                                    <button type="button" id="evaluationClearFilter"
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                        ล้างการกรอง
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3 lg:w-[360px]">
                                <div class="rounded-xl bg-rose-50 px-4 py-3 shadow-sm ring-1 ring-rose-200">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                            <span class="text-sm font-semibold text-rose-800">ยังไม่ประเมิน</span>
                                        </div>
                                        <div class="text-right leading-tight">
                                            <div class="text-sm font-semibold text-rose-500">{{ $notStartedPercent }}%</div>
                                            <div class="text-[11px] font-medium text-rose-400">ของงานทั้งหมด</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-2xl font-extrabold leading-none text-rose-600">
                                        {{ $notStartedAssignments }}</div>
                                </div>

                                <div class="rounded-xl bg-blue-50 px-4 py-3 shadow-sm ring-1 ring-blue-200">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                            <span class="text-sm font-semibold text-blue-800">กำลังดำเนินการ</span>
                                        </div>
                                        <div class="text-right leading-tight">
                                            <div class="text-sm font-semibold text-blue-500">{{ $inProgressPercent }}%</div>
                                            <div class="text-[11px] font-medium text-blue-400">ของงานทั้งหมด</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-2xl font-extrabold leading-none text-blue-600">
                                        {{ $inProgressAssignments }}</div>
                                </div>

                                <div class="rounded-xl bg-amber-50 px-4 py-3 shadow-sm ring-1 ring-amber-200">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                                            <span class="text-sm font-semibold text-amber-800">รอผลการประเมิน</span>
                                        </div>
                                        <div class="text-right leading-tight">
                                            <div class="text-sm font-semibold text-amber-500">{{ $reviewPercent }}%</div>
                                            <div class="text-[11px] font-medium text-amber-400">ของงานทั้งหมด</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-2xl font-extrabold leading-none text-amber-600">
                                        {{ $inReviewAssignments }}</div>
                                </div>

                                <div class="rounded-xl bg-emerald-50 px-4 py-3 shadow-sm ring-1 ring-emerald-200">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                            <span class="text-sm font-semibold text-emerald-800">ประเมินเสร็จสิ้น</span>
                                        </div>
                                        <div class="text-right leading-tight">
                                            <div class="text-sm font-semibold text-emerald-500">{{ $completedPercent }}%</div>
                                            <div class="text-[11px] font-medium text-emerald-400">ของงานทั้งหมด</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-2xl font-extrabold leading-none text-emerald-600">
                                        {{ $completedAssignments }}</div>
                                </div>

                                <div class="hidden pt-1 text-right">
                                    <button type="button" id="evaluationClearFilterBottom"
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                        ล้างการกรอง
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex h-full flex-col rounded-2xl border border-amber-200 bg-amber-50/70 p-6 shadow-sm">

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-amber-900">ใกล้ครบกำหนด</h4>
                                <p class="mt-1 text-sm text-amber-700">รายการที่ครบกำหนดภายใน 3 วัน</p>
                            </div>
                            <div
                                class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-semibold text-amber-700 shadow-sm ring-1 ring-amber-200">
                                {{ $dueSoonAssignments }} รายการ
                            </div>
                        </div>

                        <div class="mt-6">
                            @forelse($dueSoonList as $assignment)
                                <div class="w-full border-b border-amber-100 py-4 last:border-b-0">
                                    <div class="line-clamp-2 text-sm font-semibold leading-6 text-slate-800">
                                        {{ $assignment['title'] }}</div>
                                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                                        <span>ครบกำหนด {{ $assignment['deadline'] }}</span>
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 font-semibold text-amber-700">
                                            {{ $assignment['daysLeft'] === 0 ? 'วันนี้' : 'อีก ' . $assignment['daysLeft'] . ' วัน' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="w-full py-8 text-center text-sm text-slate-500">
                                    ไม่มีรายการที่ใกล้ครบกำหนด
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex h-full flex-col rounded-2xl border border-rose-200 bg-rose-50/70 p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-rose-900">เลยกำหนดการส่ง</h4>
                                <p class="mt-1 text-sm text-rose-700">รายการที่เลยวันครบกำหนดแล้ว</p>
                            </div>
                            <div
                                class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-semibold text-rose-700 shadow-sm ring-1 ring-rose-200">
                                {{ $overdueCount }} รายการ
                            </div>
                        </div>

                        <div class="mt-6">
                            @forelse($overdueList as $assignment)
                                <div class="w-full border-b border-rose-100 py-4 last:border-b-0">
                                    <div class="line-clamp-2 text-sm font-semibold leading-6 text-slate-800">
                                        {{ $assignment['title'] }}</div>
                                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                                        <span>ครบกำหนด {{ $assignment['deadline'] }}</span>
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 font-semibold text-rose-700">
                                            {{ 'เลย ' . abs($assignment['daysLeft']) . ' วัน' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="w-full py-8 text-center text-sm text-slate-500">
                                    ไม่มีรายการที่เลยกำหนด
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <div class="hidden mt-6 space-y-6">
                    <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800">แบบที่ 1: แถบสัดส่วน</h4>
                                <p class="text-sm text-slate-500">เห็นภาพรวมจำนวนงานแต่ละสถานะในแถบเดียว</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-slate-600 shadow-sm">
                                ทั้งหมด {{ $actionRequiredAssignments + $inReviewAssignments + $completedAssignments }}
                                รายการ
                            </span>
                        </div>

                        <div class="overflow-hidden rounded-full bg-white shadow-inner">
                            <div class="flex h-4 w-full">
                                <div class="bg-red-500" style="width: {{ $actionPercent }}%"></div>
                                <div class="bg-amber-400" style="width: {{ $reviewPercent }}%"></div>
                                <div class="bg-emerald-500" style="width: {{ $completedPercent }}%"></div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="rounded-xl bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-red-500">ยังไม่เริ่ม / กำลังกรอก</span>
                                    <span class="text-xs font-semibold text-slate-400">{{ $actionPercent }}%</span>
                                </div>
                                <div class="mt-2 text-2xl font-extrabold text-red-600">{{ $actionRequiredAssignments }}
                                </div>
                            </div>
                            <div class="rounded-xl bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-amber-500">รอการพิจารณา</span>
                                    <span class="text-xs font-semibold text-slate-400">{{ $reviewPercent }}%</span>
                                </div>
                                <div class="mt-2 text-2xl font-extrabold text-amber-600">{{ $inReviewAssignments }}</div>
                            </div>
                            <div class="rounded-xl bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-emerald-500">เสร็จสิ้นแล้ว</span>
                                    <span class="text-xs font-semibold text-slate-400">{{ $completedPercent }}%</span>
                                </div>
                                <div class="mt-2 text-2xl font-extrabold text-emerald-600">{{ $completedAssignments }}
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <x-evaluation-summary :evaluations="$evaluations" :status-counts="$statusCounts" :years="$years" />
    </div>

    @if (session('success'))
        <div id="successMessage"
            class="fixed right-4 top-4 z-[10000] transform rounded-lg bg-green-500 px-6 py-4 text-white shadow-lg transition-transform duration-300">
            <div class="flex items-center space-x-3">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <style>
        @media (max-width: 768px) {
            .space-y-6>*+* {
                margin-top: 1rem;
            }

            .max-w-4xl {
                max-width: 100%;
                padding: 0 1rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusChartConfig = @json($statusChart);
            const statusChartCanvas = document.getElementById(statusChartConfig.id);
            const statusChartCenterValue = document.getElementById('statusChartCenterValue');
            const statusChartCenterLabel = document.getElementById('statusChartCenterLabel');

            const resetStatusChartCenter = () => {
                if (statusChartCenterValue) {
                    statusChartCenterValue.textContent = statusChartConfig.centerValue;
                }
                if (statusChartCenterLabel) {
                    statusChartCenterLabel.textContent = statusChartConfig.centerLabel;
                }
            };

            const updateStatusChartCenter = (chart, element) => {
                if (!statusChartCenterValue || !statusChartCenterLabel || !element) {
                    return;
                }

                const index = element.index;
                const value = chart.data.datasets[0].data[index];
                const total = chart.data.datasets[0].data.reduce((sum, item) => sum + item, 0);
                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                statusChartCenterValue.textContent = `${percent}%`;
                statusChartCenterLabel.textContent = `${chart.data.labels[index]} ${value} รายการ`;
            };

            if (statusChartCanvas) {
                new Chart(statusChartCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: statusChartConfig.labels,
                        datasets: [{
                            data: statusChartConfig.data,
                            filters: statusChartConfig.filters,
                            backgroundColor: statusChartConfig.colors,
                            hoverBackgroundColor: statusChartConfig.colors,
                            hoverOffset: 8,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            spacing: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 900
                        },
                        onClick(event, elements, chart) {
                            if (!elements.length) {
                                return;
                            }

                            const index = elements[0].index;
                            const targetFilter = chart.data.datasets[0].filters?.[index];
                            if (targetFilter && typeof window.applyEvaluationStatusFilter === 'function') {
                                window.applyEvaluationStatusFilter(targetFilter);
                            }
                        },
                        onHover(event, elements, chart) {
                            chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                            if (elements.length) {
                                updateStatusChartCenter(chart, elements[0]);
                            } else {
                                resetStatusChartCenter();
                            }
                        }
                    }
                });
            }

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
        });
    </script>
@endsection
