{{-- กล่องข้อความแจ้งผลและข้อผิดพลาดของหน้าแบบประเมิน --}}
@if (session('success'))
    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
