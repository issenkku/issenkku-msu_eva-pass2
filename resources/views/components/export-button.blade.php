@props(['route', 'userId' => null, 'label' => 'ส่งออกเป็น Excel'])

{{-- ฟอร์ม --}}
<form method="GET" action="{{ $route }}" class="export-button-form">
    @if($userId)
        <input type="hidden" name="user_id" value="{{ $userId }}">
    @endif
    {{-- Preserve filters --}}
    @foreach(request()->only(['search','year','start_time','end_time', 'department']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <a href="{{ $route }}"
        class="export-button-link min-h-11 inline-flex items-center px-4 py-2 border w-full border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3 m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
        </svg>
        {{ $label }}
    </a>
</form>
