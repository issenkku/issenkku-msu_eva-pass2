{{-- ปุ่มจัดการของบทบาทแต่ละรายการ --}}
<a href="{{ route('roles.edit', $role) }}" class="role-action-link edit">แก้ไข</a>

<form
    method="POST"
    action="{{ route('roles.destroy', $role) }}"
    class="inline-block"
    onsubmit="return confirm('ลบบทบาทนี้ใช่หรือไม่?')"
    data-async-delete-form
>
    @csrf
    @method('DELETE')
    <button class="role-action-link delete">ลบ</button>
</form>
