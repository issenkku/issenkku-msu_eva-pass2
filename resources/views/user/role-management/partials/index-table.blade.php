{{-- ตารางบทบาท แสดงชื่อบทบาท สิทธิ์ที่ผูกไว้ และปุ่มจัดการ --}}
<table class="role-table">
    @include('user.role-management.partials.index-table-head')
    <tbody data-resource-rows data-async-table-region>
        @foreach($roles as $i => $role)
            @include('user.role-management.partials.index-table-row', ['index' => $i + 1, 'role' => $role])
        @endforeach
    </tbody>
</table>
