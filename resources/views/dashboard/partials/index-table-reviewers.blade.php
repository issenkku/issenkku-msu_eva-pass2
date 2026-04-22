{{-- คอลัมน์ผู้ประเมินและรายชื่อผู้ตรวจเพิ่ม --}}
<td class="align-top px-6 py-4">
    <div class="max-w-[280px]">
        @if ($primaryReviewer)
            <div class="break-words text-sm font-medium text-gray-900">
                {{ $primaryReviewer['name'] }}
            </div>
            @if ($additionalReviewerCount > 0)
                <button
                    type="button"
                    class="mt-1 text-xs font-medium text-blue-600 underline hover:text-blue-800"
                    data-reviewer-modal-button
                    data-reviewers='@json($reviewerEntries->values())'>
                    เพิ่มเติม ({{ $additionalReviewerCount }} คน)
                </button>
            @endif
        @else
            <div class="break-words text-sm font-medium text-gray-900">
                {{ $evaluatorName }}
            </div>
        @endif
    </div>
</td>
