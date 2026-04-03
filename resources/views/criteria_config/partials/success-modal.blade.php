{{-- modal สำเร็จหลังบันทึกเกณฑ์ --}}
<div id="success_modal"
    class="fixed inset-0 {{ $backdropClass ?? 'bg-opacity-50' }} backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full">
        <div class="text-center">
            <div class="bg-green-100 rounded-full p-4 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{!! $title !!}</h3>
            <p class="text-gray-600 mb-6">{!! $message !!}</p>

            @if (!empty($countdown))
                <p class="text-gray-500 text-sm mb-6">
                    {!! $countdownPrefix !!} <span id="countdown">{{ $countdown }}</span> {!! $countdownSuffix !!}
                </p>
            @endif

            <div class="flex justify-center space-x-4">
                <a href="{{ route('criteria_config.index') }}"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    {!! $buttonLabel !!}
                </a>
            </div>
        </div>
    </div>
</div>
