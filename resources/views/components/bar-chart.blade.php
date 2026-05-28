<div class="animate-fadeIn rounded-xl bg-white p-6 shadow-md" style="animation-delay: 0.4s;">
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>

        @if($downloadable)
            <div class="flex space-x-2">
                <button
                    id="{{ $chartId }}_downloadBtn"
                    class="text-gray-400 transition-colors hover:text-gray-600"
                    title="ดาวน์โหลดกราฟ"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                        ></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <div class="h-80" style="height: {{ $height }}">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
</div>

@push('scripts')
    @include('components.bar-chart-script')
@endpush
