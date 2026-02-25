{{-- ไฟล์มุมมอง: resources/views\components\filter.blade.php --}}
@props([
    'name',
    'label',
    'options' => [],
    'value' => request($name, []),
    'placeholder' => 'ทั้งหมด',
])

@php
    $value = is_array($value) ? $value : [$value];
    $selectedLabels = array_intersect_key($options, array_flip($value));
@endphp

<style>
    [x-cloak] { display: none !important; }
</style>

{{-- บล็อกเนื้อหา --}}
<div class="relative w-48" x-data="{ open: false }" @click.outside="open = false">
    <label class="text-sm font-medium text-gray-700 mb-1 block">{{ $label }}</label>

    <!-- Trigger -->
    <div 
        class="border border-gray-300 rounded-md px-4 py-2 text-sm bg-white shadow-sm w-full cursor-pointer relative"
        @click="open = !open"
    >
        <span class="block truncate">
            {{ count($selectedLabels) ? implode(', ', $selectedLabels) : $placeholder }}
        </span>
        {{-- บล็อกเนื้อหา --}}
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <!-- Dropdown content -->
    <div 
        class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
        x-show="open"
        x-transition
        x-cloak
    >
        {{-- บล็อกเนื้อหา --}}
        <div class="p-2 space-y-1">
            @foreach ($options as $key => $option)
                <label class="flex items-center space-x-2 text-sm text-gray-700">
                    <input 
                        type="checkbox" 
                        name="{{ $name }}[]" 
                        value="{{ $key }}" 
                        @checked(in_array($key, $value)) 
                        onchange="this.form.submit()"
                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
