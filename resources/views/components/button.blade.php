@props([
    'type' => 'primary', // style
    'text',
    'onclick' => null,
    'buttonType' => 'button',
])

@php
    $class = $type === 'primary' 
        ? 'bg-purple-600 hover:bg-purple-700 text-white'
        : ($type === 'danger' 
            ? 'bg-red-500 hover:bg-red-600 text-white' 
            : 'bg-gray-300 text-black');
@endphp

<button 
    type="{{ $buttonType }}"
    @if ($onclick) onclick="{{ $onclick }}" @endif
    {{ $attributes->merge(['class' => "px-4 py-2 rounded-lg $class"]) }}
>
    {{ $text }}
</button>
