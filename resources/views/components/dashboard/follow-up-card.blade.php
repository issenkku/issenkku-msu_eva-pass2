{{-- การ์ดรายการที่ควรติดตามใน dashboard --}}
@props([
    'name',
    'status',
    'progress' => 0,
    'dueDate',
    'remainingText',
])

<div class="rounded-2xl border border-gray-200 p-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-lg font-semibold text-gray-900">{{ $name }}</p>
            <p class="text-sm text-gray-500">{!! $status !!}</p>
        </div>
        <div class="text-right">
            <p class="text-lg font-bold text-gray-900">{{ $progress }}%</p>
        </div>
    </div>
    <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-100">
        <div class="h-full rounded-full bg-blue-500" style="width: {{ min($progress, 100) }}%;"></div>
    </div>
    <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
        <span>{!! $dueDate !!}</span>
        <span>{{ $remainingText }}</span>
    </div>
</div>
