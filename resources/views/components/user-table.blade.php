@props(['index', 'employee'])

@php
    $roleClass = match ($employee['role']) {
        'admin' => 'bg-purple-200 text-purple-800',
        'ผู้บริหาร' => 'bg-yellow-200 text-yellow-800',
        'ผู้ประเมิน' => 'bg-green-200 text-green-800',
        'ผู้รับการประเมิน' => 'bg-blue-200 text-blue-800',
        default => 'bg-gray-200 text-gray-800',
    };

    $typeClass = match ($employee['type']) {
        'สนับสนุน' => 'bg-green-200 text-green-800',
        'วิชาการ' => 'bg-yellow-200 text-yellow-800',
        'บริหาร' => 'bg-blue-200 text-blue-800',
        default => '',
    };
@endphp

<tr class="border-b">
    <td class="p-4 text-center">{{ $index }}</td>
    <td class="p-4">
        {{ $employee['name'] }}
        @if(!empty($employee['role']))
            <div>
                <span class="{{ $roleClass }} px-3 rounded-full text-xs">
                    {{ $employee['role'] }}
                </span>
            </div>
        @endif
    </td>
    <td class="p-4 text-center">{{ $employee['code'] }}</td>
    <td class="p-4 text-center">{{ $employee['position'] }}</td>
    <td class="p-4 text-center">
        <span class="{{ $typeClass }} px-3 py-1 rounded-full text-sm">
            {{ $employee['type'] }}
        </span>
    </td>
    <td class="p-4 text-center">{{ $employee['contact'] }}</td>
    <td class="p-4 text-center space-x-2">
        <div class="d-flex justify-content-center gap-2 align-items-center">
            <x-button type="warning" text="แก้ไข" class="text-sm" icon="fas fa-edit"
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
            <x-button 
                type="danger" 
                text="ลบ" 
                buttonType="submit" 
                class="text-sm" 
                icon="fas fa-trash-alt"
                onclick="confirmDelete({{ $employee['id'] }})"
            />
        </div>
    </td>
</tr>

<x-delete-warning-modal 
    text="เจ้าหน้าที่" 
    formAction="{{ route('users.destroy', ':id') }}"
    entityUrl="/users" />



