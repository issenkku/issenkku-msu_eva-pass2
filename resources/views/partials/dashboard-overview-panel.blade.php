{{-- แผงภาพรวมด้านซ้ายของ dashboard ใช้ซ้ำได้ทุกบทบาท --}}
@php
    $overviewTitle = $overviewTitle ?? 'ภาพรวมการประเมิน';
    $overviewSubtitle = $overviewSubtitle ?? 'ใช้ติดตามความคืบหน้าของงานในภาพรวม';
    $overviewChart = $overviewChart ?? [];
    $overviewPercents = $overviewPercents ?? [];
    $activeFilters = $activeFilters ?? collect();
@endphp

<div class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-6 shadow-md">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">{{ $overviewTitle }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $overviewSubtitle }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-100 bg-slate-50/60 p-5 lg:p-6">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[300px_minmax(0,1fr)] xl:items-center">
            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-1">
                    <x-dashboard.metric-card
                        title="บุคลากรทั้งหมด"
                        :value="$totalUsers"
                        unit="คน"
                        tone="blue" />
                    <x-dashboard.metric-card
                        title="ผู้เข้ารับการประเมิน"
                        :value="$totalEvaluatees"
                        unit="คน"
                        tone="emerald" />
                </div>

                <div class="overview-chart-wrap mx-auto xl:mx-0">
                    <canvas id="{{ $overviewChart['id'] }}"></canvas>
                    <div class="overview-chart-center">
                        <div id="{{ $overviewChart['id'] }}Value" class="overview-chart-value">{{ $overviewChart['centerValue'] }}</div>
                        <div id="{{ $overviewChart['id'] }}Label" class="overview-chart-label">{{ $overviewChart['centerLabel'] }}</div>
                        <div id="{{ $overviewChart['id'] }}Meta" class="overview-chart-meta">{{ $overviewChart['centerMeta'] }}</div>
                    </div>
                </div>

                @if ($activeFilters->isNotEmpty())
                    <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-start">
                        @if (request('status'))
                            <div class="rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 ring-1 ring-orange-100">
                                กรองอยู่: {{ request('status') }}
                            </div>
                        @endif
                        <button
                            type="button"
                            data-dashboard-reset-filters
                            class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-gray-200 transition hover:bg-gray-50">
                            ล้างการกรอง
                        </button>
                    </div>
                @endif
            </div>

            <div class="space-y-2.5">
                @foreach ($overviewChart['labels'] as $index => $label)
                    <x-dashboard.status-card
                        :label="$label"
                        :count="$overviewChart['data'][$index]"
                        :percent="$overviewPercents[$index] ?? 0"
                        :color="$overviewChart['colors'][$index]"
                        total-label="คิดเป็น <span class='font-semibold' style='color: {{ $overviewChart['colors'][$index] }}'>{{ $overviewPercents[$index] ?? 0 }}%</span> ของผู้เข้ารับการประเมินทั้งหมด {{ $totalEvaluatees }} คน"
                        unit="คน" />
                @endforeach
            </div>
        </div>
    </div>
</div>
