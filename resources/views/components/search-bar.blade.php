<!-- resources/views/components/search-bar.blade.php -->
<form action="{{ url()->current() }}" method="GET" class="flex items-center space-x-2">
    
    <!-- Search Input -->
    <div class="relative">
        <input 
            type="text" 
            name="search" 
            placeholder="ค้นหาชื่อ, รหัสพนักงาน..."
            value="{{ request('search') }}"
            class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <!-- Submit Button (ส่วนที่เพิ่มเข้ามา) -->
    <button 
        type="submit" 
        class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
    >
        ค้นหา
    </button>
    
    <!-- Hidden Inputs for other filters -->
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