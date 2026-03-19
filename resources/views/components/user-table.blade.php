@props(['index', 'employee'])

@php
    $typeClass = match ($employee['type']) {
        'สนับสนุน' => 'bg-green-200 text-green-800',
        'วิชาการ' => 'bg-yellow-200 text-yellow-800',
        'บริหาร' => 'bg-blue-200 text-blue-800',
        default => '',
    };

    $editUserPayload = [
        'id' => $employee['id'],
        'prefix' => $employee['prefix'] ?? '',
        'name' => $employee['name'] ?? '',
        'employee_id' => $employee['code'] ?? '',
        'email' => $employee['email'] ?? '',
        'phone' => $employee['contact'] ?? '',
        'personnel_type' => $employee['type'] ?? '',
        'bio' => $employee['bio'] ?? '',
        'education_history' => $employee['education_history'] ?? [],
        'status' => $employee['status'] ?? 'active',
        'position_id' => $employee['position_id'] ?? null,
        'department_id' => $employee['department_id'] ?? null,
        'roles' => array_map(fn ($role) => ['name' => $role], $employee['role_names'] ?? []),
    ];
    $editUserPayloadEncoded = base64_encode(json_encode($editUserPayload, JSON_UNESCAPED_UNICODE));
@endphp

<tr class="border-b">
    <td class="p-4 text-center">{{ $index }}</td>
    <td class="p-4">
        {{ trim(collect([$employee['prefix'] ?? '', $employee['name'] ?? ''])->filter()->implode(' ')) }}
        @if (!empty($employee['role_names']))
            <div class="mt-1 flex flex-wrap gap-1">
                @foreach ($employee['role_names'] as $role)
                    @php
                        $roleClass = match ($role) {
                            'admin' => 'bg-purple-200 text-purple-800',
                            'ผู้บริหาร' => 'bg-yellow-200 text-yellow-800',
                            'ผู้ประเมิน' => 'bg-green-200 text-green-800',
                            'ผู้รับการประเมิน' => 'bg-blue-200 text-blue-800',
                            'กรรมการ' => 'bg-orange-200 text-orange-800',
                            default => 'bg-gray-200 text-gray-800',
                        };
                    @endphp
                    <span class="{{ $roleClass }} mb-1 rounded-full px-3 py-1 text-xs">{{ $role }}</span>
                @endforeach
            </div>
        @endif
    </td>
    <td class="p-4 text-center">{{ $employee['code'] }}</td>
    <td class="p-4 text-center">{{ $employee['position'] }}</td>
    <td class="p-4 text-center">
        <span class="{{ $typeClass }} rounded-full px-3 py-1 text-sm">{{ $employee['type'] }}</span>
    </td>
    <td class="p-4 text-center">{{ $employee['contact'] }}</td>
    <td class="p-4 text-center space-x-2">
        <div class="d-flex justify-content-center gap-2 align-items-center">
            <x-button
                type="warning"
                text="แก้ไข"
                class="text-sm"
                icon="fas fa-edit"
                onclick="openEditModalFromButton(this)"
                data-user="{{ $editUserPayloadEncoded }}"
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
