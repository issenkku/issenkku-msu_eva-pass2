{{-- การ์ดสรุปตัวเลขแบบสั้น ใช้ซ้ำได้ในหน้า dashboard หลายบทบาท --}}
@props([
    'title',
    'value' => 0,
    'unit' => null,
    'tone' => 'blue',
])

@php
    $tones = [
        'blue' => [
            'border' => 'border-blue-100',
            'background' => 'from-blue-50 to-white',
            'title' => 'text-blue-600',
            'badgeBg' => 'bg-blue-100',
            'badgeText' => 'text-blue-700',
        ],
        'emerald' => [
            'border' => 'border-emerald-100',
            'background' => 'from-emerald-50 to-white',
            'title' => 'text-emerald-600',
            'badgeBg' => 'bg-emerald-100',
            'badgeText' => 'text-emerald-700',
        ],
    ];

    $palette = $tones[$tone] ?? $tones['blue'];
@endphp

<div @class([
    'rounded-2xl border bg-gradient-to-br px-4 py-3 shadow-sm',
    $palette['border'],
    $palette['background'],
])>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div @class([
                'text-base font-bold leading-tight',
                $palette['title'],
            ])>{!! $title !!}</div>
            <div class="mt-3 text-2xl font-extrabold leading-none text-slate-900">{{ $value }}</div>
        </div>

        @if (filled($unit))
            <div @class([
                'shrink-0 rounded-full px-3 py-1.5 text-sm font-bold',
                $palette['badgeBg'],
                $palette['badgeText'],
            ])>{!! $unit !!}</div>
        @endif
    </div>
</div>
