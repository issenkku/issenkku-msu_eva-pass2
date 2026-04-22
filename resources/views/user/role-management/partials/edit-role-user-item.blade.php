{{-- รายการผู้ใช้แต่ละคนในฟอร์มแก้บทบาท --}}
<label class="flex items-center space-x-2">
    <input
        type="checkbox"
        name="users[]"
        value="{{ $user->id }}"
        {{ in_array($user->id, $assignedUsers) ? 'checked' : '' }}
    >
    <span>{{ $user->name }}</span>
</label>
