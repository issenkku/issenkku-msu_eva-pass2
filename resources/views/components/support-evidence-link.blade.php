@props([
    'href',
    'label' => 'เปิดดู',
    'ariaLabel' => null,
])

<a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
    @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    class="inline-flex max-w-full items-center justify-center gap-1 whitespace-nowrap rounded-lg border border-blue-200 bg-blue-50 px-2 py-1.5 text-xs font-semibold leading-tight text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1">
    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor"
        stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M7.5 12.5l5-5m-3.75-2.25 1.5-1.5a3.182 3.182 0 0 1 4.5 4.5l-1.5 1.5m-2 5-1.5 1.5a3.182 3.182 0 0 1-4.5-4.5l1.5-1.5" />
    </svg>
    <span>{{ $label }}</span>
    <span class="shrink-0" aria-hidden="true">↗</span>
</a>
