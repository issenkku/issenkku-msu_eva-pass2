{{-- รายการสิทธิ์แต่ละตัวในฟอร์มแก้บทบาท --}}
<label>
    <input
        type="checkbox"
        name="permissions[]"
        value="{{ $permission->name }}"
        {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
    >
    {{ $permission->name }}
</label>
