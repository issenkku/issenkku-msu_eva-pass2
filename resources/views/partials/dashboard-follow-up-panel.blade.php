{{-- แผงรายการที่ควรติดตามด้านขวาของ dashboard --}}
@php
    $followUpTitle = $followUpTitle ?? 'รายการที่ควรติดตาม';
    $followUpSubtitle = $followUpSubtitle ?? 'แสดงงานที่ควรติดตามก่อน';
@endphp

<div class="rounded-2xl bg-white p-6 shadow-md border border-gray-100">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">{{ $followUpTitle }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $followUpSubtitle }}</p>
        </div>
        <a href="#evaluation-table" class="text-sm font-medium text-blue-600 hover:text-blue-700">ดูทั้งหมด</a>
    </div>

    <div class="mt-5 space-y-4">
        @forelse($followUpEvaluations->take(5) as $item)
            <x-dashboard.follow-up-card
                :name="$item['evaluatee_name']"
                status="สถานะ: {{ $item['pretty_status'] }}"
                :progress="$item['progress_percent']"
                due-date="ครบกำหนด {{ $item['due_date'] }}"
                :remaining-text="$item['remaining_text']" />
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-500">
                ไม่มีรายการที่ต้องติดตาม
            </div>
        @endforelse
    </div>
</div>
