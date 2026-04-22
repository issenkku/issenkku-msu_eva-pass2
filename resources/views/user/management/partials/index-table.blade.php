{{-- ตารางรายชื่อเจ้าหน้าที่ ใช้ component x-user-table เดิมเพื่อคงรูปแบบข้อมูลและ action --}}
<div class="overflow-x-auto">
    <table class="min-w-full bg-white rounded-lg shadow">
        @include('user.management.partials.index-table-head')
        <tbody>
            @forelse ($users as $index => $user)
                <x-user-table :index="$index + 1" :employee="[
                    'id' => $user['id'],
                    'prefix' => $user['prefix'],
                    'name' => $user['name'],
                    'code' => $user['employee_id'],
                    'position' => $user['position']['name'] ?? '',
                    'job_level' => $user['job_level']['name'] ?? '',
                    'type' => $user['personnel_type'],
                    'contact' => $user['phone'],
                    'email' => $user['email'],
                    'bio' => $user['bio'],
                    'education_history' => $user['education_history_entries'] ?? [],
                    'status' => $user['status'],
                    'position_id' => $user['position_id'],
                    'job_level_id' => $user['job_level_id'],
                    'department_id' => $user['department_id'],
                    'role' => $user['role_names'][0] ?? '',
                    'role_names' => $user['role_names'] ?? [],
                ]" />
            @empty
                @include('user.management.partials.index-table-empty-state')
            @endforelse
        </tbody>
    </table>
</div>
