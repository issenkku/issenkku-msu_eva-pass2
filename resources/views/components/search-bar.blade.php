<!-- resources/views/components/search-bar.blade.php -->
@props([
    'placeholder',
    'inputClass' => '',
])

<form action="{{ url()->current() }}" method="GET" role="search" class="search-bar-form flex items-center space-x-2" data-auto-search-form>
    <div class="relative">
        <input
            type="text"
            name="search"
            data-auto-search-input
            placeholder="{{ $placeholder ?? 'ค้นหา...' }}"
            aria-label="{{ $placeholder ?? 'ค้นหา' }}"
            value="{{ request('search') }}"
            class="search-bar-input w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 {{ $inputClass }}"
        >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <button
        type="submit"
        class="search-bar-submit px-5 py-2 rounded-lg bg-gray-400 text-white hover:bg-gray-500 transition"
    >
        ค้นหา
    </button>

    @if (request('search'))
        <button
            type="button"
            data-auto-search-clear
            aria-label="ล้างคำค้นหา"
            class="search-bar-clear px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
            ล้าง
        </button>
    @endif

    @foreach (request()->except('search', 'page') as $key => $value)
        @if (is_array($value))
            @foreach ($value as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
</form>

@include('components.search-bar-script')
