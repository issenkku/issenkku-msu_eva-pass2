{{-- รายชื่อผู้ใช้ที่ได้รับบทบาทนี้ --}}
<div class="mb-4">
    <label class="block mb-1 font-medium">ผู้ใช้ที่ได้รับบทบาทนี้</label>
    <div class="grid grid-cols-2 gap-2">
        @foreach ($users as $user)
            @include('user.role-management.partials.edit-role-user-item', ['user' => $user])
        @endforeach
    </div>
</div>
