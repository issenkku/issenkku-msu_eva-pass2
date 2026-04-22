{{-- ส่วนแสดงข้อผิดพลาดจาก validation และการนำเข้า --}}
@if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-6">
        <div class="flex">
            <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">เกิดข้อผิดพลาดในการนำเข้าข้อมูล</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

@if(session('import_errors'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 max-h-40 overflow-y-auto mt-6">
        <div class="flex">
            <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">รายการข้อผิดพลาด</h3>
                <div class="mt-2 text-sm text-red-700">
                    @foreach(session('import_errors') as $error)
                        <div class="mb-2 p-2 bg-red-100 rounded">
                            <strong>แถวที่ {{ $error['row'] }}:</strong> {{ $error['error'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
