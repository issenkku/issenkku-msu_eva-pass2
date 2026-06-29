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
    $controlId = $name . '_filter_control';
    $panelId = $name . '_filter_panel';
@endphp

@include('components.alpine-cloak-style')

<div class="relative w-48" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <div id="{{ $name }}_filter_label" class="mb-1 block text-sm font-medium text-gray-700">{{ $label }}</div>

    <button
        x-ref="button"
        id="{{ $controlId }}"
        type="button"
        class="relative w-full cursor-pointer rounded-md border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm"
        aria-haspopup="group"
        aria-expanded="false"
        aria-controls="{{ $panelId }}"
        aria-labelledby="{{ $name }}_filter_label"
        x-bind:aria-expanded="open.toString()"
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
    </button>

    <div
        id="{{ $panelId }}"
        role="group"
        aria-labelledby="{{ $controlId }}"
        class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-lg"
        x-show="open"
        x-transition
        x-cloak
        @keydown.escape.stop.prevent="open = false; $refs.button?.focus()">
        <div class="space-y-1 p-2">
            @foreach ($options as $key => $option)
                <label class="flex items-center space-x-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="{{ $name }}[]"
                        value="{{ $key }}"
                        @checked(in_array($key, $value))
                        data-auto-submit-select
                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

@include('components.alpine-cdn-script')
@include('components.auto-submit-script')
