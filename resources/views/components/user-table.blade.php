@props(['index', 'employee'])

<tr class="border-b">
    <td class="p-4 text-center">{{ $index }}</td>
    <td class="p-4">{{ $employee['name'] }}</td>
    <td class="p-4 text-center">{{ $employee['code'] }}</td>
    <td class="p-4 text-center">{{ $employee['position'] }}</td>
    <td class="p-4 text-center">
        <span class="bg-green-200 text-green-800 px-2 py-1 rounded-full text-sm">
            {{ $employee['type'] }}
        </span>
    </td>
    <td class="p-4 text-center">{{ $employee['contact'] }}</td>
    <td class="p-4 text-center space-x-2">
        <x-button type="primary" text="แก้ไข" class="text-sm" />
        <form action="{{ route('users.destroy', $employee['id']) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <x-button type="danger" text="ลบ" class="text-sm" onclick="return confirm('ยืนยันการลบ?')" />
        </form>
    </td>
</tr>
