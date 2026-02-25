{{-- ไฟล์มุมมอง: resources/views\components\button.blade.php --}}
@props([
    'type' => 'primary', // style
    'text',
    'onclick' => null,
    'buttonType' => 'button',
    'icon' => null,
    'href' => null,
])

@php
    $class = match ($type) {
        'primary' => 'bg-purple-600 hover:bg-purple-500 text-white font-semibold',
        'danger'  => 'bg-red-500 hover:bg-red-600 text-white',
        'warning' => 'bg-blue-500 hover:bg-blue-600 text-white',
        default   => 'bg-gray-300 hover:bg-gray-400 text-gray-800',
        'secondary' => 'bg-white border-2 border-purple-500 hover:bg-purple-200 text-purple-500 font-semibold',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "px-4 py-2 rounded-lg inline-flex items-center gap-2 $class"]) }}>
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        <span>{{ $text }}</span>
    </a>
@else
    <button 
        type="{{ $buttonType }}"
        @if ($onclick) onclick="{{ $onclick }}" @endif
        {{ $attributes->merge(['class' => "px-4 py-2 rounded-lg inline-flex items-center gap-2 $class"]) }}
    >
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        <span>{{ $text }}</span>
    </button>
@endif
