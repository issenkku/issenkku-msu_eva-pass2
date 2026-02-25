@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\user\role-management\index.blade.php --}}

@section('content')
{{-- บล็อกเนื้อหา --}}
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">การจัดการบทบาทและสิทธิ์</h1>

    {{-- บล็อกเนื้อหา --}}
    <div class="mb-4 flex justify-between">
        <button onclick="openCreateModal()" class="bg-purple-600 text-white px-4 py-2 rounded">เพิ่มบทบาท</button>
    </div>

    {{-- ตารางข้อมูล --}}
    <table class="w-full bg-white shadow rounded">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-3">#</th>
                <th class="p-3">ชื่อบทบาท</th>
                <th class="p-3">สิทธิ์</th>
                <th class="p-3">การดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $i => $role)
                <tr class="border-t">
                    <td class="p-3">{{ $i + 1 }}</td>
                    <td class="p-3">{{ $role->name }}</td>
                    <td class="p-3">
                        @foreach($role->permissions as $p)
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">{{ $p->name }}</span>
                        @endforeach
                    </td>
                    <td class="p-3 space-x-2">
                        <a href="{{ route('roles.edit', $role) }}"
                           class="bg-indigo-500 text-white px-3 py-1 rounded">แก้ไข</a>

                        {{-- ฟอร์ม --}}
                        <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline-block"
                              onsubmit="return confirm('ลบบทบาทนี้ใช่หรือไม่?')">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-500 text-white px-3 py-1 rounded">ลบ</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Optional: Keep Create Modal -->
{{-- บล็อกเนื้อหา --}}
<div id="roleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    {{-- บล็อกเนื้อหา --}}
    <div class="bg-white p-6 rounded-lg w-full max-w-xl">
        <h2 id="modalTitle" class="text-lg font-semibold mb-4">เพิ่มบทบาท</h2>
        {{-- ฟอร์ม --}}
        <form method="POST" id="roleForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="text" name="name" id="roleName" placeholder="ชื่อบทบาท" class="w-full border rounded px-3 py-2 mb-4">

            <label class="block mb-2 font-medium">สิทธิ์</label>
            <div class="grid grid-cols-2 gap-2 mb-4">
                @foreach($permissions as $perm)
                <label>
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="permission-checkbox">
                    {{ $perm->name }}
                </label>
                @endforeach
            </div>

            <div class="text-right space-x-2">
                <button type="button" onclick="closeModal()" class="px-3 py-2 bg-gray-300 rounded">ยกเลิก</button>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('roleModal').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = 'เพิ่มบทบาท';
    document.getElementById('roleForm').action = '{{ route("roles.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('roleName').value = '';
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
}

function closeModal() {
    document.getElementById('roleModal').classList.add('hidden');
}
</script>
@endsection
