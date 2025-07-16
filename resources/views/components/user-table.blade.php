@props(['index', 'employee'])

<tr class="border-b">
    <td class="p-4 text-center">{{ $index }}</td>
    <td class="p-4">
        {{ $employee['name'] }}
        @if(!empty($employee['role']))
            <div class="mt-1 text-xs text-blue-700">บทบาท: <strong>{{ $employee['role'] }}</strong></div>
        @endif
    </td>
    <td class="p-4 text-center">{{ $employee['code'] }}</td>
    <td class="p-4 text-center">{{ $employee['position'] }}</td>
    <td class="p-4 text-center">
        <span class="bg-green-200 text-green-800 px-2 py-1 rounded-full text-sm">
            {{ $employee['type'] }}
        </span>
    </td>
    <td class="p-4 text-center">{{ $employee['contact'] }}</td>
    <td class="p-4 text-center space-x-2">
        <x-button type="warning" text="แก้ไข" class="text-sm" 
            onclick='openEditModal({
                id: {{ $employee["id"] }},
                prefix: "{{ $employee["prefix"] ?? "" }}",
                name: "{{ $employee["name"] }}",
                employee_id: "{{ $employee["code"] }}",
                email: "{{ $employee["email"] ?? "" }}",
                phone: "{{ $employee["contact"] }}",
                personnel_type: "{{ $employee["type"] }}",
                bio: "{{ $employee["bio"] ?? "" }}",
                status: "{{ $employee["status"] ?? "active" }}",
                position_id: {{ $employee["position_id"] ?? "null" }},
                department_id: {{ $employee["department_id"] ?? "null" }},
                roles: [{ name: {!! json_encode($employee["role"] ?? "") !!} }]
            })'
        />
        <form action="{{ route('users.destroy', $employee['id']) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <x-button 
                type="danger" 
                text="ลบ" 
                buttonType="submit" 
                class="text-sm" 
                onclick="return confirm('ยืนยันการลบ?')" 
            />
        </form>
    </td>
</tr>

