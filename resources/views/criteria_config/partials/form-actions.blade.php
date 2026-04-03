{{-- ปุ่ม action ท้ายฟอร์ม ใช้ร่วมกันระหว่าง create และ edit --}}
<div class="flex justify-end mt-10 space-x-4">
    @if (($mode ?? 'create') === 'create')
        <button type="button" id="reset_form_btn"
            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            ล้างฟอร์ม
        </button>
    @else
        <a href="{{ route('criteria_config.index') }}"
            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
            ยกเลิก
        </a>
    @endif

    <button type="submit"
        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition {{ ($mode ?? 'create') === 'create' ? 'flex items-center' : 'font-medium' }}">
        @if (($mode ?? 'create') === 'create')
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        @endif
        {!! $submitLabel !!}
    </button>
</div>
