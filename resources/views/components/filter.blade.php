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

@include('components.alpine-cloak-style')

<div class="relative w-48" x-data="{ open: false }" @click.outside="open = false">
    <label class="mb-1 block text-sm font-medium text-gray-700">{{ $label }}</label>

    <div
        class="relative w-full cursor-pointer rounded-md border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm"
        @click="open = !open">
        <span class="block truncate">
            {{ count($selectedLabels) ? implode(', ', $selectedLabels) : $placeholder }}
        </span>
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <div
        class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-lg"
        x-show="open"
        x-transition
        x-cloak>
        <div class="space-y-1 p-2">
            @foreach ($options as $key => $option)
                <label class="flex items-center space-x-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="{{ $name }}[]"
                        value="{{ $key }}"
                        @checked(in_array($key, $value))
                        onchange="this.form.submit()"
                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

@include('components.alpine-cdn-script')
