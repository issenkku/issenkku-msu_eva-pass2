{{-- การ์ดสถานะใน overview ใช้ซ้ำระหว่าง manager/director/evaluator ได้ --}}
@props([
    'label',
    'count' => 0,
    'percent' => 0,
    'color' => '#3b82f6',
    'totalLabel',
    'unit' => null,
])

<div class="rounded-xl border px-4 py-3.5 shadow-sm" style="border-color: {{ $color }}22; background: linear-gradient(135deg, {{ $color }}12 0%, #ffffff 55%); box-shadow: inset 4px 0 0 {{ $color }};">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex min-w-0 items-center gap-3">
                <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $color }}"></span>
                <div class="truncate text-[15px] font-semibold text-gray-900">{!! $label !!}</div>
            </div>
        </div>
        <div class="shrink-0 rounded-2xl px-3 py-2 text-center" style="min-width: 86px; background-color: {{ $color }}14;">
            <div class="text-2xl font-extrabold leading-none" style="color: {{ $color }}">{{ $count }}</div>
            @if (filled($unit))
                <div class="mt-1 text-xs font-semibold" style="color: {{ $color }}">{!! $unit !!}</div>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between gap-4 text-sm">
        <div class="text-gray-600">
            {!! $totalLabel !!}
            <span class="font-semibold" style="color: {{ $color }}">{{ $percent }}%</span>
        </div>
    </div>

    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-white/80">
        <div class="h-full rounded-full" style="width: {{ min($percent, 100) }}%; background-color: {{ $color }};"></div>
    </div>
</div>
