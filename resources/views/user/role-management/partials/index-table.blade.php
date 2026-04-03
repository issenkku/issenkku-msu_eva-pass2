{{-- ตารางบทบาท แสดงชื่อบทบาท สิทธิ์ที่ผูกไว้ และปุ่มจัดการ --}}
<table class="role-table">
    <thead>
        <tr>
            <th>#</th>
            <th>ชื่อบทบาท</th>
            <th>สิทธิ์</th>
            <th>การดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($roles as $i => $role)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $role->name }}</td>
                <td>
                    @foreach($role->permissions as $permission)
                        <span class="permission-badge">{{ $permission->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('roles.edit', $role) }}" class="role-action-link edit">แก้ไข</a>

                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline-block"
                        onsubmit="return confirm('ลบบทบาทนี้ใช่หรือไม่?')">
                        @csrf
                        @method('DELETE')
                        <button class="role-action-link delete">ลบ</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
