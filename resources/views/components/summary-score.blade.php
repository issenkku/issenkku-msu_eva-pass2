@props([
    'title' => '',
    'value' => '',
    'subtitle' => '',
    'color' => 'purple',
    'icon' => null,
    'iconSize' => 'text-3xl',
])

@php
    $borderColor = [
        'purple' => 'border-purple-500',
        'blue' => 'border-blue-500',
        'green' => 'border-green-500',
        'red' => 'border-red-500',
        'yellow' => 'border-yellow-500',
        'gray' => 'border-gray-500',
    ][$color] ?? 'border-purple-500';

    $iconBg = [
        'purple' => 'bg-purple-100 text-purple-600',
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-green-100 text-green-600',
        'red' => 'bg-red-100 text-red-600',
        'yellow' => 'bg-yellow-100 text-yellow-600',
        'gray' => 'bg-gray-100 text-gray-600',
    ][$color] ?? 'bg-purple-100 text-purple-600';
@endphp

{{--  --}}
<div class="stat-card bg-white rounded-xl p-6 shadow-md hover-scale {{ $borderColor }} border-l-4 flex items-center justify-between mb-4">
    <div>
        <p class="text-gray-500 text-sm font-medium">{{ $title }}</p>
        <p class="text-3xl font-bold mt-2 text-gray-900">{{ $value }}</p>
        @if($subtitle)
            <p class="text-gray-500 text-sm mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    {{-- บล็อกเนื้อหา --}}
    <div class="p-3 rounded-lg {{ $iconBg }}">
        @if ($icon)
            <i class="{{ $icon }} {{ $iconSize }} w-8 h-8"></i>
        @else
            {{-- Default fallback icon --}}
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 
                       00-2 2v6a2 2 0 002 2h2a2 2 
                       0 002-2zm0 0V9a2 2 0 
                       012-2h2a2 2 0 012 2v10m-6 
                       0a2 2 0 002 2h2a2 2 0 
                       002-2m0 0V5a2 2 0 012-2h2a2 
                       2 0 012 2v14a2 2 0 01-2 2h-2a2 
                       2 0 01-2-2z"/>
            </svg>
        @endif
    </div>
</div>
