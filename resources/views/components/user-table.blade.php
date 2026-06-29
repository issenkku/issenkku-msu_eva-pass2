@props(['index', 'employee'])

@php
    // map ประเภทบุคลากรเป็นสี badge สำหรับตารางรายการผู้ใช้
    $typeClass = match ($employee['type']) {
        'สนับสนุน' => 'bg-green-200 text-green-800',
        'วิชาการ' => 'bg-yellow-200 text-yellow-800',
        'บริหาร' => 'bg-blue-200 text-blue-800',
        default => '',
    };

    // payload กลางสำหรับส่งเข้า modal แก้ไขผู้ใช้จากปุ่มในแต่ละแถว
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
        'job_level_id' => $employee['job_level_id'] ?? null,
        'department_id' => $employee['department_id'] ?? null,
        'roles' => array_map(fn ($role) => ['name' => $role], $employee['role_names'] ?? []),
    ];

    $editUserPayloadEncoded = base64_encode(json_encode($editUserPayload, JSON_UNESCAPED_UNICODE));
@endphp

<tr class="border-b">
    <td class="p-4 text-center">
        <input
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
            value="{{ $employee['id'] }}"
            aria-label="เลือก {{ trim(collect([$employee['prefix'] ?? '', $employee['name'] ?? ''])->filter()->implode(' ')) }}"
            data-user-bulk-checkbox
        >
    </td>
    <td class="p-4 text-center">{{ $index }}</td>
    <td class="p-4">
        {{ trim(collect([$employee['prefix'] ?? '', $employee['name'] ?? ''])->filter()->implode(' ')) }}

        @if (!empty($employee['role_names']))
            @include('user.management.partials.user-role-badges', ['roles' => $employee['role_names']])
        @endif
    </td>
    <td class="p-4 text-center">{{ $employee['code'] }}</td>
    <td class="p-4 text-center">{{ $employee['position'] }}</td>
    <td class="p-4 text-center">{{ $employee['job_level'] ?? '-' }}</td>
    <td class="p-4 text-center">
        <span class="{{ $typeClass }} rounded-full px-3 py-1 text-sm">{{ $employee['type'] }}</span>
    </td>
    <td class="p-4 text-center">{{ $employee['contact'] }}</td>
    <td class="space-x-2 p-4 text-center">
        @include('user.management.partials.user-table-actions', [
            'employee' => $employee,
            'editUserPayloadEncoded' => $editUserPayloadEncoded,
        ])
    </td>
</tr>
