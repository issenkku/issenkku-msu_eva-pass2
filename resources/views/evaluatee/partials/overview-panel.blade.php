<div class="rounded-2xl border bg-white p-6 shadow-md">
    <h3 class="text-xl font-bold text-gray-900">สถานะของฉัน</h3>

    @php
        $overviewCardTones = [
            'rose' => ['bg' => 'bg-rose-50', 'ring' => 'ring-rose-200', 'dot' => 'bg-rose-500', 'text' => 'text-rose-800', 'value' => 'text-rose-600', 'percent' => 'text-rose-500', 'meta' => 'text-rose-400'],
            'blue' => ['bg' => 'bg-blue-50', 'ring' => 'ring-blue-200', 'dot' => 'bg-blue-500', 'text' => 'text-blue-800', 'value' => 'text-blue-600', 'percent' => 'text-blue-500', 'meta' => 'text-blue-400'],
            'amber' => ['bg' => 'bg-amber-50', 'ring' => 'ring-amber-200', 'dot' => 'bg-amber-400', 'text' => 'text-amber-800', 'value' => 'text-amber-600', 'percent' => 'text-amber-500', 'meta' => 'text-amber-400'],
            'emerald' => ['bg' => 'bg-emerald-50', 'ring' => 'ring-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-800', 'value' => 'text-emerald-600', 'percent' => 'text-emerald-500', 'meta' => 'text-emerald-400'],
        ];
    @endphp

    <section class="mt-5 grid grid-cols-1 items-start gap-6 2xl:grid-cols-[minmax(0,1fr),340px,340px]">
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h4 class="text-base font-bold text-slate-800">ภาพรวมสถานะงาน</h4>
                    <p class="text-sm text-slate-500">ดูจำนวนงานในแต่ละสถานะทั้งหมด</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 2xl:grid-cols-[260px,minmax(260px,1fr)] 2xl:items-start">
                <div class="min-w-0">
                    <div class="relative mx-auto h-[260px] w-full max-w-[260px]">
                        <canvas id="{{ $evaluateeOverview['statusChart']['id'] }}"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                            <div id="statusChartCenterValue" class="text-4xl font-extrabold leading-none text-slate-900">
                                {{ $evaluateeOverview['statusChart']['centerValue'] }}
                            </div>
                            <div id="statusChartCenterLabel" class="mt-2 text-sm font-medium text-slate-500">
                                {{ $evaluateeOverview['statusChart']['centerLabel'] }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="button" id="evaluationClearFilter" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                            ล้างการกรอง
                        </button>
                    </div>
                </div>

                <div class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 2xl:grid-cols-1">
                    @foreach ($evaluateeOverview['overviewCards'] as $card)
                        @php
                            $tone = $overviewCardTones[$card['tone']] ?? $overviewCardTones['blue'];
                        @endphp
                        <button
                            type="button"
                            class="evaluation-overview-card min-w-0 rounded-xl px-4 py-3 shadow-sm ring-1 {{ $tone['bg'] }} {{ $tone['ring'] }}"
                            data-status-filter="{{ $card['filter'] }}"
                            aria-pressed="false">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $tone['dot'] }}"></span>
                                    <span class="min-w-0 text-sm font-semibold {{ $tone['text'] }}">{{ $card['title'] }}</span>
                                </div>
                                <div class="shrink-0 text-right leading-tight">
                                    <div class="text-sm font-semibold {{ $tone['percent'] }}">{{ $card['percent'] }}%</div>
                                    <div class="text-[11px] font-medium {{ $tone['meta'] }}">ของงานทั้งหมด</div>
                                </div>
                            </div>
                            <div class="mt-2 text-2xl font-extrabold leading-none {{ $tone['value'] }}">{{ $card['count'] }}</div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="min-w-0 flex flex-col rounded-2xl border border-amber-200 bg-amber-50/70 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h4 class="text-base font-bold text-amber-900">ใกล้ครบกำหนด</h4>
                    <p class="mt-1 text-sm text-amber-700">รายการที่ครบกำหนดภายในไม่กี่วัน</p>
                </div>
                <div class="inline-flex h-12 min-w-[88px] shrink-0 items-center justify-center rounded-full bg-white px-4 text-center text-sm font-semibold leading-none text-amber-700 shadow-sm ring-1 ring-amber-200">
                    {{ $evaluateeOverview['dueSoonCount'] }} รายการ
                </div>
            </div>

            <div class="mt-6">
                @forelse($evaluateeOverview['dueSoonList'] as $assignment)
                    <div class="w-full border-b border-amber-100 py-4 last:border-b-0">
                        <div class="line-clamp-2 text-sm font-semibold leading-6 text-slate-800">{{ $assignment['title'] }}</div>
                        <div class="mt-3 flex flex-col gap-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                            <span>ครบกำหนด {{ $assignment['deadline'] }}</span>
                            <a href="{{ route('evaluation.show', $assignment['id']) }}" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold shadow transition sm:w-auto sm:min-w-[116px] {{ $assignment['action']['classes'] }}">
                                {{ $assignment['action']['label'] }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="w-full py-8 text-center text-sm text-slate-500">
                        ไม่มีรายการที่ใกล้ครบกำหนด
                    </div>
                @endforelse
            </div>
        </div>

        <div class="min-w-0 flex flex-col rounded-2xl border border-rose-200 bg-rose-50/70 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h4 class="text-base font-bold text-rose-900">เลยกำหนดการส่ง</h4>
                    <p class="mt-1 text-sm text-rose-700">รายการที่เลยวันครบกำหนดแล้ว</p>
                </div>
                <div class="inline-flex h-12 min-w-[88px] shrink-0 items-center justify-center rounded-full bg-white px-4 text-center text-sm font-semibold leading-none text-rose-700 shadow-sm ring-1 ring-rose-200">
                    {{ $evaluateeOverview['overdueCount'] }} รายการ
                </div>
            </div>

            <div class="mt-6">
                @forelse($evaluateeOverview['overdueList'] as $assignment)
                    <div class="w-full border-b border-rose-100 py-4 last:border-b-0">
                        <div class="line-clamp-2 text-sm font-semibold leading-6 text-slate-800">{{ $assignment['title'] }}</div>
                        <div class="mt-3 flex flex-col gap-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                            <span>ครบกำหนด {{ $assignment['deadline'] }}</span>
                            <a href="{{ route('evaluation.show', $assignment['id']) }}" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold shadow transition sm:w-auto sm:min-w-[116px] {{ $assignment['action']['classes'] }}">
                                {{ $assignment['action']['label'] }}
                            </a>
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
</div>
