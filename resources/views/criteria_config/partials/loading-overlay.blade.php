{{-- loading overlay กลางหน้า ใช้ร่วมกันระหว่าง create และ edit --}}
<div id="loading_overlay"
    class="fixed inset-0 {{ $backdropClass ?? 'bg-opacity-50' }} backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-700 text-lg">{!! $message !!}</p>
    </div>
</div>
