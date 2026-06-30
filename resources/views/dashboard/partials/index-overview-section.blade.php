{{-- ส่วนภาพรวมความคืบหน้าของแดชบอร์ด --}}
<div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 animate-fadeIn">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
    </div>

    <div class="mt-6 grid grid-cols-1 xl:grid-cols-[300px,1fr] gap-6">
        <div class="overview-chart-panel">
            <div class="mb-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">สรุปภาพรวม</div>
                <div class="mt-1 text-sm text-slate-600">ดูสัดส่วนสถานะและความคืบหน้าก่อนอ่านตารางด้านล่าง</div>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-1">
                @foreach ($overviewMiniCards as $card)
                    <div class="overview-mini-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold {{ $card['accent'] }}">{{ $card['title'] }}</div>
                                <div class="mt-2.5 text-[1.8rem] font-extrabold leading-none text-slate-900">{{ $card['value'] }}</div>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $card['badge'] }}">{{ $card['unit'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="overview-panel-card">
                <div class="overview-chart-wrap">
                    <canvas id="{{ $overviewChart['id'] }}"></canvas>
                    <div class="overview-chart-center">
                        <div id="overviewChartCenterValue" class="overview-chart-value">{{ $overviewChart['centerValue'] }}</div>
                        <div id="overviewChartCenterLabel" class="overview-chart-label">{{ $overviewChart['centerLabel'] }}</div>
                        <div id="overviewChartCenterSubLabel" class="overview-chart-sub-label">{{ $overviewChart['centerSubLabel'] }}</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @foreach ($overviewStatusCards as $card)
                <div class="overview-status-card">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-3">
                                <span class="h-3.5 w-3.5 rounded-full" style="background-color: {{ $card['color'] }}"></span>
                                <span class="text-lg font-semibold text-slate-900">{{ $card['text'] }}</span>
                            </div>
                            <div class="mt-4 text-sm text-slate-500">
                                คิดเป็น <span class="font-bold" style="color: {{ $card['color'] }}">{{ $card['percent'] }}%</span> ของผู้เข้ารับการประเมินทั้งหมด {{ $totalEvaluatees }} คน
                            </div>
                        </div>
                        <div class="min-w-[78px] rounded-2xl px-3.5 py-2.5 text-center" style="background-color: {{ $card['color'] }}14; color: {{ $card['color'] }}">
                            <div class="text-[2.1rem] font-extrabold leading-none">{{ $card['count'] }}</div>
                            <div class="mt-1.5 text-sm font-semibold leading-none">คน</div>
                        </div>
                    </div>
                    <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full" style="width: {{ min($card['percent'], 100) }}%; background-color: {{ $card['color'] }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
