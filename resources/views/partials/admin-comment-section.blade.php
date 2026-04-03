{{-- ความคิดเห็นจากผู้ประเมินสำหรับหน้า admin เก็บแยกไว้เพื่อให้หน้าแม่เหลือเฉพาะโครงฟอร์ม --}}
<div class="bg-purple-50 border border-blue-200 rounded-lg p-6 mt-8">
    <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
            >
            </path>
        </svg>
        ความคิดเห็นจากผู้ประเมิน
    </h3>

    @if(!$readonly)
        <textarea
            name="comment"
            class="bg-white form-input text-base w-full mt-2 px-3 rounded-lg p-4 border border-gray-400 focus:ring-green-500 focus:border-green-500"
            placeholder="ระบุความความเห็นเพิ่มเติม"
        >{{ old('comment', $assignment->report->comment ?? '') }}</textarea>
    @elseif(isset($report->comment) && !empty($report->comment))
        <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($report->comment)) !!}
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
            <p class="text-gray-500 italic">ไม่มีความคิดเห็นเพิ่มเติม</p>
        </div>
    @endif
</div>
