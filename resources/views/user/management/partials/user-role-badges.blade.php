{{-- badge แสดงบทบาทของผู้ใช้งานแต่ละคน --}}
<div class="mt-1 flex flex-wrap gap-1">
    @foreach ($roles as $role)
        @php
            $roleClass = match ($role) {
                'admin' => 'bg-purple-200 text-purple-800',
                'ผู้บริหาร' => 'bg-yellow-200 text-yellow-800',
                'ผู้ประเมิน' => 'bg-green-200 text-green-800',
                'ผู้รับการประเมิน' => 'bg-blue-200 text-blue-800',
                'กรรมการ' => 'bg-orange-200 text-orange-800',
                default => 'bg-gray-200 text-gray-800',
            };
        @endphp
        <span class="{{ $roleClass }} mb-1 rounded-full px-3 py-1 text-xs">{{ $role }}</span>
    @endforeach
</div>
