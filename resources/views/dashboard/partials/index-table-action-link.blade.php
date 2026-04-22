{{-- ลิงก์ดูรายละเอียดผลการประเมิน --}}
<td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
    <div class="flex h-full items-center justify-center">
        <a
            href="{{ url('/dashboard-data/' . ($evaluation->report->id ?? $evaluation->report->report_id ?? '')) }}"
            class="text-center text-blue-600 transition-colors hover:text-blue-900">
            ดูรายละเอียด
        </a>
    </div>
</td>
