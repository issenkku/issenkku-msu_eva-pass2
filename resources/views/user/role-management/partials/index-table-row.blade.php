{{-- แถวข้อมูลบทบาทแต่ละรายการ --}}
<tr>
    <td>{{ $index }}</td>
    <td>{{ $role->name }}</td>
    <td>@include('user.role-management.partials.index-table-permissions', ['permissions' => $role->permissions])</td>
    <td>@include('user.role-management.partials.index-table-actions', ['role' => $role])</td>
</tr>
