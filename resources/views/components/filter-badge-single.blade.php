@props([
    'name',
    'options' => [],
    'value' => request($name),
    'placeholder',
])

@php
    $controlId = $name . '-filter-badge-control';
    $panelId = $name . '-filter-badge-panel';
@endphp

@include('components.alpine-cloak-style')

<div class="filter-badge-single relative w-48" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button
        x-ref="button"
        id="{{ $controlId }}"
        type="button"
        class="relative w-full cursor-pointer rounded-md border border-gray-300 bg-white px-4 py-2 text-medium shadow-sm"
        aria-expanded="false"
        aria-controls="{{ $panelId }}"
        x-bind:aria-expanded="open.toString()"
        @click="open = !open">
        <span class="block truncate">
            {{ $options[$value] ?? $placeholder }}
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
        role="radiogroup"
        aria-orientation="vertical"
        aria-labelledby="{{ $controlId }}"
        class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-md border border-gray-300 bg-white shadow-lg"
        x-show="open"
        x-transition
        x-cloak
        @keydown.escape.stop.prevent="open = false; $refs.button.focus()">
        <form method="GET" action="{{ url()->current() }}">
            <div class="space-y-1 p-2">
                <label class="flex items-center space-x-2 text-medium text-gray-700">
                    <input
                        type="radio"
                        name="{{ $name }}"
                        value=""
                        data-auto-submit-select
                        @checked($value === null)
                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <span>{{ $placeholder }}</span>
                </label>
                @foreach ($options as $key => $option)
                    <label class="flex items-center space-x-2 text-medium text-gray-700">
                    <input
                        type="radio"
                        name="{{ $name }}"
                        value="{{ $key }}"
                        data-auto-submit-select
                        @checked((string) $value === (string) $key)
                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <span>{{ $option }}</span>
                </label>
            @endforeach
            </div>

            @foreach (request()->except($name, 'page') as $key => $val)
                @if (is_array($val))
                    @foreach ($val as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
        </form>
    </div>
</div>

@include('components.alpine-cdn-script')
@include('components.auto-submit-script')
