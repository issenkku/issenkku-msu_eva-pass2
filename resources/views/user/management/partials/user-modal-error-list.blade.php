{{-- แสดง validation error จากฝั่ง Laravel เมื่อ submit ฟอร์มแล้วไม่ผ่าน --}}
@if ($errors->any())
    <div class="mb-4 mt-4 rounded border border-red-400 bg-red-100 p-3 text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
