@props([
    'type' => 'primary', // style
    'text',
    'onclick' => null,
    'buttonType' => 'button',
])

@php
    $class = match ($type) {
        'primary' => 'bg-lime-400 hover:bg-lime-400 text-gray-800 font-semibold',
        'danger'  => 'bg-red-500 hover:bg-red-600 text-white',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
        default   => 'bg-gray-300 hover:bg-gray-400 text-gray-800',
    };
@endphp

<button 
    type="{{ $buttonType }}"
    @if ($onclick) onclick="{{ $onclick }}" @endif
    {{ $attributes->merge(['class' => "px-4 py-2 rounded-lg $class"]) }}
>
    {{ $text }}
</button>
