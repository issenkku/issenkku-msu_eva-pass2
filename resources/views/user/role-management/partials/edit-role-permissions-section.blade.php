{{-- รายการสิทธิ์ที่ผูกกับบทบาท --}}
<div class="mb-4">
    <label class="block mb-1 font-medium">สิทธิ์</label>
    <div class="grid grid-cols-2 gap-2">
        @foreach ($permissions as $permission)
            @include('user.role-management.partials.edit-role-permission-item', ['permission' => $permission])
        @endforeach
    </div>
</div>
