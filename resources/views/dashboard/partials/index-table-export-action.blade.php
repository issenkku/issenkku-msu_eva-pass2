{{-- ปุ่มส่งออกรายงานเมื่อรายการประเมินเสร็จสิ้นแล้ว --}}
<td class="whitespace-nowrap px-3 py-4">
    <div class="flex items-center justify-center">
        @if ($status === 'Completed')
            <a
                href="{{ route('single.reports.export', ['id' => $evaluation->report->id ?? 0]) }}"
                class="rounded-md bg-green-400 p-2 text-white transition duration-200 hover:bg-green-500"
                title="ส่งออกรายงานผลการประเมินของ {{ $evaluateeName ?? 'บุคคล' }}">
                <i class="fas fa-file-export"></i>
            </a>
        @else
            <div>-</div>
        @endif
    </div>
</td>
