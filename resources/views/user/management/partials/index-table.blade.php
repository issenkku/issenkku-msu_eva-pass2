{{-- ตารางรายชื่อเจ้าหน้าที่ ใช้ component x-user-table เดิมเพื่อคงรูปแบบข้อมูลและ action --}}
<div class="overflow-x-auto">
    <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="p-4 text-center">ลำดับ</th>
                <th class="p-4 text-left">ข้อมูลพนักงาน</th>
                <th class="p-4 text-center">รหัสพนักงาน</th>
                <th class="p-4 text-center">ตำแหน่งงาน</th>
                <th class="p-4 text-center">ระดับตำแหน่งงาน</th>
                <th class="p-4 text-center">ประเภท</th>
                <th class="p-4 text-center">ติดต่อ</th>
                <th class="p-4 text-center">การดำเนินการ</th>
            </tr>
        </thead>
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
                <tr>
                    <td colspan="8" class="text-center py-8 text-gray-500">
                        <i class="fas fa-user text-3xl mb-2 block"></i>
                        <p>ไม่มีข้อมูลเจ้าหน้าที่</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
